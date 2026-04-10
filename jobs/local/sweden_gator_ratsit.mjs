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
    let order = null;

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

        if (arg.startsWith('--order=')) {
			order = arg.slice('--order='.length).trim() || null;
			continue;
		}

		if (arg === '--order' && args[i + 1]) {
			order = String(args[i + 1]).trim() || null;
			i++;
		}
	}

	return { postort, postnummer, kommun, lan, order };
}

async function scrapeRatsitAdresser(url, row, connection) {
	console.log(`\nScraping: ${url} (${row.gata || ''}, ${row.postnummer || ''} ${row.postort || ''})`);

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
    const orderPararm = filters.order ? `ORDER BY id ${filters.order}` : 'ORDER BY id';
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

		const query = `SELECT id, gata, postnummer, postort, kommun, lan, ratsit_link
			 FROM sweden_gator
			 WHERE ${whereClauses.join(' AND ')}
			 ${orderPararm}`;

		const { rows: gatorRows } = await connection.query(query, queryParams);

		if (filters.postort || filters.postnummer || filters.kommun || filters.lan) {
			console.log(`Applied filters: postort=${filters.postort || '-'} postnummer=${filters.postnummer || '-'} kommun=${filters.kommun || '-'} lan=${filters.lan || '-'}`);
		}

		console.log(`Found ${gatorRows.length} gator rows to process.\n`);

		let successCount = 0;
		let failCount = 0;

		for (const [index, row] of gatorRows.entries()) {
			console.log(
				`[${index + 1}/${gatorRows.length}] Processing: ${row.gata || ''} (${row.postnummer || ''} ${row.postort || ''})`,
			);

			const adresserCount = await scrapeRatsitAdresser(row.ratsit_link, row, connection);

			if (adresserCount !== null) {
await connection.query(
				'UPDATE sweden_gator SET adresser = $1, is_done = true, is_queue = false WHERE id = $2',
					[adresserCount, row.id],
				);

				console.log(`  ✓ Done. adresser=${adresserCount}. Marked is_done=1.\n`);
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
		await connection.end();
	}
}

main().catch((err) => {
	console.error('Fatal error:', err);
	process.exit(1);
});
