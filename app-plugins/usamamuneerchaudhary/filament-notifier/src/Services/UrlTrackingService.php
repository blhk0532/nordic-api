<?php

namespace Usamamuneerchaudhary\Notifier\Services;

use Illuminate\Http\RedirectResponse;

class UrlTrackingService
{
    /**
     * Safely redirect to URL with validation
     */
    public function safeRedirect(string $url): RedirectResponse
    {
        $dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:', 'about:'];
        $urlLower = strtolower(trim($url));

        foreach ($dangerousProtocols as $protocol) {
            if (str_starts_with($urlLower, $protocol)) {
                return redirect()->to('/');
            }
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            if (! str_starts_with($url, '/')) {
                $url = '/'.$url;
            }

            return redirect()->to($url);
        }

        // Only allow http/https protocols
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['scheme']) && ! in_array(strtolower($parsedUrl['scheme']), ['http', 'https'])) {
            return redirect()->to('/');
        }

        return redirect()->away($url);
    }

    /**
     * Rewrite URLs in content for click tracking
     */
    public function rewriteUrlsForTracking(string $content, string $token): string
    {
        $appUrl = rtrim(config('app.url', ''), '/');
        $trackingUrl = "{$appUrl}/notifier/track/click/{$token}";

        $pattern = '/href=["\']([^"\']+)["\']/i';

        return preg_replace_callback($pattern, function ($matches) use ($trackingUrl) {
            $originalUrl = $matches[1];

            if (str_contains($originalUrl, '/notifier/track/') ||
                str_starts_with($originalUrl, 'mailto:') ||
                str_starts_with($originalUrl, 'tel:')) {
                return $matches[0];
            }

            $encodedUrl = urlencode($originalUrl);
            $newUrl = "{$trackingUrl}?url={$encodedUrl}";

            return 'href="'.htmlspecialchars($newUrl, ENT_QUOTES, 'UTF-8').'"';
        }, $content);
    }
}
