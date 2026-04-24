<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SwedenPersonerResource;
use App\Models\SwedenPersoner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SwedenPersonerSearchController extends Controller
{
    /**
     * Search sweden persons with filtering, sorting, and field selection.
     *
     * Query parameters:
     * - filter[fornamn]=value (partial match)
     * - filter[efternamn]=value (partial match)
     * - filter[adress]=value (partial match)
     * - filter[postort]=value (partial match)
     * - filter[postnummer]=value (exact match)
     * - filter[kommun]=value (exact match)
     * - sort=fornamn,-efternamn (ascending/descending)
     * - fields=id,fornamn,efternamn,adress,telefon
     * - page=1&per_page=50
     */
    public function search(): ResourceCollection
    {
        $query = QueryBuilder::for(SwedenPersoner::class)
            ->allowedFilters(
                AllowedFilter::partial('fornamn'),    // First name (substring)
                AllowedFilter::partial('efternamn'),   // Last name (substring)
                AllowedFilter::partial('adress'),      // Address (substring)
                AllowedFilter::partial('postort'),     // Postal city (substring)
                AllowedFilter::exact('postnummer'),    // Postal code (exact)
                AllowedFilter::exact('kommun'),
                AllowedFilter::exact('kon'),
            )
            ->allowedSorts('fornamn', 'efternamn', 'adress', 'postort', 'postnummer', 'created_at')
            ->allowedFields('id', 'fornamn', 'efternamn', 'personnamn', 'adress', 'postnummer', 'postort', 'kommun', 'lan', 'telefon', 'kon', 'alder', 'is_active', 'created_at')
            ->paginate(request('per_page', 50))
            ->appends(request()->query());

        return SwedenPersonerResource::collection($query);
    }

    /**
     * Quick search by name or address across multiple fields.
     *
     * Query parameter: q=search_term (minimum 2 characters)
     */
    public function quickSearch(): JsonResponse
    {
        $search = request('q', '');

        if (strlen(trim($search)) < 2) {
            return response()->json([
                'message' => 'Search term must be at least 2 characters',
                'data' => [],
            ], 422);
        }

        $results = SwedenPersoner::where('fornamn', 'like', "%{$search}%")
            ->orWhere('efternamn', 'like', "%{$search}%")
            ->orWhere('adress', 'like', "%{$search}%")
            ->orWhere('personnamn', 'like', "%{$search}%")
            ->select('id', 'fornamn', 'efternamn', 'personnamn', 'adress', 'postnummer', 'postort', 'telefon', 'kommun')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => SwedenPersonerResource::collection($results),
            'count' => $results->count(),
        ]);
    }
}
