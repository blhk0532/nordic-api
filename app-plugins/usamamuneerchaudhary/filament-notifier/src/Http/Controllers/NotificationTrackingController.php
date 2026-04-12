<?php

namespace Usamamuneerchaudhary\Notifier\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Usamamuneerchaudhary\Notifier\Services\AnalyticsService;
use Usamamuneerchaudhary\Notifier\Services\NotificationRepository;
use Usamamuneerchaudhary\Notifier\Services\UrlTrackingService;

class NotificationTrackingController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected NotificationRepository $notificationRepository,
        protected UrlTrackingService $urlTrackingService
    ) {}

    /**
     * Track email open - returns 1x1 transparent pixel
     */
    public function trackOpen(string $token): Response
    {
        try {
            $notification = $this->notificationRepository->findByToken($token);

            if (! $notification) {
                return $this->transparentPixel();
            }

            if (! $this->analyticsService->isOpenTrackingEnabled()) {
                return $this->transparentPixel();
            }

            $this->analyticsService->trackOpen($notification);

        } catch (\Exception $e) {
            Log::error('Failed to track notification open: '.$e->getMessage(), [
                'token' => $token,
            ]);
        }

        return $this->transparentPixel();
    }

    /**
     * Track link click - redirects to original URL
     */
    public function trackClick(string $token, Request $request)
    {
        try {
            $notification = $this->notificationRepository->findByToken($token);

            if (! $notification) {
                return redirect()->to($request->get('url', '/'));
            }

            if (! $this->analyticsService->isClickTrackingEnabled()) {
                return $this->urlTrackingService->safeRedirect($request->get('url', '/'));
            }

            $originalUrl = $request->get('url', '/');

            if (empty($originalUrl) || $originalUrl === '/') {
                return redirect()->to('/');
            }

            $this->analyticsService->trackClick($notification);

            return $this->urlTrackingService->safeRedirect($originalUrl);

        } catch (\Exception $e) {
            Log::error('Failed to track notification click: '.$e->getMessage(), [
                'token' => $token,
                'url' => $request->get('url'),
            ]);

            return $this->urlTrackingService->safeRedirect($request->get('url', '/'));
        }
    }

    /**
     * Return 1x1 transparent PNG pixel
     */
    protected function transparentPixel(): Response
    {
        $pixel = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
