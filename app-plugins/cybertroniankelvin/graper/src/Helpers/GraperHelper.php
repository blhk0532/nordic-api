<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Helpers;

class GraperHelper
{
    public static function stripLayerDirectives(string $css): string
    {
        $css = preg_replace('/@layer\s+[^{\s]*\{[^}]*}/', '', $css);
        $css = preg_replace('/@layer\s+[^{;]+;/', '', $css);
        $css = preg_replace('/@property\s+[^{]+{[^}]*}/', '', $css);

        return $css;
    }

    public static function buildContentFromProjectData(array $projectData): array
    {
        $pages = $projectData['pages'] ?? [];

        foreach ($pages as $page) {
            $frames = $page['frames'] ?? [];
            foreach ($frames as $frame) {
                $component = $frame['component'] ?? [];

                $bodyChildren = $component['components'] ?? [];

                if (empty($bodyChildren)) {
                    continue;
                }

                $html = '';
                foreach ($bodyChildren as $child) {
                    $html .= self::renderComponents($child);
                }

                $css = self::extractStyles($projectData);

                return ['html' => "<body>{$html}</body>", 'css' => $css];
            }
        }

        if (isset($projectData['layers'])) {
            $html = self::renderLayers($projectData['layers']);

            return ['html' => "<body>{$html}</body>", 'css' => ''];
        }

        return ['html' => '', 'css' => ''];
    }

    private static function renderComponents(array $component): string
    {
        $tag = $component['tagName'] ?? 'div';
        $classes = implode(' ', $component['classes'] ?? []);
        $attrs = '';
        if (isset($component['attributes'])) {
            foreach ($component['attributes'] as $k => $v) {
                $attrs .= " {$k}=\"".htmlspecialchars($v).'"';
            }
        }

        $content = '';
        if (isset($component['components'])) {
            foreach ($component['components'] as $child) {
                $content .= self::renderComponents($child);
            }
        }

        if (isset($component['content'])) {
            $content .= htmlspecialchars($component['content']);
        }

        return "<{$tag} class=\"{$classes}\"{$attrs}>{$content}</{$tag}>";
    }

    private static function renderLayers(array $layers): string
    {
        $html = '';
        foreach ($layers as $layer) {
            if (isset($layer['docEl'])) {
                $html .= self::renderComponents($layer['docEl']);
            }
            if (isset($layer['frames'])) {
                foreach ($layer['frames'] as $frame) {
                    $html .= self::renderComponents($frame['component'] ?? []);
                }
            }
        }

        return $html;
    }

    private static function extractStyles(array $projectData): string
    {
        $css = "* { box-sizing: border-box; } body {margin: 0;}\n";

        if (isset($projectData['styles'])) {
            foreach ($projectData['styles'] as $style) {
                if (is_array($style)) {
                    $selectors = array_keys($style);
                    foreach ($selectors as $selector) {
                        $rules = $style[$selector];
                        if (is_array($rules)) {
                            $ruleStr = '';
                            foreach ($rules as $prop => $val) {
                                $ruleStr .= "{$prop}: {$val}; ";
                            }
                            if ($ruleStr) {
                                $css .= "{$selector} { {$ruleStr}}\n";
                            }
                        }
                    }
                }
            }
        }

        return $css;
    }
}