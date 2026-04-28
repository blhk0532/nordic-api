<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class HeroVideoBlock extends Block
{
    public static function getId(): string
    {
        return 'hero-video';
    }

    public static function getName(): string
    {
        return 'Hero with Video';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 15;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="bg-gray-900 text-white py-24 lg:py-40">
  <div class="container mx-auto px-4 text-center max-w-4xl">
    <h1 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">See It In Action</h1>
    <p class="text-xl text-gray-300 mb-10 max-w-2xl mx-auto">Watch how our platform transforms the way you build and publish websites.</p>
    <div class="relative mx-auto max-w-3xl rounded-2xl overflow-hidden shadow-2xl bg-black aspect-video mb-10">
      <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Product Demo" frameborder="0" allowfullscreen></iframe>
    </div>
    <a href="/get-started" class="inline-block bg-white text-gray-900 font-bold py-4 px-10 rounded-xl hover:bg-gray-100 transition-all text-lg shadow-lg">
      Start Building Free
    </a>
  </div>
</section>
HTML;
    }

    public function getFlowbiteTemplate(): string
    {
        return <<<'HTML'
<section class="bg-gray-900 dark:bg-gray-900">
  <div class="py-20 px-4 mx-auto max-w-screen-xl text-center lg:py-32">
    <h1 class="mb-6 text-4xl font-extrabold tracking-tight leading-none text-white md:text-5xl lg:text-6xl">See It In Action</h1>
    <p class="mb-10 text-lg font-normal text-gray-400 lg:text-xl sm:px-16 xl:px-48">Watch how our platform transforms the way you build and publish websites.</p>
    <div class="relative mx-auto max-w-3xl rounded-lg overflow-hidden shadow-2xl bg-black aspect-video mb-10">
      <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Product Demo" frameborder="0" allowfullscreen></iframe>
    </div>
    <a href="/get-started" class="inline-flex justify-center items-center py-3 px-8 text-base font-medium text-center text-gray-900 rounded-lg bg-white hover:bg-gray-100 focus:ring-4 focus:ring-gray-100">
      Start Building Free
    </a>
  </div>
</section>
HTML;
    }

    public function getShadcnTemplate(): string
    {
        return <<<'HTML'
<section class="bg-background py-24 lg:py-40">
  <div class="container mx-auto px-4 text-center max-w-4xl">
    <h1 class="text-4xl lg:text-6xl font-bold tracking-tight text-foreground mb-6">See It In Action</h1>
    <p class="text-xl text-muted-foreground mb-10 max-w-2xl mx-auto">Watch how our platform transforms the way you build and publish websites.</p>
    <div class="relative mx-auto max-w-3xl rounded-xl overflow-hidden border border-border shadow-lg bg-black aspect-video mb-10">
      <iframe class="w-full h-full" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Product Demo" frameborder="0" allowfullscreen></iframe>
    </div>
    <a href="/get-started" class="inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-11 px-8 shadow">
      Start Building Free
    </a>
  </div>
</section>
HTML;
    }
}
