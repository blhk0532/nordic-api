<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\BookingOutcallQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BookingOutcallQueueController extends Controller
{
    /**
     * Return the latest phone for the authenticated user from booking_outcall_queues.
     */
    public function latestPhone(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['phone' => null], 401);
        }

        $queue = BookingOutcallQueue::where('user_id', $user->id)
            ->whereNotNull('phone')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'phone' => $queue?->phone,
        ]);
    }
}
