#!/usr/bin/env node

import pg from 'pg';
import { chromium } from 'playwright';

async function createDbConnection() {
	const client = new pg.Client({
		host: '127.0.0.1',
		port: 5432,
		user: 'postgres',
		password: 'bkkbkk',
		database: 'nordic',
	});
	await client.connect();
	return client;
}

function normalizePostnummer(value) {
	return String(value || '').replace(/\D/g, '');
}

const API_BASE_URL =
	process.env.SWEDEN_POSTNUMMER_API_URL ||
	process.env.SWEDEN_API_URL ||
	'https://ekoll.se/api';

async function parseJsonResponse(response) {
	const responseText = await response.text();
	try {
		return JSON.parse(responseText);
	} catch (error) {
		throw new Error(`Failed to parse API JSON response (status ${response.status}): ${responseText.slice(0, 1024)}`);
	}
}

function parseCliFilters(argv) {
	const args = Array.isArray(argv) ? argv : [];
	let postort = null;
	let postnummer = null;
	let kommun = null;
	let lan = null;
	let apiOnly = false;

	for (let i = 0; i < args.length; i++) {
		const arg = args[i];

		if (arg.startsWith('--postort=')) {
			postort = arg.slice('--postort='.length).trim() || null;
			continue;
		}

		if (arg === '--postort' && args[i + 1]) {
			postort = String(args[i + 1]).trim() || null;
			i++;
			continue;
		}

		if (arg.startsWith('--postnummer=')) {
			postnummer = normalizePostnummer(arg.slice('--postnummer='.length)) || null;
			continue;
		}

		if (arg === '--postnummer' && args[i + 1]) {
			postnummer = normalizePostnummer(args[i + 1]) || null;
			i++;
			continue;
		}

		if (arg.startsWith('--kommun=')) {
			kommun = arg.slice('--kommun='.length).trim() || null;
			continue;
		}

		if (arg === '--kommun' && args[i + 1]) {
			kommun = String(args[i + 1]).trim() || null;
			i++;
			continue;
		}

		if (arg.startsWith('--lan=')) {
			lan = arg.slice('--lan='.length).trim() || null;
			continue;
		}

		if (arg === '--lan' && args[i + 1]) {
			lan = String(args[i + 1]).trim() || null;
			i++;
			continue;
		}

		if (arg === '--api-only' || arg === '--only-api') {
			apiOnly = true;
			continue;
		}
	}

	return { postort, postnummer, kommun, lan, apiOnly };
}

function buildQueryString(params) {
	const searchParams = new URLSearchParams();
	for (const [key, value] of Object.entries(params)) {
		if (value !== null && value !== undefined && value !== '') {
			searchParams.set(key, value);
		}
	}
	return searchParams.toString();
}

async function fetchNextPostnummerRow(filters) {
	const query = buildQueryString({
		postort: filters.postort,
		postnummer: filters.postnummer,
		kommun: filters.kommun,
		lan: filters.lan,
	});

	const response = await fetch(`${API_BASE_URL}/sweden-postnummer/next${query ? `?${query}` : ''}`, {
		headers: {
			Accept: 'application/json',
		},
	});

	if (response.status === 204) {
		return null;
	}

	if (!response.ok) {
		const text = await response.text();
		throw new Error(`API request failed with status ${response.status}: ${text}`);
	}

	const data = await parseJsonResponse(response);
	return data.data || null;
}

async function markPostnummerProcessed(id, gatorCount = null) {
	const payload = { id, is_done: true, is_queue: false };
	if (gatorCount !== null) {
		payload.gator = gatorCount;
	}

	const response = await fetch(`${API_BASE_URL}/sweden-postnummer/processed`, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			Accept: 'application/json',
		},
		body: JSON.stringify(payload),
	});

	if (!response.ok) {
		const text = await response.text();
		throw new Error(`API request failed with status ${response.status}: ${text}`);
	}

	return parseJsonResponse(response);
}

async function postScrapedGator(records) {
	const response = await fetch(`${API_BASE_URL}/sweden-gator/scraped`, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			Accept: 'application/json',
		},
		body: JSON.stringify({ records }),
	});

	if (!response.ok) {
		const text = await response.text();
		throw new Error(`API request failed with status ${response.status}: ${text}`);
	}

	return parseJsonResponse(response);
}

async function scrapeRatsitGator(url, row, connection, apiOnly = false) {
	console.log(`\nScraping: ${url} (${row.postnummer} ${row.postort || ''})`);

	let browser = null;

	try {
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

		await page.setExtraHTTPHeaders({
			'Accept-Language': 'sv-SE,sv;q=0.9,en;q=0.8,en-US;q=0.7',
			Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
		});

		await page.goto(url, {
			waitUntil: 'networkidle',
			timeout: 30000,
		});

		await page.waitForTimeout(5000);
		await page.waitForTimeout(3000);

		await page.waitForFunction(
			() => {
				return document.body && document.body.innerHTML.length > 1000;
			},
			{ timeout: 10000 },
		);

		const gatorRows = await page.evaluate(() => {
			try {
				const result = [];

				const allLinks = document.querySelectorAll('a[href*="/personer/"]');

				for (const link of Array.from(allLinks)) {
					const href = link.href;
					const text = (link.textContent || '').replace(/\s+/g, ' ').trim();

					const countFromText = text.match(/\((\d+)\)/)?.[1] || '';
					const countFromLi =
						link
							.closest('li')
							?.querySelector('.tree-structure__count')
							?.textContent?.replace(/[^\d]/g, '') || '';
					const countFromParent =
						link.parentElement
							?.querySelector('.tree-structure__count')
							?.textContent?.replace(/[^\d]/g, '') || '';

					const personer = parseInt(
						countFromLi || countFromParent || countFromText || '0',
						10,
					) || 0;

					if (personer <= 0) {
						continue;
					}

					const gata = text
						.replace(/\s*\(\d+\)\s*/g, ' ')
						.replace(/\d{3}\s*\d{2}/g, ' ')
						.replace(/\s+/g, ' ')
						.trim();

					if (!gata || gata.length < 2) {
						continue;
					}

					result.push({
						gata,
						personer,
						ratsit_link: href,
					});
				}

				return result;
			} catch (error) {
				console.error('Error in page evaluation:', error);
				return [];
			}
		});

		const gatorMap = new Map();

		for (const gatorRow of gatorRows) {
			const existing = gatorMap.get(gatorRow.gata);

			if (!existing) {
				gatorMap.set(gatorRow.gata, {
					gata: gatorRow.gata,
					personer: gatorRow.personer,
					ratsit_link: gatorRow.ratsit_link,
				});
			} else {
				existing.personer += gatorRow.personer;
			}
		}

		const normalizedPostnummer = normalizePostnummer(row.postnummer);

		if (apiOnly) {
			const records = Array.from(gatorMap.values()).map((gataRow) => ({
				gata: gataRow.gata,
				postnummer: normalizedPostnummer,
				postort: row.postort,
				kommun: row.kommun,
				lan: row.lan,
				personer: gataRow.personer,
				ratsit_link: gataRow.ratsit_link,
				is_queue: true,
				is_done: false,
			}));

			if (records.length === 0) {
				return 0;
			}

			const apiResponse = await postScrapedGator(records);
			return (apiResponse.summary.created ?? 0) + (apiResponse.summary.updated ?? 0) || records.length;
		}

		for (const gataRow of gatorMap.values()) {
			try {
				const { rows: existingRows } = await connection.query(
					`SELECT id
					 FROM sweden_gator
					 WHERE gata = $1 AND postnummer = $2 AND postort = $3 AND kommun = $4
					 LIMIT 1`,
					[
						gataRow.gata,
						normalizedPostnummer,
						row.postort,
						row.kommun,
					],
				);

				if (existingRows.length > 0) {
					await connection.query(
						`UPDATE sweden_gator
						 SET lan = $1, personer = $2, ratsit_link = $3, is_queue = true
						 WHERE id = $4`,
						[
							row.lan,
							gataRow.personer,
							gataRow.ratsit_link,
							existingRows[0].id,
						],
					);
				} else {
					await connection.query(
						`INSERT INTO sweden_gator (gata, postnummer, postort, kommun, lan, personer, ratsit_link, is_queue)
						 VALUES ($1, $2, $3, $4, $5, $6, $7, true)`,
						[
							gataRow.gata,
							normalizedPostnummer,
							row.postort,
							row.kommun,
							row.lan,
							gataRow.personer,
							gataRow.ratsit_link,
						],
					);
				}

				console.log(
					`  Upserted gata ${gataRow.gata} (${gataRow.personer} personer)`,
				);
			} catch (error) {
				console.error(`  Error processing gata ${gataRow.gata}:`, error.message);
			}
		}

		return gatorMap.size;
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
	console.log('Starting Ratsit gator scrape from sweden_postnummer...\n');
	const filters = parseCliFilters(process.argv.slice(2));
	const connection = filters.apiOnly ? null : await createDbConnection();

	try {
		const whereClauses = [
			`ratsit_link IS NOT NULL`,
			`ratsit_link != ''`,
			`is_done = false`,
		];
		const queryParams = [];

		if (filters.postort) {
			queryParams.push(filters.postort);
            whereClauses.push(`postort = $${queryParams.length}`);
        }

        if (filters.postnummer) {
            queryParams.push(filters.postnummer);
            whereClauses.push(`postnummer = $${queryParams.length}`);
        }

        if (filters.kommun) {
            queryParams.push(filters.kommun);
            whereClauses.push(`kommun = $${queryParams.length}`);
        }

		let postnummerRows = [];
		const isApiOnlyQueue = filters.apiOnly;

		if (isApiOnlyQueue) {
			console.log('API-only mode is active. Fetching queued sweden_postnummer rows from the API.');
		} else {
const query = `SELECT id, postnummer AS postnummer, postort AS postort, kommun, lan, ratsit_link
			 FROM sweden_postnummer
			 WHERE ${whereClauses.join(' AND ')}
			 ORDER BY id`;

			const { rows } = await connection.query(query, queryParams);
			postnummerRows = rows;

			if (filters.postort || filters.postnummer || filters.kommun || filters.lan) {
				console.log(`Applied filters: postort=${filters.postort || '-'} postnummer=${filters.postnummer || '-'} kommun=${filters.kommun || '-'} lan=${filters.lan || '-'}`);
			}

			console.log(`Found ${postnummerRows.length} postnummer rows to process.\n`);
		}

		let successCount = 0;
		let failCount = 0;

		if (isApiOnlyQueue) {
			let index = 0;
			while (true) {
				const row = await fetchNextPostnummerRow(filters);
				if (!row) {
					break;
				}

				index += 1;
				console.log(`Processing API queue row #${index}: ${row.postnummer || ''} ${row.postort || ''} (${row.kommun || ''})`);

				const gatorCount = await scrapeRatsitGator(row.ratsit_link, row, connection, filters.apiOnly);

				if (gatorCount !== null) {
					await markPostnummerProcessed(row.id, gatorCount);
					console.log(`  ✓ Done. gator=${gatorCount}. API-only mode, notified API that row ${row.id} is processed.\n`);
					successCount++;
				} else {
					console.log('  ✗ Failed. Skipping is_done update.\n');
					failCount++;
				}
			}

			console.log('\nAll postnummer rows processed.');
			console.log(`  Success: ${successCount}`);
			console.log(`  Failed:  ${failCount}`);
			return;
		}

		for (const [index, row] of postnummerRows.entries()) {
			console.log(
				`[${index + 1}/${postnummerRows.length}] Processing: ${row.postnummer} ${row.postort || ''} (${row.kommun || ''})`,
			);

			const gatorCount = await scrapeRatsitGator(row.ratsit_link, row, connection, filters.apiOnly);

			if (gatorCount !== null) {
				await connection.query(
					'UPDATE sweden_postnummer SET gator = $1, is_done = true WHERE id = $2',
					[gatorCount, row.id],
				);

				console.log(`  ✓ Done. gator=${gatorCount}. Marked is_done=1.\n`);
				successCount++;
			} else {
				console.log('  ✗ Failed. Skipping is_done update.\n');
				failCount++;
			}
		}

		console.log('\nAll postnummer rows processed.');
		console.log(`  Success: ${successCount}`);
		console.log(`  Failed:  ${failCount}`);
	} finally {
		if (connection) {
			await connection.end();
		}
	}
}

main().catch((err) => {
	console.error('Fatal error:', err);
	process.exit(1);
});
