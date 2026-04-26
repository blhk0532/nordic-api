<?php

use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Api\DataPrivateController;
use App\Http\Controllers\Api\EniroDataController;
use App\Http\Controllers\Api\HittaDataController;
use App\Http\Controllers\Api\HittaForetagQueueController;
use App\Http\Controllers\Api\HittaQueueController;
use App\Http\Controllers\Api\HittaSeController;
use App\Http\Controllers\Api\JobQueueController;
use App\Http\Controllers\Api\MerinfoController;
use App\Http\Controllers\Api\MerinfoDataController;
use App\Http\Controllers\Api\MerinfoForetagQueueController;
use App\Http\Controllers\Api\MerinfoImportController;
use App\Http\Controllers\Api\MerinfoQueueController;
use App\Http\Controllers\Api\PeopleImportController;
use App\Http\Controllers\Api\PersonerDataController;
use App\Http\Controllers\Api\PostNummerApiController;
use App\Http\Controllers\Api\PostNummerForetagQueueController;
use App\Http\Controllers\Api\PostNummerQueController;
use App\Http\Controllers\Api\PostNummerQueueController;
use App\Http\Controllers\Api\RatsitDataController;
use App\Http\Controllers\Api\RatsitForetagQueueController;
use App\Http\Controllers\Api\RatsitKommunApiController;
use App\Http\Controllers\Api\RatsitQueueController;
use App\Http\Controllers\Api\SanctumAuthController;
use App\Http\Controllers\Api\SwedenAdresserQueueController;
use App\Http\Controllers\Api\SwedenGatorQueueController;
use App\Http\Controllers\Api\SwedenPersonerQueueController;
use App\Http\Controllers\Api\SwedenPostnummerApiController;
use App\Http\Controllers\Api\SwedenPostnummerQueueController;
use App\Http\Controllers\Api\UpplysningDataController;
use App\Http\Controllers\RingaDataOutcomeController;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// ========================================
// Authentication Routes (Public)
// ========================================

/**
 * Issue a new API token
 * POST /api/sanctum/token
 */
Route::post('/sanctum/token', [SanctumAuthController::class, 'issueToken'])
    ->middleware('throttle:15,1')
    ->name('sanctum.token');

/**
 * Protected Sanctum Routes
 */
Route::middleware('auth:sanctum')->group(function () {
    // Get authenticated user
    Route::get('/user', [SanctumAuthController::class, 'user']);

    // Token management
    Route::get('/sanctum/tokens', [SanctumAuthController::class, 'listTokens']);
    Route::post('/sanctum/revoke', [SanctumAuthController::class, 'revokeToken']);
    Route::delete('/sanctum/tokens/{tokenId}', [SanctumAuthController::class, 'revokeTokenById']);
});

// ========================================
// Application Routes
// ========================================

Route::post('/people/import', [PeopleImportController::class, 'import']);
Route::post('/merinfo/import', [MerinfoImportController::class, 'import']);

// BookingOutcallQueue API: latest phone for current user
use App\Http\Controllers\Api\BookingOutcallQueueController;

Route::middleware(['auth'])->get('/booking-outcall-queue/latest-phone', [BookingOutcallQueueController::class, 'latestPhone']);

Route::middleware(['auth'])->group(function () {
    Route::post('/ringa-data/{id}/outcome', [RingaDataOutcomeController::class, 'store']);
});

// API endpoints for Node.js scripts (scrapers)
// GET routes for fetching data
Route::get('/hitta-se', [HittaSeController::class, 'index']);
Route::get('/hitta-data', [HittaDataController::class, 'index']);
Route::get('/personer-data', [PersonerDataController::class, 'index']);
Route::get('/ratsit-data', [RatsitDataController::class, 'index']);

// POST routes for storing data
Route::post('/hitta-se', [HittaSeController::class, 'store']);
Route::post('/hitta-se/batch', [HittaSeController::class, 'batch']);
Route::post('/hitta-data', [HittaDataController::class, 'store']);
Route::post('/hitta-data/bulk', [HittaDataController::class, 'bulk']);
Route::post('/personer-data', [PersonerDataController::class, 'store']);
Route::post('/personer-data/bulk', [PersonerDataController::class, 'bulk']);
Route::post('/ratsit-data', [RatsitDataController::class, 'store']);
Route::post('/ratsit-data/bulk', [RatsitDataController::class, 'bulk']);
Route::get('/ratsit-data/bulk', [RatsitDataController::class, 'bulk']);

Route::apiResource('data-private', DataPrivateController::class);
Route::post('/data-private/bulk', [DataPrivateController::class, 'bulkStore']);

// SwedenPersoner bulk import endpoints
use App\Http\Controllers\Api\SwedenAdresserImportController;
use App\Http\Controllers\Api\SwedenGatorImportController;
use App\Http\Controllers\Api\SwedenPersonerImportController;
use App\Http\Controllers\Api\SwedenPersonerSearchController;

Route::post('/sweden-personer/import-json', [SwedenPersonerImportController::class, 'importJson']);
Route::post('/sweden-personer/import-file', [SwedenPersonerImportController::class, 'importFile']);
Route::post('/sweden-personer/scraped', [SwedenPersonerImportController::class, 'importScraped']);
Route::post('/sweden-personer/hitta', [SwedenPersonerImportController::class, 'storeHittaPerson']);
Route::post('/sweden-adresser/scraped', [SwedenAdresserImportController::class, 'importScraped']);
Route::post('/sweden-gator/scraped', [SwedenGatorImportController::class, 'importScraped']);

Route::get('/sweden-gator/next', [SwedenGatorQueueController::class, 'next']);
Route::post('/sweden-gator/processed', [SwedenGatorQueueController::class, 'markProcessed']);
Route::get('/sweden-adresser/next', [SwedenAdresserQueueController::class, 'next']);
Route::post('/sweden-adresser/processed', [SwedenAdresserQueueController::class, 'markProcessed']);
Route::get('/sweden-postnummer/next', [SwedenPostnummerQueueController::class, 'next']);
Route::post('/sweden-postnummer/processed', [SwedenPostnummerQueueController::class, 'markProcessed']);
Route::get('/sweden-postnummer/hitta-queue', [SwedenPostnummerQueueController::class, 'hittaQueue']);
Route::get('/sweden-postnummer/hitta-queue/next', [SwedenPostnummerQueueController::class, 'hittaQueueNext']);
Route::post('/sweden-postnummer/hitta-queue', [SwedenPostnummerQueueController::class, 'updateHittaQueue']);
Route::get('/sweden-personer/next', [SwedenPersonerQueueController::class, 'next']);

// SwedenPersoner search API endpoints
Route::get('/sweden-personer/search', [SwedenPersonerSearchController::class, 'search']);
Route::get('/sweden-personer/quick-search', [SwedenPersonerSearchController::class, 'quickSearch']);
Route::post('/sweden-personer/processed', [SwedenPersonerQueueController::class, 'markProcessed']);

// Public API routes (no authentication required)
// Use only the manual POST/GET routes for custom batch/bulk handlers
// apiResource would create redundant routes:
//   GET /hitta-data → HittaDataController@index (want custom)
//   POST /hitta-data → HittaDataController@store (want custom bulk)
// So we only declare the two resource methods we need: index + show
Route::get('/hitta-data/{hitta_datum}', [HittaDataController::class, 'show']);
Route::get('/hitta-personer-data/{hitta_datum}', [HittaDataController::class, 'show']);

Route::apiResource('eniro-data', EniroDataController::class);
Route::post('/eniro-data/bulk', [EniroDataController::class, 'bulkStore']);

Route::apiResource('upplysning-data', UpplysningDataController::class);
Route::post('/upplysning-data/bulk', [UpplysningDataController::class, 'bulkStore']);

// Ratsit data - public access for queue processing
// Use only the manual POST/GET routes for custom batch/bulk handlers
Route::get('/ratsit-data/{ratsit_datum}', [RatsitDataController::class, 'show']);
Route::get('/ratsit-personer-data/{ratsit_datum}', [RatsitDataController::class, 'show']);

// Merinfo data - public API routes (no authentication required)
Route::any('/merinfo-data/test', function (Request $request) {
    Log::channel('api')->debug('Merinfo data test endpoint hit', [
        'method' => $request->method(),
        'url' => $request->fullUrl(),
        'ip' => $request->ip(),
        'query' => $request->query(),
        'body' => $request->all(),
        'headers' => [
            'user-agent' => $request->userAgent(),
            'accept' => $request->header('accept'),
            'content-type' => $request->header('content-type'),
        ],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Merinfo test endpoint received and logged.',
    ]);
});

Route::withoutMiddleware([StartSession::class])->group(function () {
    Route::apiResource('merinfo-data', MerinfoDataController::class);
    Route::post('/merinfo-data/bulk', [MerinfoDataController::class, 'bulkStore']);
    Route::post('/merinfo-data/bulk-update-totals', [MerinfoDataController::class, 'bulkUpdateTotals']);
});

// Merinfo API routes for new Merinfo model
Route::apiResource('merinfo', MerinfoController::class);
Route::post('/merinfo/bulk', [MerinfoController::class, 'bulkStore']);

// Personer data - consolidated table for all sources
Route::post('/personer-data/bulk', [PersonerDataController::class, 'bulkStore']);

// Data private - requires authentication for individual operations but bulk is public for scraping

Route::apiResource('data-private', DataPrivateController::class);

Route::post('/data-private/bulk', [DataPrivateController::class, 'bulkStore']);

// Merinfo Queue REST + helpers
Route::get('/merinfo-queue', [MerinfoQueueController::class, 'index']);
Route::get('/merinfo-queue/{id}', [MerinfoQueueController::class, 'show'])->whereNumber('id');
Route::get('/merinfo-queue/run-personer', [MerinfoQueueController::class, 'runPersoner']);
Route::post('/merinfo-queue/bulk-update', [MerinfoQueueController::class, 'bulkUpdate']);
Route::put('/merinfo-queue/update/{postNummer}', [MerinfoQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');
// Merinfo companies (företag) queue endpoints
Route::get('/merinfo-foretag-queue', [MerinfoForetagQueueController::class, 'index']);
Route::get('/merinfo-foretag-queue/{id}', [MerinfoForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/merinfo-foretag-queue/run-foretag', [MerinfoForetagQueueController::class, 'runForetag']);
Route::post('/merinfo-foretag-queue/bulk-update', [MerinfoForetagQueueController::class, 'bulkUpdate']);
Route::put('/merinfo-foretag-queue/update/{postNummer}', [MerinfoForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Hitta companies (företag) queue endpoints
Route::get('/hitta-foretag-queue', [HittaForetagQueueController::class, 'index']);
Route::get('/hitta-foretag-queue/{id}', [HittaForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/hitta-foretag-queue/run-foretag', [HittaForetagQueueController::class, 'runForetag']);
Route::post('/hitta-foretag-queue/bulk-update', [HittaForetagQueueController::class, 'bulkUpdate']);
Route::put('/hitta-foretag-queue/update/{postNummer}', [HittaForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Ratsit companies (företag) queue endpoints
Route::get('/ratsit-foretag-queue', [RatsitForetagQueueController::class, 'index']);
Route::get('/ratsit-foretag-queue/{id}', [RatsitForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/ratsit-foretag-queue/run-foretag', [RatsitForetagQueueController::class, 'runForetag']);
Route::post('/ratsit-foretag-queue/bulk-update', [RatsitForetagQueueController::class, 'bulkUpdate']);
Route::put('/ratsit-foretag-queue/update/{postNummer}', [RatsitForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Postnummer foretag queue endpoints
Route::get('/postnummer-foretag-queue', [PostNummerForetagQueueController::class, 'index']);
Route::get('/postnummer-foretag-queue/{id}', [PostNummerForetagQueueController::class, 'show'])->whereNumber('id');
Route::get('/postnummer-foretag-queue/run-foretag', [PostNummerForetagQueueController::class, 'runForetag']);
Route::post('/postnummer-foretag-queue/bulk-update', [PostNummerForetagQueueController::class, 'bulkUpdate']);
Route::put('/postnummer-foretag-queue/update/{postNummer}', [PostNummerForetagQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

Route::apiResource('post-nummer', PostNummerApiController::class);
Route::get('/post-nums/merinfo-queue', [PostNummerApiController::class, 'getMerinfoQueue']);
Route::get('/post-nums/merinfo-count', [PostNummerApiController::class, 'getMerinfoCount']);
// Allow POST as well so external clients can submit data (e.g. merinfo requests)
Route::match(['get', 'put', 'post'], '/post-nums/by-code/{postnummer}', [PostNummerApiController::class, 'getByPostnummer']);
Route::put('/post-nummer/by-code/{postnummer}', [PostNummerApiController::class, 'updateByPostnummer']);
Route::put('/post-nums/update/{postNummer}', [PostNummerApiController::class, 'updateByPostnummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');
Route::post('/post-nummer/bulk-update', [PostNummerApiController::class, 'bulkUpdateByPostnummer']);
Route::post('/post-nummer/bulk-update-totals', [PostNummerApiController::class, 'bulkUpdateTotals']);
Route::post('/post-nummer/increment-counters/{postnummer}', [PostNummerApiController::class, 'incrementCounters']);
Route::get('/post-nummer/resume-info/{postnummer}', [PostNummerApiController::class, 'getResumeInfo']);
Route::post('/post-nummer/reset-counters/{postnummer}', [PostNummerApiController::class, 'resetCounters']);

Route::get('/postnummer-que/first', [PostNummerQueController::class, 'getFirstPostNummer']);
Route::post('/postnummer-que/first-next', [PostNummerQueController::class, 'firstNext']);
Route::post('/postnummer-que/process-next', [PostNummerQueController::class, 'processNext']);
Route::apiResource('postnummer-que', PostNummerQueController::class)->only(['update']);
Route::put('/postnummer-que/by-code/{postNummer}', [PostNummerQueController::class, 'updateByPostNummer']);
Route::post('/postnummer-que/bulk-update', [PostNummerQueController::class, 'bulkUpdate']);

Route::post('/hitta-se', [HittaSeController::class, 'store']);
Route::post('/hitta-se/batch', [HittaSeController::class, 'batchStore']);

// Hitta Queue API routes
Route::get('/hitta-queue/run-personer', [HittaQueueController::class, 'runPersoner']);
Route::post('/hitta-queue/bulk-update', [HittaQueueController::class, 'bulkUpdate']);
Route::put('/hitta-queue/update/{postNummer}', [HittaQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Ratsit Queue API routes
Route::get('/ratsit-queue/run-personer', [RatsitQueueController::class, 'runPersoner']);
Route::post('/ratsit-queue/bulk-update', [RatsitQueueController::class, 'bulkUpdate']);
Route::put('/ratsit-queue/update/{postNummer}', [RatsitQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Postnummer Queue API routes (comprehensive queue tracker)
Route::post('/postnummer-queue/bulk-update', [PostNummerQueueController::class, 'bulkUpdate']);
Route::put('/postnummer-queue/update/{postNummer}', [PostNummerQueueController::class, 'updateByPostNummer'])->where('postNummer', '[a-zA-Z0-9\s%]+');

// Sweden Postnummer API routes
Route::get('/sweden-postnummer/get-queue', [SwedenPostnummerApiController::class, 'getQueue']);
Route::match(['get', 'put', 'post'], '/sweden-postnummer/by-code/{postnummer}', [SwedenPostnummerApiController::class, 'getByCode'])->where('postnummer', '[a-zA-Z0-9\s%]+');
Route::put('/sweden-postnummer/update/{postnummer}', [SwedenPostnummerApiController::class, 'update'])->where('postnummer', '[a-zA-Z0-9\s%]+');
Route::post('/sweden-postnummer/batch-update', [SwedenPostnummerApiController::class, 'batchUpdate']);
Route::get('/sweden-postnummer/check-counts/{postnummer}', [SwedenPostnummerApiController::class, 'checkCounts'])->where('postnummer', '[a-zA-Z0-9\s%]+');

// Ratsit Kommuner API routes
Route::get('/ratsit-kommuner/list', [RatsitKommunApiController::class, 'list']);
Route::get('/ratsit-kommuner/stats', [RatsitKommunApiController::class, 'stats']);
Route::match(['get', 'put', 'post'], '/ratsit-kommuner/by-name/{kommun}', [RatsitKommunApiController::class, 'getByName'])->where('kommun', '[a-zA-Z0-9\s%]+');
Route::put('/ratsit-kommuner/update/{kommun}', [RatsitKommunApiController::class, 'update'])->where('kommun', '[a-zA-Z0-9\s%]+');
Route::post('/ratsit-kommuner/batch-update', [RatsitKommunApiController::class, 'batchUpdate']);

// Generic Job Queue API endpoints (Merinfo specific)
Route::get('/job-queue/get-merinfo', [JobQueueController::class, 'getMerinfo']);
Route::get('/job-queue/get-merinfo-postnummer', [JobQueueController::class, 'getMerinfoPostnummer']);
Route::put('/job-queue/put-merinfo', [JobQueueController::class, 'putMerinfo']);
Route::post('/job-queue/post-merinfo', [JobQueueController::class, 'putMerinfo']);
Route::delete('/job-queue/delete-merinfo-postnummer', [JobQueueController::class, 'deleteMerinfoPostnummer']);
Route::get('/job-queue/delete-merinfo-postnummer', [JobQueueController::class, 'deleteMerinfoPostnummer']);
Route::put('/job-queue/put-merinfo-postnummer', [JobQueueController::class, 'putMerinfoPostnummer']);
Route::post('/job-queue/post-merinfo-postnummer', [JobQueueController::class, 'putMerinfoPostnummer']);
Route::get('/job-queue/get-merinfo-count', [JobQueueController::class, 'getMerinfoPostnummerCount']);
Route::put('/job-queue/put-merinfo-count', [JobQueueController::class, 'putMerinfoCount']);
Route::post('/job-queue/post-merinfo-count', [JobQueueController::class, 'getMerinfoPostnummerCount']);
// Delete queue listing for merinfo
Route::get('/delete-queue/get-merinfo', [JobQueueController::class, 'deleteMerinfoPostnummer']);

Route::get('/job-queue/get-ratsit', [JobQueueController::class, 'getRatsit']);
Route::put('/job-queue/put-ratsit', [JobQueueController::class, 'putRatsit']);
Route::post('/job-queue/post-ratsit', [JobQueueController::class, 'putRatsit']);
Route::get('/job-queue/get-ratsit-count', [JobQueueController::class, 'getRatsitCount']);
Route::put('/job-queue/put-ratsit-count', [JobQueueController::class, 'putRatsitCount']);

// AI Chat API
Route::post('/ai/chat', [AiChatController::class, '__invoke'])->name('api.ai.chat');
