<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Http\Controllers;

use CybertronianKelvin\Graper\Helpers\GraperHelper;
use CybertronianKelvin\Graper\Models\GraperPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class GraperPageController extends Controller
{
    public function update(Request $request, GraperPage $page): JsonResponse
    {
        $data = $request->validate([
            'html' => 'nullable|string',
            'css' => 'nullable|string',
            'project_data' => 'nullable|array',
        ]);

        $page->update($data);

        return response()->json(['success' => true]);
    }

    public function show(GraperPage $page): JsonResponse
    {
        return response()->json([
            'html' => $page->getAttributeValue('html') ?? '',
            'css' => $page->getAttributeValue('css') ?? '',
            'project_data' => $page->getAttributeValue('project_data') ?? [],
        ]);
    }

    public function display(string $slug, Request $request): Response
    {
        $page = GraperPage::where('slug', $slug)->firstOrFail();

        if (! $page->is_published && ! $request->user()?->can('view', $page)) {
            abort(404);
        }

        $html = $page->html;
        $css = $page->css;

        if (empty($html) && ! empty($page->project_data)) {
            $built = GraperHelper::buildContentFromProjectData($page->project_data);
            $html = $built['html'];
            $css = $built['css'];
        }

        return response()->view('graper::display', [
            'page' => $page,
            'html' => $html ?? '',
            'css' => $css ?? '',
        ]);
    }
}
