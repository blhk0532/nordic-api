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

function parseCliFilters(argv) {
	const args = Array.isArray(argv) ? argv : [];
	let postort = null;
	let postnummer = null;
	let kommun = null;
	let lan = null;

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
		}
	}

	return { postort, postnummer, kommun, lan };
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

		let attemptedUpserts = 0;
		let successfulUpserts = 0;

		for (const personRow of personerMap.values()) {
			if (!personRow.adress || !personRow.fornamn || !personRow.efternamn) {
				continue;
			}

			attemptedUpserts++;

			try {
				await connection.query(
					`INSERT INTO sweden_personer
						(adress, postnummer, postort, fornamn, efternamn, personnamn, kon, civilstand, alder, kommun, ratsit_link, is_queue)
					 VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, true)
					 ON CONFLICT (adress, fornamn, efternamn) DO UPDATE SET
						postnummer = EXCLUDED.postnummer,
						postort = EXCLUDED.postort,
						personnamn = EXCLUDED.personnamn,
						kon = EXCLUDED.kon,
						civilstand = EXCLUDED.civilstand,
						alder = EXCLUDED.alder,
						kommun = EXCLUDED.kommun,
						ratsit_link = EXCLUDED.ratsit_link`,
					[
						personRow.adress,
						normalizedPostnummer,
						row.postort,
						personRow.fornamn,
						personRow.efternamn,
						personRow.personnamn,
						personRow.kon,
						personRow.civilstand,
						personRow.alder,
						row.kommun,
						personRow.ratsit_link,
					],
				);

				console.log(
					`  Upserted person ${personRow.personnamn} (${personRow.alder ?? 'n/a'} år)`,
				);
				successfulUpserts++;
			} catch (error) {
				console.error(
					`  Error processing person ${personRow.personnamn}:`,
					error.message,
				);
			}
		}

		return {
			found: personerMap.size,
			attemptedUpserts,
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

	const connection = await createDbConnection();

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

		const query = `SELECT id, adress, postnummer, postort, kommun, lan, ratsit_link
			 FROM sweden_adresser
			 WHERE ${whereClauses.join(' AND ')}
			 ORDER BY id`;

		const { rows: adressRows } = await connection.query(query, queryParams);

		if (filters.postort || filters.postnummer || filters.kommun || filters.lan) {
			console.log(`Applied filters: postort=${filters.postort || '-'} postnummer=${filters.postnummer || '-'} kommun=${filters.kommun || '-'} lan=${filters.lan || '-'}`);
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
await connection.query(
				'UPDATE sweden_adresser SET is_queue = false, is_done = true WHERE id = $1',
					[row.id],
				);

				console.log(
					`  ✓ Done. found=${scrapeResult.found}, saved=${scrapeResult.successfulUpserts}. Marked is_queue=0, is_done=1.\n`,
				);
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
		await connection.end();
	}
}

main().catch((err) => {
	console.error('Fatal error:', err);
	process.exit(1);
});
