<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

class GoogleStaticMap
{
    protected string $apiKey;

    protected ?string $secret = null;

    protected string $baseUrl = 'https://maps.googleapis.com/maps/api/staticmap';

    protected float $centerLat = 0;

    protected float $centerLng = 0;

    protected int $zoom = 10;

    protected string $mapType = 'roadmap';

    protected int $width = 400;

    protected int $height = 300;

    protected int $scale = 1;

    protected array $markers = [];

    protected ?string $language = null;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function setCenterLatLng(float $lat, float $lng): static
    {
        $this->centerLat = $lat;
        $this->centerLng = $lng;

        return $this;
    }

    public function setZoom(int $zoom): static
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function setMapType(string $type): static
    {
        $this->mapType = $type;

        return $this;
    }

    public function setSize(int $width, int $height): static
    {
        $this->width = $width;
        $this->height = $height;

        return $this;
    }

    public function setScale(int $scale): static
    {
        $this->scale = $scale;

        return $this;
    }

    public function setSecret(string $secret): static
    {
        $this->secret = $secret;

        return $this;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function addMarkerLatLng(float $lat, float $lng, string $size = 'normal', string $color = 'red', ?string $label = null): static
    {
        $marker = "{$color}";

        if ($label) {
            $marker .= ":{$label}";
        }

        $marker .= "|{$lat},{$lng}";

        $this->markers[] = $marker;

        return $this;
    }

    public function addMarkerLatLngWithIcon(float $lat, float $lng, string $iconUrl): static
    {
        $this->markers[] = "icon:{$iconUrl}|{$lat},{$lng}";

        return $this;
    }

    public function make(): string
    {
        $params = [
            'key' => $this->apiKey,
            'center' => "{$this->centerLat},{$this->centerLng}",
            'zoom' => $this->zoom,
            'maptype' => $this->mapType,
            'size' => "{$this->width}x{$this->height}",
            'scale' => $this->scale,
        ];

        if (! empty($this->markers)) {
            $params['markers'] = implode('&markers=', array_map(fn ($m) => $this->encodeMarker($m), $this->markers));
        }

        if ($this->language) {
            $params['language'] = $this->language;
        }

        $url = $this->baseUrl.'?'.http_build_query($params);

        if ($this->secret) {
            $url .= '&signature='.$this->generateSignature($url);
        }

        return $url;
    }

    protected function encodeMarker(string $marker): string
    {
        return str_replace(['|', ':'], ['%7C', '%3A'], $marker);
    }

    protected function generateSignature(string $url): string
    {
        $url = str_replace($this->baseUrl.'?', '', $url);
        $url = str_replace('key='.$this->apiKey, '', $url);
        parse_str($url, $params);

        $stringToSign = '/'.$this->baseUrl.'?'.http_build_query($params);

        return $this->signUrl($stringToSign, $this->secret);
    }

    protected function signUrl(string $url, string $key): string
    {
        $decodedKey = base64_decode(strtr($key, '-_', '+/'));

        $hash = hash_hmac('sha256', $url, $decodedKey, true);

        return strtr(base64_encode($hash), '+/', '-_');
    }
}
