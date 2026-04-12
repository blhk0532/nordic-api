#!/usr/bin/env node

import pg from 'pg';
import { chromium } from 'playwright';

async function createDbConnection() {
    const client = new pg.Client({
        host: '127.0.0.1',
        port: 5432,
        user: 'postgres',
        password: 'qToo81p82TFrWDLWtdNF',
        database: 'nordic',
    });
    await client.connect();
    return client;
}

async function scrapeRatsitPostorter(url, kommunName, lan, connection) {
    console.log(`\nScraping: ${url} (${kommunName})`);

    let browser = null;

    try {
        // Launch browser with realistic settings
        browser = await chromium.launch({
            headless: true,
            executablePath: '/usr/bin/google-chrome',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu',
            ],
        });

        const context = await browser.newContext({
            userAgent:
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport: { width: 1920, height: 1080 },
            locale: 'sv-SE',
        });

        const page = await context.newPage();

        // Set additional headers to look more like a real browser
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'sv-SE,sv;q=0.9,en;q=0.8,en-US;q=0.7',
            Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        });

        // Go to the specific URL
        await page.goto(url, {
            waitUntil: 'networkidle',
            timeout: 30000,
        });

        // Wait for content to load
        await page.waitForTimeout(5000);

        // Wait for any dynamic content to load
        await page.waitForTimeout(3000);

        // Wait for any dynamic content to load
        await page.waitForTimeout(3000);

        // Check if content has loaded
        await page.waitForFunction(
            () => {
                return document.body && document.body.innerHTML.length > 1000;
            },
            { timeout: 10000 },
        );

        console.log('Page content loaded, checking for postal areas...');

        // Extract postal area information
        const postnummerRows = await page.evaluate(() => {
            try {
                const result = [];

                // Debug: Get page title and content
                const pageTitle = document.title;
                const hasContent =
                    document.body && document.body.innerHTML.length > 1000;

                console.log(`Page title: "${pageTitle}"`);
                console.log(`Has body content: ${hasContent}`);

                // Look for postal area links - try broader approach
                const allLinks = document.querySelectorAll(
                    'a[href*="/personer/"]',
                );
                const postalAreaLinks = Array.from(allLinks).filter((link) => {
                    const text = link.textContent.trim();
                    return /\d{3}\s*\d{2}/.test(text);
                });

                console.log(
                    `Found ${postalAreaLinks.length} potential postal area links`,
                );

                // Debug: Show what we found
                for (let i = 0; i < Math.min(3, postalAreaLinks.length); i++) {
                    const link = postalAreaLinks[i];
                    console.log(
                        `Link ${i}: href="${link.href}", text="${link.textContent.trim()}"`,
                    );
                }

                // Also check for any other elements that might contain postal areas
                const allElements = document.querySelectorAll('li, div, span');
                console.log(`Total elements found: ${allElements.length}`);

                // Look for any elements containing postal codes
                const postalCodeElements = Array.from(allElements).filter(
                    (el) => {
                        const text = el.textContent || '';
                        return /\d{3}\s*\d{2}/.test(text);
                    },
                );
                console.log(
                    `Elements with postal codes: ${postalCodeElements.length}`,
                );

                // Show first few elements with postal codes
                for (
                    let i = 0;
                    i < Math.min(3, postalCodeElements.length);
                    i++
                ) {
                    const el = postalCodeElements[i];
                    console.log(
                        `Postal element ${i}: "${el.textContent.trim()}"`,
                    );
                }

                const normalizePostNummer = (value) => {
                    const digits = String(value || '').replace(/\D/g, '');
                    if (digits.length !== 5) {
                        return null;
                    }

                    return `${digits.slice(0, 3)} ${digits.slice(3)}`;
                };

                for (const link of postalAreaLinks) {
                    const href = link.href;
                    const text = link.textContent.trim();
                    const countSpan = link.parentElement?.querySelector(
                        '.tree-structure__count',
                    );

                    const normalizedText = text.replace(/\s+/g, ' ').trim();
                    const postNummerMatch = normalizedText.match(/(\d{3}\s*\d{2})/);
                    const postNummer = normalizePostNummer(postNummerMatch?.[1] || '');

                    if (!postNummer) {
                        continue;
                    }

                    const postOrt = normalizedText
                        .replace(/\s*\(?\d{3}\s*\d{2}\)?\s*/g, ' ')
                        .replace(/\s*\(\d+\)\s*/g, ' ')
                        .replace(/\s*-\s*/g, ' ')
                        .replace(/\s+/g, ' ')
                        .trim();

                    const spanCountText = countSpan?.textContent?.replace(/[^\d]/g, '') || '';
                    const textCountMatch = normalizedText.match(/\((\d+)\)/);
                    const personer = parseInt(spanCountText || textCountMatch?.[1] || '0', 10) || 0;

                    if (!postOrt) {
                        continue;
                    }

                    result.push({
                        post_ort: postOrt,
                        post_nummer: postNummer,
                        personer,
                        post_nummer_link: href,
                    });
                }

                return result;
            } catch (error) {
                console.error('Error in page evaluation:', error);
                return [];
            }
        });

        console.log(`Found ${postnummerRows.length} postnummer rows`);

        // Show first few results for debugging
        console.log('Sample results:');
        postnummerRows.slice(0, 5).forEach((p) => {
            console.log(
                `  ${p.post_ort}: ${p.post_nummer} (${p.personer} personer)`,
            );
        });

        const postorterMap = new Map();
        const postnummerMap = new Map();

        for (const row of postnummerRows) {
            const existingPostort = postorterMap.get(row.post_ort);

            if (!existingPostort) {
                postorterMap.set(row.post_ort, {
                    post_ort: row.post_ort,
                    personer: row.personer,
                    postnummerSet: new Set([row.post_nummer]),
                    ratsit_link: row.post_nummer_link,
                });
            } else {
                existingPostort.personer += row.personer;
                existingPostort.postnummerSet.add(row.post_nummer);
            }

            postnummerMap.set(row.post_nummer, row);
        }

        // Save sweden_postnummer
        for (const postnummerRow of postnummerMap.values()) {
            try {
                await connection.query(
                    `INSERT INTO sweden_postnummer (postnummer, postort, kommun, lan, personer, ratsit_link, is_done, is_queue)
                     VALUES ($1, $2, $3, $4, $5, $6, false, true)
                     ON CONFLICT (postnummer) DO UPDATE SET
                         postort = EXCLUDED.postort,
                         kommun = EXCLUDED.kommun,
                         lan = EXCLUDED.lan,
                         personer = EXCLUDED.personer,
                         ratsit_link = EXCLUDED.ratsit_link,
                         is_done = false,
                         is_queue = true`,
                    [
                        postnummerRow.post_nummer,
                        postnummerRow.post_ort,
                        kommunName,
                        lan,
                        postnummerRow.personer,
                        postnummerRow.post_nummer_link,
                    ],
                );
                console.log(
                    `  Upserted postnummer ${postnummerRow.post_nummer} (${postnummerRow.personer} personer)`,
                );
            } catch (error) {
                console.error(`  Error processing postnummer ${postnummerRow.post_nummer}:`, error.message);
            }
        }

        // Save aggregated sweden_postorter
        for (const postort of postorterMap.values()) {
            try {
                await connection.query(
                    `INSERT INTO sweden_postorter (postort, kommun, lan, personer, postnummer, ratsit_link, is_done, is_queue)
                     VALUES ($1, $2, $3, $4, $5, $6, true, false)
                     ON CONFLICT DO NOTHING`,
                    [
                        postort.post_ort,
                        kommunName,
                        lan,
                        postort.personer,
                        postort.postnummerSet.size,
                        postort.ratsit_link,
                    ],
                );
                console.log(
                    `  Upserted postort ${postort.post_ort} (${postort.personer} personer, ${postort.postnummerSet.size} postnummer)`,
                );
            } catch (error) {
                console.error(`  Error processing postort ${postort.post_ort}:`, error.message);
            }
        }

        return {
            uniquePostorter: postorterMap.size,
            totalPostnummer: postnummerMap.size,
        };
    } catch (error) {
        console.error(`  Scraping error for ${url}:`, error.message);
        return null;
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

async function main() {
    console.log('Starting full Ratsit postorter scrape from sweden_kommuner...\n');

    const connection = await createDbConnection();

    try {
        // Fetch all unprocessed kommuner that have a ratsit_link
        const { rows: kommuner } = await connection.query(
            'SELECT id, kommun, lan, ratsit_link FROM sweden_kommuner WHERE ratsit_link IS NOT NULL AND is_done = false ORDER BY id',
        );

        console.log(`Found ${kommuner.length} kommuner to process.\n`);

        let successCount = 0;
        let failCount = 0;

        for (const [index, row] of kommuner.entries()) {
            console.log(`[${index + 1}/${kommuner.length}] Processing: ${row.kommun} (${row.lan})`);

            const scrapeResult = await scrapeRatsitPostorter(
                row.ratsit_link,
                row.kommun,
                row.lan,
                connection,
            );

            if (scrapeResult !== null) {
                // Mark as done in sweden_kommuner and store counters
                await connection.query(
                    'UPDATE sweden_kommuner SET is_done = true, is_queue = false, postorter = $1, postnummer = $2 WHERE id = $3',
                    [scrapeResult.uniquePostorter, scrapeResult.totalPostnummer, row.id],
                );
                console.log(
                    `  ✓ Done. postorter=${scrapeResult.uniquePostorter}, postnummer=${scrapeResult.totalPostnummer}. Marked is_done=1.\n`,
                );
                successCount++;
            } else {
                console.log(`  ✗ Failed. Skipping is_done update.\n`);
                failCount++;
            }
        }

        console.log(`\nAll kommuner processed.`);
        console.log(`  Success: ${successCount}`);
        console.log(`  Failed:  ${failCount}`);
    } finally {
        await connection.end();
    }
}

main().catch((err) => {
    console.error('Fatal error:', err);
    process.exit(1);
});
