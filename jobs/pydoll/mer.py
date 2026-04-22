import asyncio
import json
import re
import sqlite3
import aiohttp
from datetime import datetime
from pydoll.browser import Chrome


COOKIES_FILE = "cookies.json"
API_URL = "https://ekoll.se/api/merinfo/import"
DB_FILE = "merinfo_results.db"

def init_db():
    conn = sqlite3.connect(DB_FILE)
    c = conn.cursor()
    c.execute('''
        CREATE TABLE IF NOT EXISTS results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            postnummer TEXT,
            page_num INTEGER,
            short_uuid TEXT,
            name TEXT,
            given_name TEXT,
            personal_number TEXT,
            street TEXT,
            zip_code TEXT,
            city TEXT,
            gender TEXT,
            phone_raw TEXT,
            phone_number TEXT,
            url TEXT,
            merinfo_url TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sent_to_api INTEGER DEFAULT 0
        )
    ''')
    conn.commit()
    return conn

def save_to_db(conn, postnummer, page_num, data):
    c = conn.cursor()
    count = 0
    
    total_items = sum(len(r.get('items', [])) for r in data.get('results', []))
    print(f"    save_to_db: found {total_items} items to save")
    
    for result_group in data.get('results', []):
        for item in result_group.get('items', []):
            try:
                phone_numbers = item.get('phone_number', [])
                phone_raw = phone_numbers[0].get('raw', '') if phone_numbers else ''
                phone_display = phone_numbers[0].get('number', '') if phone_numbers else ''
                
                address = item.get('address', [{}])[0] if item.get('address') else {}
                
                c.execute('''
                    INSERT INTO results (
                        postnummer, page_num, short_uuid, name, given_name,
                        personal_number, street, zip_code, city, gender,
                        phone_raw, phone_number, url, merinfo_url
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ''', (
                    postnummer,
                    page_num,
                    item.get('short_uuid', ''),
                    item.get('name', ''),
                    item.get('givenNameOrFirstName', ''),
                    item.get('personalNumber', ''),
                    address.get('street', ''),
                    address.get('zip_code', ''),
                    address.get('city', ''),
                    item.get('gender', ''),
                    phone_raw,
                    phone_display,
                    item.get('url', ''),
                    item.get('same_address_url', '')
                ))
                count += 1
            except Exception as e:
                print(f"    save_to_db error for item: {e}")
                continue
    
    conn.commit()
    return count

def strip_html(text):
    if not text:
        return ""
    clean = re.sub(r'<[^>]+>', '', text)
    clean = clean.replace('&lt;', '<').replace('&gt;', '>').replace('&amp;', '&')
    clean = clean.replace('&nbsp;', ' ').replace('&quot;', '"')
    return clean.strip()

def clean_data(data):
    import re
    is_hus_pattern = re.compile(r'lgh|1 tr|2 tr|3 tr|4 tr|5 tr|6 tr| nb| box| bv|\bBox\b|\b([1-9][0-9]?|100)\s*[A-Z]\b', re.IGNORECASE)
    
    if isinstance(data, dict):
        cleaned = {}
        for key, value in data.items():
            if key == 'name' and isinstance(value, str):
                cleaned[key] = strip_html(value)
            else:
                cleaned[key] = clean_data(value)
        
        if 'address' in cleaned and isinstance(cleaned['address'], list) and len(cleaned['address']) > 0:
            street = cleaned['address'][0].get('street', '')
            if street and is_hus_pattern.search(street):
                cleaned['is_hus'] = False
            else:
                cleaned['is_hus'] = True
        return cleaned
    elif isinstance(data, list):
        return [clean_data(item) for item in data]
    elif isinstance(data, str):
        return strip_html(data)
    return data

async def get_queue():
    try:
        async with aiohttp.ClientSession() as session:
            async with session.get('https://ekoll.se/api/sweden-postnummer/get-queue') as resp:
                if resp.content_type == 'application/json':
                    data = await resp.json()
                    dataData = data.get('data')
                    postnummer = dataData['postnummer']
                    if postnummer:
                        print(f"Got postnummer from queue: {postnummer}")
                    else:
                        print(data)
                    return postnummer
                else:
                    print(f"Unexpected response content type: {resp.content_type}")
                    return None
    except Exception as e:
        print(f"{e}")
        return None

async def save_cookies(page):
    pass  # Do not save cookies - managed manually

async def load_cookies(page):
    try:
        with open(COOKIES_FILE, "r") as f:
            cookies = json.load(f)
        if cookies:
            await page.set_cookies(cookies)
            return True
    except:
        pass
    return False

async def send_to_bulk_api(page, data):
    try:
        async with aiohttp.ClientSession(timeout=aiohttp.ClientTimeout(total=60)) as session:
            all_items = []
            for result_group in data.get("results", []):
                all_items.extend(result_group.get("items", []))
            
            batch_size = 10
            total_batches = (len(all_items) + batch_size - 1) // batch_size
            print(f"    Sending {len(all_items)} items in {total_batches} batches of {batch_size}...")
            
            for batch_num in range(total_batches):
                start_idx = batch_num * batch_size
                end_idx = min(start_idx + batch_size, len(all_items))
                batch = all_items[start_idx:end_idx]
                
                payload = {"results": [{"type": "person", "items": batch}]}
                
                async with session.post(API_URL, json=payload) as resp:
                    text = await resp.text()
                    print(f"    Batch {batch_num + 1}/{total_batches}: status {resp.status}, response: {text[:200]}")
                    if resp.status != 200:
                        break
                
                # Add delay between batches except after the last one
                if batch_num + 1 < total_batches:
                    await asyncio.sleep(5)
            
            return {"status": resp.status, "ok": resp.ok}
    except aiohttp.ClientConnectorError as e:
        print(f"    Bulk API connection error: {e}")
        return {}
    except asyncio.TimeoutError:
        print(f"    Bulk API timeout")
        return {}
    except Exception as e:
        print(f"    Bulk API error: {e}")
        return {}

async def wait_for_cloudflare(page, max_wait=60):
    for _ in range(max_wait // 5):
        try:
            cloudflare = await page.execute_script("""
                (() => {
                    const check = document.querySelector('#cf-challenge-running, .cf-challenge-container, #challenge-running');
                    return check ? true : false;
                })()
            """, return_by_value=True)
            cf_running = cloudflare.get('result', {}).get('result', {}).get('value', False)
            if cf_running:
                print("  Waiting for Cloudflare challenge...")
                await asyncio.sleep(5)
            else:
                await asyncio.sleep(2)
                return True
        except:
            await asyncio.sleep(5)
    return False

async def accept_cookies(page):
    async def try_click(selector):
        try:
            result = await page.execute_script(f"""
                (() => {{
                    const el = document.querySelector('{selector}');
                    if (el) {{
                        console.log('Found element: ' + '{selector}');
                        const tag = el.tagName.toLowerCase();
                        console.log('Tag: ' + tag + ', href: ' + (el.href || 'none') + ', type: ' + (el.type || 'none'));
                        
                        if (tag === 'a' && el.href && el.href.includes('cookie')) {{
                            console.log('Skipping link to cookie page');
                            return 'skip';
                        }}
                        
                        el.click();
                        console.log('Clicked: ' + '{selector}');
                        return true;
                    }}
                    return false;
                }})()
            """, return_by_value=True)
            raw_result = result.get('result', {}).get('result', {})
            val = raw_result.get('value', False)
            if val == 'skip':
                return 'skip'
            if val:
                await asyncio.sleep(2)
                print("  Cookie consent accepted")
                return True
        except Exception as e:
            print(f"  Error clicking {selector}: {e}")
        return False
    
    print("  Checking for cookie consent button...")
    
    selectors = [
        '#accept-btn',
        'button#accept-btn',
        'button[id="accept-btn"]',
        '.accept-btn',
        'button.accept-btn',
        'button[class*="accept"]',
        '[role="button"][class*="cookie"]'
    ]
    
    for sel in selectors:
        res = await try_click(sel)
        if res == True:
            await asyncio.sleep(1)
            url = await page.execute_script("return window.location.href", return_by_value=True)
            current_url = url.get('result', {}).get('result', {}).get('value', '')
            if 'cookie' in current_url.lower() or 'policy' in current_url.lower():
                print(f"  WARNING: Navigated to {current_url}")
                await page.go_back()
                await asyncio.sleep(2)
                continue
            return True
        elif res == 'skip':
            continue
    
    print("  Trying to find any accept button by text...")
    try:
        result = await page.execute_script("""
            (() => {
                const selectors = document.querySelectorAll('button, [role="button"], input[type="button"]');
                const texts = ['acceptera', 'godkänn', 'accept', 'agree', 'ok', 'got it', 'understand'];
                for (const btn of selectors) {
                    const text = btn.textContent.toLowerCase().trim();
                    for (const t of texts) {
                        if (text.includes(t)) {
                            console.log('Found button with text: ' + text + ', tag: ' + btn.tagName);
                            btn.click();
                            return true;
                        }
                    }
                }
                return false;
            })()
        """, return_by_value=True)
        if result.get('result', {}).get('result', {}).get('value', False):
            await asyncio.sleep(2)
            print("  Cookie consent accepted")
            return True
    except Exception as e:
        print(f"  Error finding by text: {e}")
    
    print("  No cookie button found")
    return False

async def click_if_exists(page, selector, delay=2):
    try:
        exists = await page.execute_script(f"""
            (() => {{
                const el = document.querySelector('{selector}');
                return el ? true : false;
            }})()
        """, return_by_value=True)
        if exists.get('result', {}).get('result', {}).get('value', False):
            await page.execute_script(f"document.querySelector('{selector}').click();")
            await asyncio.sleep(delay)
            return True
    except:
        pass
    return False

async def click_element_by_text(page, text_pattern, parent_selector=None, delay=2):
    try:
        script = f"""
            (() => {{
                const selectors = document.querySelectorAll('{parent_selector or "button, a, div"}');
                for (const el of selectors) {{
                    if (el.textContent.includes('{text_pattern}')) {{
                        el.click();
                        return true;
                    }}
                }}
                return false;
            }})()
        """
        result = await page.execute_script(script, return_by_value=True)
        clicked = result.get('result', {}).get('result', {}).get('value', False)
        if clicked:
            await asyncio.sleep(delay)
            return True
    except:
        pass
    return False

async def process_postnummer(page, postnummer, conn):
    page_num = 1
    total_db = 0
    all_results = []
    
    try:
        await page.go_to(f"https://www.merinfo.se/search?d=p&q={postnummer}")
        await asyncio.sleep(5)
    except Exception as e:
        print(f"  [ERROR] Page load: {e}")
        return 0
    
    for i in range(3):
        await accept_cookies(page)
        await asyncio.sleep(2)
    
    await wait_for_cloudflare(page)
    
    for i in range(3):
        await accept_cookies(page)
        await asyncio.sleep(2)
    
    await save_cookies(page)
    
    await asyncio.sleep(3)
    
    async def safe_click(selector, timeout=5):
        import time
        start = time.time()
        while time.time() - start < timeout:
            try:
                if selector.startswith('/') or selector.startswith('('):
                    result = await page.execute_script(f"""
                        (() => {{
                            const xpath = `{selector}`;
                            const result = document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null);
                            const el = result.singleNodeValue;
                            if (el) {{
                                el.click();
                                return true;
                            }}
                            return false;
                        }})()
                    """, return_by_value=True)
                else:
                    result = await page.execute_script(f"""
                        (() => {{
                            const el = document.querySelector('{selector}');
                            if (el) {{
                                el.click();
                                return true;
                            }}
                            return false;
                        }})()
                    """, return_by_value=True)
                val = result.get('result', {}).get('result', {}).get('value', False)
                if val:
                    await asyncio.sleep(2)
                    print(f"  Clicked: {selector}")
                    return True
                print(f"  Element not found: {selector}, retrying...")
            except Exception as e:
                print(f"  Error clicking {selector}: {e}")
            await asyncio.sleep(0.5)
        print(f"  Failed to click after {timeout}s: {selector}")
        return False
    
    print("  Clicking Avancerat sök...")
    await safe_click('[aria-label="Växla till det avancerade filret"]')
    await asyncio.sleep(3)
    
    print("  Clicking GEO filter section...")
    await safe_click('//*[@id="search-filter-advanced-component"]/DIV[1]/DIV[3]/DIV[1]')
    await asyncio.sleep(3)

    print("  Clicking postnummer filter section...")
    await safe_click('//*[@id="search-filter-advanced-component"]/DIV[1]/DIV[3]/DIV[2]/DIV[3]/INPUT[1]')
    await asyncio.sleep(3)

    print("  Clicking postnummer input filter section...")
    await safe_click('[aria-label^="Ange ett postnummer"]')
    await asyncio.sleep(1)
    
    print(f"  Filling postnummer in filter: {postnummer}")
    try:
        result = await page.execute_script(f"""
            (() => {{
                const inputs = document.querySelectorAll('input');
                for (const inp of inputs) {{
                    if (inp.ariaLabel && inp.ariaLabel.includes('postnummer')) {{
                        console.log('Found post input: ' + inp.ariaLabel);
                        inp.focus();
                        inp.value = '{postnummer}';
                        inp.dispatchEvent(new Event('focus', {{ bubbles: true }}));
                        inp.dispatchEvent(new Event('input', {{ bubbles: true }}));
                        inp.dispatchEvent(new Event('blur', {{ bubbles: true }}));
                        inp.dispatchEvent(new Event('change', {{ bubbles: true }}));
                        console.log('After fill: ' + inp.value);
                        return inp.value;
                    }}
                }}
                return null;
            }})()
        """, return_by_value=True)
        val = result.get('result', {}).get('result', {}).get('value')
        if val:
            print(f"  Input filled with: {val}")
        else:
            print(f"  No input found with placeholder")
        await asyncio.sleep(3)
    except Exception as e:
        print(f"  Error: {e}")
    
    print("  Clicking search button...")
    await safe_click('button.button-primary')
    await asyncio.sleep(3)
    
    print("  Clicking Telefonnummer filter...")
    try:
        result = await page.execute_script("""
            (() => {
                const headers = document.querySelectorAll('.text-secondary.text-lg.font-semibold');
                for (const h of headers) {
                    if (h.textContent.includes('Telefonnummer')) {
                        const parent = h.closest('.p-4');
                        if (parent) {
                            parent.click();
                            return true;
                        }
                    }
                }
                return false;
            })()
        """, return_by_value=True)
        if result.get('result', {}).get('result', {}).get('value', False):
            print("  Clicked Telefonnummer header")
            await asyncio.sleep(2)
    except Exception as e:
        print(f"  Error clicking Telefonnummer: {e}")
    
    print("  Clicking Med telefonnummer toggle...")
    try:
        result = await page.execute_script("""
            (() => {
                const containers = document.querySelectorAll('div.mx-4');
                for (const c of containers) {
                    if (c.textContent.includes('Med telefonnummer')) {
                        const toggle = c.querySelector('div[style*="border-color: rgb(150, 207, 229)"]');
                        if (toggle) {
                            toggle.click();
                            return true;
                        }
                    }
                }
                return false;
            })()
        """, return_by_value=True)
        if result.get('result', {}).get('result', {}).get('value', False):
            print("  Clicked Med telefonnummer toggle")
            await asyncio.sleep(2)
    except Exception as e:
        print(f"  Error clicking toggle: {e}")
    
    print("  Clicking Visa sökresultat...")
    await safe_click('button[aria-label="Visa sökresultat för nuvarande filtrering"]')
    await asyncio.sleep(5)
    await accept_cookies(page)
    
    try:
        html = await page.content()
        with open(f"debug_page_{postnummer}_after_search.html", "w") as f:
            f.write(html)
        print(f"  [DEBUG] Saved search results page")
    except:
        pass
    
    while True:
        print(f"  Page {page_num}...")
        await asyncio.sleep(10)
        
        try:
            logs = await page.get_network_logs(filter='api/v1/search/results')
            
            for log in logs:
                req = log.get('params', {}).get('request', {})
                if 'api/v1/search/results' in req.get('url', ''):
                    req_id = log.get('params', {}).get('requestId', '')
                    if req_id:
                        try:
                            body = await page.get_network_response_body(req_id)
                            if body and len(body) > 100:
                                data = json.loads(body)
                                
                                if data.get('results'):
                                    items_count = sum(len(r.get('items', [])) for r in data['results'])
                                    if items_count > 0:
                                        print(f"    Found {items_count} results")
                                        
                                        cleaned_data = clean_data(data)
                                        all_results.extend(cleaned_data.get("results", []))
                                        
                                        db_count = save_to_db(conn, postnummer, page_num, cleaned_data)
                                        print(f"    Saved {db_count} to DB")
                                        total_db += db_count
                                        
                                        page_num += 1
                        except Exception as e:
                            print(f"    [...] {e}")
        except Exception as e:
            print(f"    [NET ERR] {e}")
        
        # Send results after each page
        if all_results:
            page_data = {"results": all_results}
            print(f"  Sending {len(all_results)} result groups to API...")
            await send_to_bulk_api(page, page_data)
            all_results = []
        
        await accept_cookies(page)
        
        # Save all results to one file at the end
        if all_results:
            final_data = {"results": all_results}
            filename = f"network_results_{postnummer.replace(' ', '_')}.json"
            with open(filename, "w") as f:
                json.dump(final_data, f, ensure_ascii=False, indent=2)
            print(f"  Saved all {len(all_results)} result groups to {filename}")
        else:
            final_data = {"results": []}
        
        # Check next page
        try:
            check = await page.execute_script("""
                (() => {
                    const next = document.querySelector('a[rel="next"]');
                    if (!next) return false;
                    return !next.classList.contains('pointer-events-none');
                })()
            """, return_by_value=True)
            has_next = check.get('result', {}).get('result', {}).get('value', False)
        except:
            has_next = False
        
        if has_next:
            clicked = await click_if_exists(page, 'a[rel="next"]', 5)
            if clicked:
                await asyncio.sleep(3)
                await accept_cookies(page)
            else:
                break
        else:
            break
    
    return total_db

async def main():
    print("Initializing database...")
    conn = init_db()
    
    while True:
        print(f"\n{'='*50}")
        
        # Get postnummer from queue
        postnummer = await get_queue()
        
        while not postnummer:
            print("Queue empty, waiting 60 seconds before retrying...")
            await asyncio.sleep(60)
            postnummer = await get_queue()
        
        print(f"Processing: {postnummer}")
        
        try:
            async with Chrome() as browser:
                page = await browser.start()
                await page.enable_network_events()
                await load_cookies(page)
                
                total = await process_postnummer(page, postnummer, conn)
                
                print(f"Completed: {postnummer} - {total} records")
                await save_cookies(page)
                
                await browser.stop()
        except Exception as e:
            print(f"[ERROR] Browser: {e}")
        
        print("Waiting 60 seconds...")
        await asyncio.sleep(60)
    
    conn.close()

if __name__ == "__main__":
    asyncio.run(main())
