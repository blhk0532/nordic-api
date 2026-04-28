<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Http\Controllers;

use CybertronianKelvin\Graper\Blocks\BlockRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class GraperBlockController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(BlockRegistry::make()->toArray());
    }
}
