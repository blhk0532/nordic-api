#!/usr/bin/env node

import fs from 'fs';
import pg from 'pg';
import { chromium } from 'playwright';

const API_BASE_URL =
	process.env.SWEDEN_ADRESSER_API_URL ||
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

function normalizeOrder(value) {
	const normalized = String(value || '').trim().toLowerCase();
	return normalized === 'desc' ? 'desc' : 'asc';
}

function loadInputRowsFromFile(filePath) {
	const text = fs.readFileSync(filePath, 'utf8');
	const ext = filePath.split('.').pop().toLowerCase();

	if (ext === 'json') {
		const parsed = JSON.parse(text);
		if (!Array.isArray(parsed)) {
			throw new Error('Input file must contain a JSON array of row objects.');
		}

		return parsed;
	}

	const lines = text.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
	if (lines.length === 0) {
		return [];
	}

	const header = lines[0].split(',').map((column) => column.trim());

	return lines.slice(1).map((line) => {
		const values = line.split(',').map((value) => value.trim());
		const row = {};

		for (let index = 0; index < header.length; index++) {
			row[header[index]] = values[index] ?? null;
		}

		return row;
	});
}

function parseCliFilters(argv) {
	const args = Array.isArray(argv) ? argv : [];
	let postort = null;
	let postnummer = null;
	let kommun = null;
	let lan = null;
	let order = null;
	let apiOnly = false;
	let inputFile = null;
	let startUrl = null;
	let gata = null;

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

		if (arg.startsWith('--input-file=')) {
			inputFile = arg.slice('--input-file='.length).trim() || null;
			continue;
		}

		if (arg === '--input-file' && args[i + 1]) {
			inputFile = String(args[i + 1]).trim() || null;
			i++;
			continue;
		}

		if (arg.startsWith('--ratsit-link=')) {
			startUrl = arg.slice('--ratsit-link='.length).trim() || null;
			continue;
		}

		if (arg === '--ratsit-link' && args[i + 1]) {
			startUrl = String(args[i + 1]).trim() || null;
			i++;
			continue;
		}

		if (arg.startsWith('--gata=')) {
			gata = arg.slice('--gata='.length).trim() || null;
			continue;
		}

		if (arg === '--gata' && args[i + 1]) {
			gata = String(args[i + 1]).trim() || null;
			i++;
			continue;
		}

		if (arg.startsWith('--order=')) {
			order = normalizeOrder(arg.slice('--order='.length));
			continue;
		}

		if (arg === '--order' && args[i + 1]) {
			order = normalizeOrder(args[i + 1]);
			i++;
			continue;
		}
	}

	return { postort, postnummer, kommun, lan, order, apiOnly, inputFile, startUrl, gata };
}

async function postScrapedAdresser(records) {
	const response = await fetch(`${API_BASE_URL}/sweden-adresser/scraped`, {
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

function buildQueryString(params) {
	const searchParams = new URLSearchParams();
	for (const [key, value] of Object.entries(params)) {
		if (value !== null && value !== undefined && value !== '') {
			searchParams.set(key, value);
		}
	}

	return searchParams.toString();
}

async function fetchNextGatorRow(filters) {
	const query = buildQueryString({
		postort: filters.postort,
		postnummer: filters.postnummer,
		kommun: filters.kommun,
		lan: filters.lan,
		order: filters.order,
	});

	const response = await fetch(`${API_BASE_URL}/sweden-gator/next${query ? `?${query}` : ''}`, {
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

async function markGatorProcessed(id, adresserCount = null) {
	const payload = { id, is_done: true, is_queue: false };
	if (adresserCount !== null) {
		payload.adresser = adresserCount;
	}

	const response = await fetch(`${API_BASE_URL}/sweden-gator/processed`, {
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

async function scrapeRatsitAdresser(url, row, connection, apiOnly = false) {
	console.log(`\nScraping: ${url} (${row.gata || ''}, ${row.postnummer || ''} ${row.postort || ''})`);

	let browser = null;

	try {
		browser = await chromium.launch({
			headless: true,
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

		const adressRows = await page.evaluate(() => {
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

					const adress = text
						.replace(/\s*\(\d+\)\s*/g, ' ')
						.replace(/\d{3}\s*\d{2}/g, ' ')
						.replace(/\s+/g, ' ')
						.trim();

					if (!adress || adress.length < 2) {
						continue;
					}

					result.push({
						adress,
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

		const adressMap = new Map();

		for (const adressRow of adressRows) {
			const existing = adressMap.get(adressRow.adress);

			if (!existing) {
				adressMap.set(adressRow.adress, {
					adress: adressRow.adress,
					personer: adressRow.personer,
					ratsit_link: adressRow.ratsit_link,
				});
			} else {
				existing.personer += adressRow.personer;
			}
		}

		const normalizedPostnummer = normalizePostnummer(row.postnummer);

		const records = Array.from(adressMap.values()).map((adressRow) => ({
			adress: adressRow.adress,
			postnummer: normalizedPostnummer,
			postort: row.postort,
			kommun: row.kommun,
			lan: row.lan,
			personer: adressRow.personer,
			ratsit_link: adressRow.ratsit_link,
			is_queue: true,
		}));

		if (records.length === 0) {
			return 0;
		}

		if (apiOnly) {
			const apiResponse = await postScrapedAdresser(records);
			return (apiResponse.summary.created ?? 0) + (apiResponse.summary.updated ?? 0) || records.length;
		}

		for (const adressRow of adressMap.values()) {
			try {
			const { rows: existingRows } = await connection.query(
				`SELECT id
				 FROM sweden_adresser
				 WHERE adress = $1 AND postnummer = $2 AND postort = $3 AND kommun = $4
				 LIMIT 1`,
				[
					adressRow.adress,
					normalizedPostnummer,
					row.postort,
					row.kommun,
				],
			);

				if (existingRows.length > 0) {
					await connection.query(
						`UPDATE sweden_adresser
						 SET lan = $1, personer = $2, ratsit_link = $3, is_queue = true
						 WHERE id = $4`,
						[
							row.lan,
							adressRow.personer,
							adressRow.ratsit_link,
							existingRows[0].id,
						],
					);
				} else {
					await connection.query(
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

				console.log(
					`  Upserted adress ${adressRow.adress} (${adressRow.personer} personer)`,
				);
			} catch (error) {
				console.error(`  Error processing adress ${adressRow.adress}:`, error.message);
			}
		}

		return adressMap.size;
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
	console.log('Starting Ratsit adress scrape from sweden_gator...\n');
	const filters = parseCliFilters(process.argv.slice(2));

	if (filters.inputFile && !filters.apiOnly) {
		console.log('Input file mode implies api-only mode. Local DB updates will be skipped.');
		filters.apiOnly = true;
	}

	if (filters.startUrl) {
		if (!filters.apiOnly) {
			console.log('Manual start-url mode implies api-only mode. Local DB updates will be skipped.');
			filters.apiOnly = true;
		}
	}

	if (filters.apiOnly && !filters.inputFile && !filters.startUrl) {
		console.log('API-only mode is active, but rows will still be read from the local sweden_gator table unless --input-file or --ratsit-link is provided.');
	}

	const orderPararm = `ORDER BY id ${normalizeOrder(filters.order)}`;
	const connection = filters.inputFile || filters.startUrl || filters.apiOnly ? null : await createDbConnection();

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

		if (filters.lan) {
			queryParams.push(filters.lan);
			whereClauses.push(`lan = $${queryParams.length}`);
		}

		let gatorRows = [];
		let isApiOnlyQueue = false;

		if (filters.inputFile) {
			gatorRows = loadInputRowsFromFile(filters.inputFile);
			console.log(`Loaded ${gatorRows.length} rows from ${filters.inputFile}.`);
		} else if (filters.startUrl) {
			gatorRows = [
				{
					id: null,
					gata: filters.gata || null,
					postnummer: filters.postnummer || null,
					postort: filters.postort || null,
					kommun: filters.kommun || null,
					lan: filters.lan || null,
					ratsit_link: filters.startUrl,
				},
			];
			console.log(`Loaded 1 row from --ratsit-link for API-only mode.`);
		} else if (filters.apiOnly) {
			isApiOnlyQueue = true;
		} else {
			const query = `SELECT id, gata, postnummer, postort, kommun, lan, ratsit_link
				 FROM sweden_gator
				 WHERE ${whereClauses.join(' AND ')}
				 ${orderPararm}`;

			try {
				const { rows } = await connection.query(query, queryParams);
				gatorRows = rows;
			} catch (error) {
				if (error?.code === '42P01') {
					console.error('Local source table sweden_gator does not exist.');
					console.error('Use --input-file path/to/file.json or --path/to/file.csv, or --ratsit-link <url>, with --api-only when no local database table is available.');
					process.exit(1);
				}

				throw error;
			}

			if (filters.postort || filters.postnummer || filters.kommun || filters.lan) {
				console.log(`Applied filters: postort=${filters.postort || '-'} postnummer=${filters.postnummer || '-'} kommun=${filters.kommun || '-'} lan=${filters.lan || '-'}`);
			}
		}

		let successCount = 0;
		let failCount = 0;

		if (isApiOnlyQueue) {
			let index = 0;
			while (true) {
				const row = await fetchNextGatorRow(filters);
				if (!row) {
					break;
				}

				index += 1;
				console.log(`Processing API queue row #${index}: ${row.gata || ''} (${row.postnummer || ''} ${row.postort || ''})`);

				const adresserCount = await scrapeRatsitAdresser(row.ratsit_link, row, connection, filters.apiOnly);

				if (adresserCount !== null) {
					if (row.id) {
						await markGatorProcessed(row.id, adresserCount);
						console.log(`  ✓ Done. adresser=${adresserCount}. API-only mode, notified API that row ${row.id} is processed.\n`);
					} else {
						console.log(`  ✓ Done. adresser=${adresserCount}. API-only mode, result posted.\n`);
					}

					successCount++;
				} else {
					console.log('  ✗ Failed. Skipping is_done update.\n');
					failCount++;
				}
			}

			console.log('\nAll gator rows processed.');
			console.log(`  Success: ${successCount}`);
			console.log(`  Failed:  ${failCount}`);
			return;
		}

		console.log(`Found ${gatorRows.length} gator rows to process.\n`);

		for (const [index, row] of gatorRows.entries()) {
			console.log(
				`[${index + 1}/${gatorRows.length}] Processing: ${row.gata || ''} (${row.postnummer || ''} ${row.postort || ''})`,
			);

			const adresserCount = await scrapeRatsitAdresser(row.ratsit_link, row, connection, filters.apiOnly);

			if (adresserCount !== null) {
				if (!filters.apiOnly) {
					await connection.query(
						'UPDATE sweden_gator SET adresser = $1, is_done = true, is_queue = false WHERE id = $2',
						[adresserCount, row.id],
					);

					console.log(`  ✓ Done. adresser=${adresserCount}. Marked is_done=1.\n`);
				} else if (row.id) {
					await markGatorProcessed(row.id, adresserCount);
					console.log(`  ✓ Done. adresser=${adresserCount}. API-only mode, notified API that row ${row.id} is processed.\n`);
				} else {
					console.log(`  ✓ Done. adresser=${adresserCount}. API-only mode, result posted.\n`);
				}

				successCount++;
			} else {
				console.log('  ✗ Failed. Skipping is_done update.\n');
				failCount++;
			}
		}

		console.log('\nAll gator rows processed.');
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
