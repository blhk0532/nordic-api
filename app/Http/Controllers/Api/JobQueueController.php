<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RunMerinfoScript;
use App\Models\PostNum;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class JobQueueController extends Controller
{
    /**
     * GET /api/job-queue/get-merinfo
     * Return list of merinfo batch names (postnummer) with status.
     */
    public function getMerinfo(Request $request)
    {
        // Fetch all batches whose name is 5 digits (normalized postnummer) and have at least one pending job
        $batches = DB::table('job_batches')
            ->select(['id', 'name', 'total_jobs', 'pending_jobs', 'failed_jobs', 'created_at', 'finished_at', 'cancelled_at'])
            ->whereRaw("name REGEXP '^[0-9]{5}$'")
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $data = $batches->map(function ($b) {
            $status = 'pending';
            if ($b->cancelled_at) {
                $status = 'cancelled';
            } elseif ($b->finished_at) {
                $status = 'finished';
            } elseif ((int) $b->failed_jobs > 0 && (int) $b->pending_jobs === 0) {
                $status = 'failed';
            } elseif ((int) $b->pending_jobs === 0) {
                $status = 'finished';
            }

            return [
                'id' => $b->id,
                'postnummer' => $b->name,
                'status' => $status,
                'total_jobs' => (int) $b->total_jobs,
                'pending_jobs' => (int) $b->pending_jobs,
                'failed_jobs' => (int) $b->failed_jobs,
                'created_at' => $b->created_at,
                'finished_at' => $b->finished_at,
                'cancelled_at' => $b->cancelled_at,
            ];
        })->values();

        return response()->json([
            'summary' => [
                'count' => $data->count(),
                'finished' => $data->where('status', 'finished')->count(),
                'pending' => $data->where('status', 'pending')->count(),
                'failed' => $data->where('status', 'failed')->count(),
                'cancelled' => $data->where('status', 'cancelled')->count(),
            ],
            'batches' => $data,
        ]);
    }

    public function getRatsit(Request $request)
    {

        return response()->json([

            'searchquery' => 'thomas denk',

        ]);
    }

    /**
     * GET /api/job-queue/get-merinfo-count
     * Return first PostNum where merinfo_personer_count = 1 and clear the flag.
     */
    public function getMerinfoCount(Request $request)
    {
        $record = PostNum::where('merinfo_personer_count', 1)
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'No post nummer found with merinfo_personer_count = 1',
                'post_nummer' => null,
            ], 404);
        }

        // Clear the flag so other workers don't fetch it multiple times
        PostNum::query()
            ->where('post_nummer', $record->post_nummer)
            ->update(['merinfo_personer_count' => false]);

        return response()->json([
            'message' => 'Found post nummer with merinfo_personer_count = 1',
            'post_nummer' => $record->post_nummer,
            'data' => $record,
        ]);
    }

    /**
     * PUT /api/job-queue/put-merinfo-count
     * Body: { postnummer: "15331", merinfo_personer_total?: int, merinfo_personer_saved?: int, merinfo_personer_count?: bool }
     */
    public function putMerinfoCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'postnummer' => ['required', 'string', 'regex:/^[0-9\s]{5,6}$/'],
            'merinfo_personer_total' => ['nullable', 'integer'],
            'merinfo_personer_saved' => ['nullable', 'integer'],
            'merinfo_personer_count' => ['nullable', 'boolean'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $raw = $request->input('postnummer');
        $normalized = preg_replace('/\s+/', '', $raw);
        if (mb_strlen($normalized) !== 5 || ! preg_match('/^[0-9]{5}$/', $normalized)) {
            return response()->json(['error' => 'Invalid postnummer format after normalization. Expect 5 digits.'], 422);
        }

        // Normalize and lookup by possible formats
        $record = PostNum::where('post_nummer', $normalized)->first();
        if (! $record) {
            // Try with space format
            $withSpace = mb_substr($normalized, 0, 3).' '.mb_substr($normalized, 3);
            $record = PostNum::where('post_nummer', $withSpace)->first();
        }
        if (! $record) {
            return response()->json(['message' => 'Post nummer not found', 'post_nummer' => $normalized], 404);
        }

        $updateData = [];
        if ($request->has('merinfo_personer_total')) {
            $updateData['merinfo_personer_total'] = $request->input('merinfo_personer_total');
        }
        if ($request->has('merinfo_personer_saved')) {
            $updateData['merinfo_personer_saved'] = $request->input('merinfo_personer_saved');
        }
        if ($request->has('merinfo_personer_count')) {
            $updateData['merinfo_personer_count'] = $request->input('merinfo_personer_count');
        }

        if (empty($updateData)) {
            return response()->json(['error' => 'No update fields provided'], 422);
        }

        $record->update($updateData);

        return response()->json([
            'message' => 'Post nummer updated',
            'post_nummer' => $record->post_nummer,
            'updated_fields' => array_keys($updateData),
            'data' => $record,
        ]);
    }

    /**
     * GET /api/delete-queue/get-merinfo
     * List job batches for merinfo (by batch name matching a 5-digit postal code)
     */
    public function getDeleteQueueMerinfo(Request $request)
    {
        // Fetch batches where the name is 5 digits (postnummer)
        $batches = DB::table('job_batches')
            ->select(['id', 'name', 'total_jobs', 'pending_jobs', 'failed_jobs', 'created_at', 'finished_at', 'cancelled_at'])
            ->whereRaw("name REGEXP '^[0-9]{5}$'")
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $data = $batches->map(function ($b) {
            $status = 'pending';
            if ($b->cancelled_at) {
                $status = 'cancelled';
            } elseif ($b->finished_at) {
                $status = 'finished';
            } elseif ((int) $b->failed_jobs > 0 && (int) $b->pending_jobs === 0) {
                $status = 'failed';
            } elseif ((int) $b->pending_jobs === 0) {
                $status = 'finished';
            }

            return [
                'id' => $b->id,
                'postnummer' => $b->name,
                'status' => $status,
                'total_jobs' => (int) $b->total_jobs,
                'pending_jobs' => (int) $b->pending_jobs,
                'failed_jobs' => (int) $b->failed_jobs,
                'created_at' => $b->created_at,
                'finished_at' => $b->finished_at,
                'cancelled_at' => $b->cancelled_at,
            ];
        })->values();

        return response()->json([
            'summary' => [
                'count' => $data->count(),
                'finished' => $data->where('status', 'finished')->count(),
                'pending' => $data->where('status', 'pending')->count(),
                'failed' => $data->where('status', 'failed')->count(),
                'cancelled' => $data->where('status', 'cancelled')->count(),
            ],
            'batches' => $data,
        ]);
    }

    /**
     * GET /api/job-queue/get-merinfo-postnummer
     * Return first job from jobs table where queue = merinfo.
     * Updates job status to "Started" when queried.
     */
    public function getMerinfoPostnummer(Request $request)
    {
        $job = DB::table('jobs')
            ->where('queue', 'merinfo')
            ->orderBy('id', 'asc')
            ->first();

        $jobQueue = DB::table('post_nums')
            ->where('merinfo_personer_queue', 1)
            ->orderBy('id', 'asc')
            ->first();

        if (! $job && ! $jobQueue) {
            return response()->json([
                'message' => 'No merinfo jobs found',
                'job' => 0,
                'postnummer' => 0,
            ], 404);
        }

        if ($job) {
            // Update job status to "Started"
            DB::table('jobs')
                ->where('id', $job->id)
                ->update(['status' => 'Started']);

            return response()->json([
                'message' => 'First merinfo job found',
                'job' => [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'name' => $job->name,
                    'status' => 'Started', // Return the updated status
                    'attempts' => $job->attempts,
                    'reserved_at' => $job->reserved_at,
                    'available_at' => $job->available_at,
                    'created_at' => $job->created_at,
                ],
                'postnummer' => $job->name,
            ]);
        }

        if ($jobQueue) {
            // Update job status to "Started"
            DB::table('post_nums')
                ->where('id', $jobQueue->id)
                ->update(['merinfo_personer_queue' => 0]);

            return response()->json([
                'message' => 'First merinfo job found',
                'id' => [
                    'id' => $jobQueue->id,
                ],
                'postnummer' => $jobQueue->id,
            ]);

        }
    }

    public function getMerinfoPostnummerCount(Request $request)
    {
        $job = DB::table('jobs')
            ->where('queue', 'merinfo')
            ->orWhere('queue', 'merinfo')
            ->orderBy('id', 'asc')
            ->first();

        if (! $job) {
            return response()->json([
                'message' => 'No merinfo jobs found',
                'job' => null,
                'postnummer' => 0,
            ], 404);
        }

        // Update job status to "Started"
        DB::table('jobs')
            ->where('id', $job->id)
            ->update(['status' => 'Started']);

        return response()->json([
            'message' => 'First merinfo job found',
            'job' => [
                'id' => $job->id,
                'queue' => $job->queue,
                'name' => $job->name,
                'status' => 'Started', // Return the updated status
                'attempts' => $job->attempts,
                'reserved_at' => $job->reserved_at,
                'available_at' => $job->available_at,
                'created_at' => $job->created_at,
            ],
            'postnummer' => $job->name,
        ]);
    }

    /**
     * PUT /api/job-queue/put-merinfo
     * Body: { postnummer: "15331", action: "requeue|cancel" }
     */
    public function putMerinfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'postnummer' => ['required', 'string', 'regex:/^[0-9\s]{5,6}$/'], // allow space variant
            'action' => ['required', 'in:requeue,cancel'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $raw = $request->input('postnummer');
        $normalized = preg_replace('/\s+/', '', $raw); // remove spaces
        if (mb_strlen($normalized) !== 5 || ! preg_match('/^[0-9]{5}$/', $normalized)) {
            return response()->json(['error' => 'Invalid postnummer format after normalization. Expect 5 digits.'], 422);
        }

        $action = $request->input('action');

        // Find existing batch by name
        $existing = DB::table('job_batches')->where('name', $normalized)->first();

        if ($action === 'cancel') {
            if (! $existing) {
                return response()->json(['message' => 'No existing batch to cancel for postnummer '.$normalized], 404);
            }

            try {
                $batch = Bus::findBatch($existing->id);
                if ($batch) {
                    $batch->cancel();

                    return response()->json(['message' => 'Batch cancelled', 'postnummer' => $normalized]);
                }
            } catch (Throwable $e) {
                Log::warning('Failed to cancel batch '.$existing->id.': '.$e->getMessage());

                return response()->json(['error' => 'Failed to cancel batch'], 500);
            }
        }

        if ($action === 'requeue') {
            // Avoid duplicate active batch: if existing pending and not finished/cancelled, skip.
            if ($existing && ! $existing->finished_at && ! $existing->cancelled_at && (int) $existing->pending_jobs > 0) {
                return response()->json(['message' => 'Active batch already exists for '.$normalized], 409);
            }

            try {
                $pending = Bus::batch([
                    new RunMerinfoScript($normalized),
                ])->name($normalized)
                    ->onQueue('merinfo')
                    ->dispatch();

                return response()->json([
                    'message' => 'Batch queued',
                    'postnummer' => $normalized,
                    'batch_id' => $pending->id,
                    'status' => 'pending',
                ]);
            } catch (Throwable $e) {
                Log::error('Failed to queue Merinfo batch for '.$normalized.': '.$e->getMessage());

                return response()->json(['error' => 'Failed to queue batch', 'details' => $e->getMessage()], 500);
            }
        }

        return response()->json(['message' => 'Unhandled action'], 400);
    }

    /**
     * DELETE /api/job-queue/delete-merinfo-postnummer
     * Query param: ?postnummer=15331
     * Deletes all jobs from jobs table where queue=merinfo and name=postnummer.
     */
    public function deleteMerinfoPostnummer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'postnummer' => ['required', 'string', 'regex:/^[0-9\s]{5,6}$/'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $raw = $request->input('postnummer');
        $normalized = preg_replace('/\s+/', '', $raw);
        if (mb_strlen($normalized) !== 5 || ! preg_match('/^[0-9]{5}$/', $normalized)) {
            return response()->json(['error' => 'Invalid postnummer format after normalization. Expect 5 digits.'], 422);
        }

        $deleted = DB::table('jobs')
            ->where('queue', 'merinfo')
            ->orWhere('queue', 'merinfo')
            ->orWhere('queue', 'merinfo')
            ->where('name', $normalized)
            ->delete();

        return response()->json([
            'message' => 'Jobs deleted',
            'postnummer' => $normalized,
            'deleted' => $normalized,
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * PUT /api/job-queue/put-merinfo-postnummer
     * Body: { postnummer: "15331", status: "Started|Completed|Failed", ... }
     * Updates job in jobs table where queue=merinfo and name=postnummer.
     */
    public function putMerinfoPostnummer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'postnummer' => ['required', 'string', 'regex:/^[0-9\s]{5,6}$/'],
            'status' => ['nullable', 'string', 'max:255'],
            'attempts' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $raw = $request->input('postnummer');
        $normalized = preg_replace('/\s+/', '', $raw);
        if (mb_strlen($normalized) !== 5 || ! preg_match('/^[0-9]{5}$/', $normalized)) {
            return response()->json(['error' => 'Invalid postnummer format after normalization. Expect 5 digits.'], 422);
        }

        $updateData = [];
        if ($request->has('status')) {
            $updateData['status'] = $request->input('status');
        }
        if ($request->has('attempts')) {
            $updateData['attempts'] = $request->input('attempts');
        }

        if (empty($updateData)) {
            return response()->json(['error' => 'No update fields provided'], 422);
        }

        $updated = DB::table('jobs')
            ->where('queue', 'merinfo')
            ->where('name', $normalized)
            ->update($updateData);

        return response()->json([
            'message' => 'Jobs updated',
            'postnummer' => $normalized,
            'updated_count' => $updated,
            'updated_fields' => array_keys($updateData),
        ]);
    }
}
