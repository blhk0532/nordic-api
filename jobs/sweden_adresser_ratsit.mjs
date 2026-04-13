#!/usr/bin/env node

import fs from 'fs';
import pg from 'pg';
import { chromium } from 'playwright';

const API_BASE_URL =
	process.env.SWEDEN_ADRESSER_API_URL ||
	process.env.SWEDEN_PERSONER_API_URL ||
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
	let apiOnly = false;
	let inputFile = null;
	let startUrl = null;
	let adress = null;

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

		if (arg.startsWith('--adress=')) {
			adress = arg.slice('--adress='.length).trim() || null;
			continue;
		}

		if (arg === '--adress' && args[i + 1]) {
			adress = String(args[i + 1]).trim() || null;
			i++;
			continue;
		}
	}

	return { postort, postnummer, kommun, lan, apiOnly, inputFile, startUrl, adress };
}

async function postScrapedPersons(records) {
	const response = await fetch(`${API_BASE_URL}/sweden-personer/scraped`, {
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

async function fetchNextAdresserRow(filters) {
	const query = buildQueryString({
		postort: filters.postort,
		postnummer: filters.postnummer,
		kommun: filters.kommun,
		lan: filters.lan,
	});

	const response = await fetch(`${API_BASE_URL}/sweden-adresser/next${query ? `?${query}` : ''}`, {
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

async function markAdresserProcessed(id, personCount = null) {
	const payload = { id, is_done: true, is_queue: false };
	if (personCount !== null) {
		payload.personer = personCount;
	}

	const response = await fetch(`${API_BASE_URL}/sweden-adresser/processed`, {
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

async function scrapeRatsitPersoner(url, row, connection) {
	console.log(`\nScraping: ${url} (${row.adress || ''}, ${row.postnummer || ''} ${row.postort || ''})`);

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

		const personerRows = await page.evaluate(() => {
			try {
				const result = [];

				const itemNodes = document.querySelectorAll('.tree-structure-result__item');

				for (const item of Array.from(itemNodes)) {
					const link = item.querySelector('a[href*="ratsit.se/"]');
					if (!link) {
						continue;
					}

					const nameNode = item.querySelector('.tree-structure-result__item-name');
					const rawName = (nameNode?.textContent || '').replace(/\s+/g, ' ').trim();

					if (!rawName) {
						continue;
					}

					const ageMatch = rawName.match(/,\s*(\d+)\s*$/);
					const alder = ageMatch ? parseInt(ageMatch[1], 10) : null;
					const personnamn = rawName.replace(/,\s*\d+\s*$/, '').trim();

					if (!personnamn) {
						continue;
					}

					const nameParts = personnamn.split(/\s+/).filter(Boolean);
					const fornamn = nameParts[0] || null;
					const efternamn = nameParts.length > 1 ? nameParts[nameParts.length - 1] : null;

					const addressNode = item.querySelector('.tree-structure-result__item-address');
					let adress = '';

					if (addressNode) {
						const cloned = addressNode.cloneNode(true);
						cloned.querySelectorAll('.search-list-name-address__city').forEach((cityNode) => {
							cityNode.remove();
						});

						adress = (cloned.textContent || '').replace(/\s+/g, ' ').trim();
					}

					let kon = null;
					let civilstand = null;

					const titleNodes = item.querySelectorAll('[title^="Är "]');

					for (const titleNode of Array.from(titleNodes)) {
						const title = (titleNode.getAttribute('title') || '').trim();

						if (!title) {
							continue;
						}

						if (/Är\s+(kvinna|man)/i.test(title) && !kon) {
							kon = title;
							continue;
						}

						if (!civilstand) {
							civilstand = title;
						}
					}

					result.push({
						adress,
						fornamn,
						efternamn,
						personnamn,
						kon,
						civilstand,
						alder,
						ratsit_link: link.href,
					});
				}

				return result;
			} catch (error) {
				console.error('Error in page evaluation:', error);
				return [];
			}
		});

		const personerMap = new Map();

		for (const personRow of personerRows) {
			const uniqueKey = `${personRow.adress}::${personRow.fornamn}::${personRow.efternamn}`;

			if (!personerMap.has(uniqueKey)) {
				personerMap.set(uniqueKey, personRow);
			}
		}

		const normalizedPostnummer = normalizePostnummer(row.postnummer);

		const records = Array.from(personerMap.values())
			.filter((personRow) => personRow.adress && personRow.fornamn && personRow.efternamn)
			.map((personRow) => ({
				adress: personRow.adress,
				postnummer: normalizedPostnummer,
				postort: row.postort,
				kommun: row.kommun,
				lan: row.lan,
				fornamn: personRow.fornamn,
				efternamn: personRow.efternamn,
				personnamn: personRow.personnamn,
				kon: personRow.kon,
				civilstand: personRow.civilstand,
				alder: personRow.alder,
				ratsit_link: personRow.ratsit_link,
				is_queue: true,
			}));

		if (records.length === 0) {
			return {
				found: 0,
				attemptedUpserts: 0,
				successfulUpserts: 0,
			};
		}

		const apiResponse = await postScrapedPersons(records);
		const successfulUpserts = (apiResponse.summary.created ?? 0) + (apiResponse.summary.updated ?? 0);

		return {
			found: records.length,
			attemptedUpserts: records.length,
			successfulUpserts,
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
	console.log('Starting Ratsit personer scrape from sweden_adresser...\n');
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
		console.log('API-only mode is active, but rows will still be read from the local sweden_adresser table unless --input-file or --ratsit-link is provided.');
	}

	const connection = filters.inputFile || filters.startUrl || filters.apiOnly ? null : await createDbConnection();
	let adressRows = [];

	try {
		if (filters.inputFile) {
			adressRows = loadInputRowsFromFile(filters.inputFile);
			console.log(`Loaded ${adressRows.length} rows from ${filters.inputFile}.`);
		} else if (filters.startUrl) {
			adressRows = [
				{
					id: null,
					adress: filters.adress || null,
					postnummer: filters.postnummer || null,
					postort: filters.postort || null,
					kommun: filters.kommun || null,
					lan: filters.lan || null,
					ratsit_link: filters.startUrl,
				},
			];
			console.log('Loaded 1 row from --ratsit-link for API-only mode.');
		} else if (filters.apiOnly) {
			let index = 0;
			let successCount = 0;
			let failCount = 0;

			while (true) {
				const row = await fetchNextAdresserRow(filters);
				if (!row) {
					break;
				}

				index += 1;
				console.log(`[${index}] Processing API queue row: ${row.adress || ''} (${row.postnummer || ''} ${row.postort || ''})`);

				const scrapeResult = await scrapeRatsitPersoner(row.ratsit_link, row, connection);

				if (
					scrapeResult !== null
					&& scrapeResult.successfulUpserts === scrapeResult.attemptedUpserts
				) {
					if (row.id) {
						await markAdresserProcessed(row.id, scrapeResult.found);
						console.log(`  ✓ Done. found=${scrapeResult.found}, saved=${scrapeResult.successfulUpserts}. API-only mode, notified API that row ${row.id} is processed.\n`);
					} else {
						console.log(`  ✓ Done. found=${scrapeResult.found}, saved=${scrapeResult.successfulUpserts}. API-only mode, result posted.\n`);
					}

					successCount++;
				} else {
					console.log(`  ✗ Failed. found=${scrapeResult?.found ?? 0}, attempted=${scrapeResult?.attemptedUpserts ?? 0}, saved=${scrapeResult?.successfulUpserts ?? 0}. Skipping is_done update.\n`);
					failCount++;
				}
			}

			console.log('\nAll adress rows processed.');
			console.log(`  Success: ${successCount}`);
			console.log(`  Failed:  ${failCount}`);
			return;
		} else {
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

			const query = `SELECT id, adress, postnummer, postort, kommun, lan, ratsit_link
				 FROM sweden_adresser
				 WHERE ${whereClauses.join(' AND ')}
				 ORDER BY id`;

			try {
				const queryResult = await connection.query(query, queryParams);
				adressRows = queryResult.rows;
			} catch (error) {
				if (error?.code === '42P01') {
					console.error('Local source table sweden_adresser does not exist.');
					console.error('Use --input-file path/to/file.json or --input-file path/to/file.csv, or --ratsit-link <url>, with --api-only when no local database table is available.');
					process.exit(1);
				}

				throw error;
			}

			if (filters.postort || filters.postnummer || filters.kommun || filters.lan) {
				console.log(`Applied filters: postort=${filters.postort || '-'} postnummer=${filters.postnummer || '-'} kommun=${filters.kommun || '-'} lan=${filters.lan || '-'}`);
			}
		}

		console.log(`Found ${adressRows.length} adress rows to process.\n`);

		let successCount = 0;
		let failCount = 0;

		for (const [index, row] of adressRows.entries()) {
			console.log(
				`[${index + 1}/${adressRows.length}] Processing: ${row.adress || ''} (${row.postnummer || ''} ${row.postort || ''})`,
			);

			const scrapeResult = await scrapeRatsitPersoner(row.ratsit_link, row, connection);

			if (
				scrapeResult !== null
				&& scrapeResult.successfulUpserts === scrapeResult.attemptedUpserts
			) {
				if (!filters.apiOnly) {
					await connection.query(
						'UPDATE sweden_adresser SET is_queue = false, is_done = true WHERE id = $1',
						[row.id],
					);

					console.log(
						`  ✓ Done. found=${scrapeResult.found}, saved=${scrapeResult.successfulUpserts}. Marked is_queue=0, is_done=1.\n`,
					);
				} else if (row.id) {
					await markAdresserProcessed(row.id, scrapeResult.found);
					console.log(`  ✓ Done. found=${scrapeResult.found}, saved=${scrapeResult.successfulUpserts}. API-only mode, notified API that row ${row.id} is processed.\n`);
				} else {
					console.log(`  ✓ Done. found=${scrapeResult.found}, saved=${scrapeResult.successfulUpserts}. API-only mode, result posted.\n`);
				}

				successCount++;
			} else {
				console.log(
					`  ✗ Failed. found=${scrapeResult?.found ?? 0}, attempted=${scrapeResult?.attemptedUpserts ?? 0}, saved=${scrapeResult?.successfulUpserts ?? 0}. Skipping is_done update.\n`,
				);
				failCount++;
			}

		}

		console.log('\nAll adress rows processed.');
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
