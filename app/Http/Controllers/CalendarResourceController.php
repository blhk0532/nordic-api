<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarResourceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = User::query()
            ->where('role', 'service')
            ->where('status', 1);

        if ($request->has('limit')) {
            $query->limit($request->input('limit'));
        }

        $resources = $query->get()
            ->map(function (User $user) {
                return [
                    'id' => (string) $user->id,
                    'title' => $user->name,
                ];
            });

        return response()->json($resources);
    }
}
