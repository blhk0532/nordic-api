<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class SimpleTextBlock extends Block
{
    public static function getId(): string
    {
        return 'simple-text';
    }

    public static function getName(): string
    {
        return 'Text Section';
    }

    public static function getCategory(): string
    {
        return 'Content';
    }

    public static function getOrder(): int
    {
        return 60;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-white">
  <div class="container mx-auto px-4 max-w-3xl">
    <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-6">Powerful features, simple interface</h2>
    <div class="prose prose-lg text-slate-600">
      <p>Our page builder combines the flexibility of custom development with the speed of no-code tools. Whether you're a solo entrepreneur or part of a large marketing team, you'll find everything you need to create stunning pages in minutes.</p>
      <p>Drag blocks from our library onto the canvas, customize the content inline, and publish when you're ready. Every page is automatically optimized for mobile devices and loads fast out of the box.</p>
    </div>
  </div>
</section>
HTML;
    }
}
