#!/usr/bin/env node

import fs from 'fs';
import path from 'path';
import axios from 'axios';
import pg from 'pg';
import { chromium } from 'playwright';

const MAX_CONCURRENCY = Math.max(1, Number.parseInt(process.env.SCRAPER_CONCURRENCY || '8', 10));
const REQUEST_TIMEOUT_MS = Math.max(5000, Number.parseInt(process.env.SCRAPER_TIMEOUT_MS || '20000', 10));
const BASE_URL = 'https://www.ratsit.se';
const ENABLE_PLAYWRIGHT_FALLBACK = process.env.ENABLE_PLAYWRIGHT_FALLBACK !== '0';

const USER_AGENTS = [
	'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
	'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
];

async function createDbPool() {
	return new pg.Pool({
		host: '127.0.0.1',
		port: 5432,
		user: 'postgres',
		password: 'bkkbkk',
		database: 'nordic',
		max: Math.max(6, MAX_CONCURRENCY + 2),
	});
}

function normalizePostnummer(value) {
	return String(value || '').replace(/\D/g, '');
}

function decodeHtml(text) {
	if (!text) {
		return '';
	}

	return text
		.replace(/&nbsp;/gi, ' ')
		.replace(/&amp;/gi, '&')
		.replace(/&quot;/gi, '"')
		.replace(/&#39;/gi, "'")
		.replace(/&lt;/gi, '<')
		.replace(/&gt;/gi, '>')
		.replace(/&#x2F;/gi, '/')
		.replace(/&#x2D;/gi, '-')
		.replace(/&#(\d+);/g, (_, code) => String.fromCharCode(Number(code)));
}

function stripHtml(text) {
	return decodeHtml(String(text || '').replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ').trim();
}

function loadCookieHeader() {
	const candidates = [
		path.join(process.cwd(), 'cookies', 'ratsit.json'),
		path.join(process.cwd(), '..', 'cookies', 'ratsit.json'),
		path.join(process.cwd(), 'jobs', 'cookies', 'ratsit.json'),
	];

	for (const file of candidates) {
		try {
			if (!fs.existsSync(file)) {
				continue;
			}

			const parsed = JSON.parse(fs.readFileSync(file, 'utf8'));
			if (!Array.isArray(parsed) || parsed.length === 0) {
				continue;
			}

			const cookieHeader = parsed
				.filter((item) => item && item.name && item.value)
				.map((item) => `${item.name}=${item.value}`)
				.join('; ');

			if (cookieHeader) {
				console.log(`Loaded ${parsed.length} cookie(s) from ${file}`);
				return cookieHeader;
			}
		} catch {
			continue;
		}
	}

	console.log('No valid ratsit cookie file found for HTTP mode.');
	return '';
}

function extractAdressRowsFromHtml(html, pageUrl) {
	const results = [];
	const anchorRegex = /<a\b[^>]*href=(['"])([^'"]*\/personer\/[^'"]*)\1[^>]*>([\s\S]*?)<\/a>/gi;
	let match;

	while ((match = anchorRegex.exec(html)) !== null) {
		const [, , hrefRaw, anchorInner] = match;
		const href = (() => {
			try {
				return new URL(hrefRaw, pageUrl || BASE_URL).href;
			} catch {
				return null;
			}
		})();

		if (!href) {
			continue;
		}

		const nearby = html.slice(Math.max(0, match.index - 700), Math.min(html.length, match.index + 1400));
		const countFromClass = nearby.match(/tree-structure__count[^>]*>\s*\(?\s*(\d+)\s*\)?\s*</i)?.[1] || '';
		const text = stripHtml(anchorInner);
		const countFromText = text.match(/\((\d+)\)/)?.[1] || '';
		const personer = Number.parseInt(countFromClass || countFromText || '0', 10) || 0;

		if (personer <= 0) {
			continue;
		}

		const adress = text
			.replace(/\s*\(\d+\)\s*/g, ' ')
			.replace(/\d{3}\s*\d{2}/g, ' ')
			.replace(/\s+/g, ' ')
			.trim();

		if (!adress || adress.length < 2) {
			continue;
		}

		results.push({
			adress,
			personer,
			ratsit_link: href,
		});
	}

	return results;
}

async function fetchHtml(url, cookieHeader) {
	let lastError = null;

	for (let attempt = 1; attempt <= 3; attempt++) {
		try {
			const response = await axios.get(url, {
				timeout: REQUEST_TIMEOUT_MS,
				maxRedirects: 5,
				validateStatus: (status) => status >= 200 && status < 400,
				headers: {
					'User-Agent': USER_AGENTS[(attempt - 1) % USER_AGENTS.length],
					'Accept-Language': 'sv-SE,sv;q=0.9,en;q=0.8,en-US;q=0.7',
					Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
					'Upgrade-Insecure-Requests': '1',
					'DNT': '1',
					'Sec-Fetch-Dest': 'document',
					'Sec-Fetch-Mode': 'navigate',
					'Sec-Fetch-Site': 'none',
					'Sec-Fetch-User': '?1',
					Referer: `${BASE_URL}/`,
					Cookie: cookieHeader || undefined,
				},
			});

			return {
				html: typeof response.data === 'string' ? response.data : '',
				status: response.status,
			};
		} catch (error) {
			lastError = error;
			const status = error?.response?.status;

			if (attempt < 3 && (status === 403 || status === 429 || status === 503)) {
				await new Promise((resolve) => setTimeout(resolve, attempt * 1200));
				continue;
			}

			throw error;
		}
	}

	throw lastError || new Error('HTTP fetch failed');
}

async function getOrCreateFallbackContext(runtime) {
	if (runtime.context && runtime.browser) {
		return runtime.context;
	}

	runtime.browser = await chromium.launch({
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

	runtime.context = await runtime.browser.newContext({
		userAgent: USER_AGENTS[0],
		viewport: { width: 1366, height: 768 },
		locale: 'sv-SE',
	});

	await runtime.context.setExtraHTTPHeaders({
		'Accept-Language': 'sv-SE,sv;q=0.9,en;q=0.8,en-US;q=0.7',
		Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
	});

	await runtime.context.route('**/*', (route) => {
		const type = route.request().resourceType();
		if (type === 'image' || type === 'font' || type === 'media' || type === 'stylesheet') {
			return route.abort();
		}

		return route.continue();
	});

	return runtime.context;
}

async function scrapeRatsitAdresserWithBrowser(url, row, pool, runtime) {
	const context = await getOrCreateFallbackContext(runtime);
	let page = null;

	try {
		page = await context.newPage();
		await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
		await page.waitForSelector('a[href*="/personer/"]', { timeout: 10000 });
		await page.waitForTimeout(350);

		const html = await page.content();
		const adressRows = extractAdressRowsFromHtml(html, url);
		const adressMap = new Map();

		for (const adressRow of adressRows) {
			const existing = adressMap.get(adressRow.adress);
			if (!existing) {
				adressMap.set(adressRow.adress, { ...adressRow });
			} else {
				existing.personer += adressRow.personer;
			}
		}

		const normalizedPostnummer = normalizePostnummer(row.postnummer);
		const { rows: existingRows } = await pool.query(
			`SELECT id, adress
			 FROM sweden_adresser
			 WHERE postnummer = $1 AND postort = $2 AND kommun = $3`,
			[normalizedPostnummer, row.postort, row.kommun],
		);

		const existingByAdress = new Map(existingRows.map((item) => [item.adress, item.id]));

		for (const adressRow of adressMap.values()) {
			const existingId = existingByAdress.get(adressRow.adress);

			if (existingId) {
				await pool.query(
					`UPDATE sweden_adresser
					 SET lan = $1, personer = $2, ratsit_link = $3, is_queue = true
					 WHERE id = $4`,
					[row.lan, adressRow.personer, adressRow.ratsit_link, existingId],
				);
			} else {
				await pool.query(
					`INSERT INTO sweden_adresser (adress, postnummer, postort, kommun, lan, personer, ratsit_link, is_queue)
					 VALUES ($1, $2, $3, $4, $5, $6, $7, true)`,
					[
						adressRow.adress,
						normalizedPostnummer,
						row.postort,
						row.kommun,
						row.lan,
						adressRow.personer,
						adressRow.ratsit_link,
					],
				);
			}
		}

		return adressMap.size;
	} finally {
		if (page) {
			await page.close().catch(() => null);
		}
	}
}

async function scrapeRatsitAdresserHttp(url, row, pool, cookieHeader, runtime) {
	console.log(`\nScraping HTTP: ${url} (${row.gata || ''}, ${row.postnummer || ''} ${row.postort || ''})`);

	try {
		const { html } = await fetchHtml(url, cookieHeader);
		if (!html || html.length < 500) {
			throw new Error('Empty/short HTML response');
		}

		const adressRows = extractAdressRowsFromHtml(html, url);
		const adressMap = new Map();

		for (const adressRow of adressRows) {
			const existing = adressMap.get(adressRow.adress);
			if (!existing) {
				adressMap.set(adressRow.adress, { ...adressRow });
			} else {
				existing.personer += adressRow.personer;
			}
		}

		const normalizedPostnummer = normalizePostnummer(row.postnummer);
		const { rows: existingRows } = await pool.query(
			`SELECT id, adress
			 FROM sweden_adresser
			 WHERE postnummer = $1 AND postort = $2 AND kommun = $3`,
			[normalizedPostnummer, row.postort, row.kommun],
		);

		const existingByAdress = new Map(existingRows.map((item) => [item.adress, item.id]));

		for (const adressRow of adressMap.values()) {
			try {
				const existingId = existingByAdress.get(adressRow.adress);

				if (existingId) {
					await pool.query(
						`UPDATE sweden_adresser
						 SET lan = $1, personer = $2, ratsit_link = $3, is_queue = true
						 WHERE id = $4`,
						[row.lan, adressRow.personer, adressRow.ratsit_link, existingId],
					);
				} else {
					await pool.query(
						`INSERT INTO sweden_adresser (adress, postnummer, postort, kommun, lan, personer, ratsit_link, is_queue)
						 VALUES ($1, $2, $3, $4, $5, $6, $7, true)`,
						[
							adressRow.adress,
							normalizedPostnummer,
							row.postort,
							row.kommun,
							row.lan,
							adressRow.personer,
							adressRow.ratsit_link,
						],
					);
				}
			} catch (error) {
				console.error(`  Error processing adress ${adressRow.adress}:`, error.message);
			}
		}

		return adressMap.size;
	} catch (error) {
		const status = error?.response?.status;
		console.error(`  HTTP scraping error for ${url}:`, error.message);

		if (ENABLE_PLAYWRIGHT_FALLBACK && (status === 403 || status === 429 || status === 503)) {
			console.log(`  ↪ Falling back to Playwright for blocked URL (${status})`);
			try {
				return await scrapeRatsitAdresserWithBrowser(url, row, pool, runtime);
			} catch (fallbackError) {
				console.error(`  Browser fallback failed for ${url}:`, fallbackError.message);
			}
		}

		return null;
	}
}

async function main() {
	console.log(`Starting Ratsit adress scrape (HTTP fast mode) with concurrency=${MAX_CONCURRENCY}...\n`);

	const pool = await createDbPool();
	const cookieHeader = loadCookieHeader();
	const runtime = {
		browser: null,
		context: null,
	};

	try {
		const { rows: gatorRows } = await pool.query(
			`SELECT id, gata, postnummer, postort, kommun, lan, ratsit_link
			 FROM sweden_gator
			 WHERE ratsit_link IS NOT NULL AND ratsit_link != '' AND is_done = false
			 ORDER BY id`,
		);

		console.log(`Found ${gatorRows.length} gator rows to process.\n`);

		let successCount = 0;
		let failCount = 0;
		let cursor = 0;

		const worker = async (workerId) => {
			while (true) {
				const index = cursor;
				cursor += 1;

				if (index >= gatorRows.length) {
					return;
				}

				const row = gatorRows[index];
				console.log(
					`[W${workerId}] [${index + 1}/${gatorRows.length}] Processing: ${row.gata || ''} (${row.postnummer || ''} ${row.postort || ''})`,
				);

				const adresserCount = await scrapeRatsitAdresserHttp(row.ratsit_link, row, pool, cookieHeader, runtime);

				if (adresserCount !== null) {
await pool.query(
					'UPDATE sweden_gator SET adresser = $1, is_done = true, is_queue = false WHERE id = $2',
						[adresserCount, row.id],
					);

					console.log(`[W${workerId}] ✓ Done id=${row.id}. adresser=${adresserCount}.`);
					successCount += 1;
				} else {
					console.log(`[W${workerId}] ✗ Failed id=${row.id}.`);
					failCount += 1;
				}
			}
		};

		const workers = Array.from(
			{ length: Math.min(MAX_CONCURRENCY, gatorRows.length || 1) },
			(_, idx) => worker(idx + 1),
		);
		await Promise.all(workers);

		console.log('\nAll gator rows processed.');
		console.log(`  Success: ${successCount}`);
		console.log(`  Failed:  ${failCount}`);
	} finally {
		if (runtime.context) {
			await runtime.context.close().catch(() => null);
		}
		if (runtime.browser) {
			await runtime.browser.close().catch(() => null);
		}
		await pool.end();
	}
}

main().catch((err) => {
	console.error('Fatal error:', err);
	process.exit(1);
});
