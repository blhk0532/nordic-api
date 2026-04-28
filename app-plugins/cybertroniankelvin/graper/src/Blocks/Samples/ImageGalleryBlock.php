<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class ImageGalleryBlock extends Block
{
    public static function getId(): string
    {
        return 'image-gallery';
    }

    public static function getName(): string
    {
        return 'Image Gallery';
    }

    public static function getCategory(): string
    {
        return 'Media';
    }

    public static function getOrder(): int
    {
        return 55;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-16 bg-white">
  <div class="container mx-auto px-4 max-w-6xl">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-slate-900 mb-3">Our Gallery</h2>
      <p class="text-slate-600 max-w-xl mx-auto">A glimpse into our world — the moments, projects, and spaces that define us.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div class="aspect-square bg-slate-200 rounded-xl overflow-hidden flex items-center justify-center text-slate-400 text-sm font-medium hover:bg-slate-300 transition-colors cursor-pointer">Image 1</div>
      <div class="aspect-square bg-slate-300 rounded-xl overflow-hidden flex items-center justify-center text-slate-500 text-sm font-medium hover:bg-slate-400 transition-colors cursor-pointer">Image 2</div>
      <div class="aspect-square bg-indigo-100 rounded-xl overflow-hidden flex items-center justify-center text-indigo-400 text-sm font-medium hover:bg-indigo-200 transition-colors cursor-pointer">Image 3</div>
      <div class="aspect-square bg-emerald-100 rounded-xl overflow-hidden flex items-center justify-center text-emerald-400 text-sm font-medium hover:bg-emerald-200 transition-colors cursor-pointer">Image 4</div>
      <div class="aspect-square bg-amber-100 rounded-xl overflow-hidden flex items-center justify-center text-amber-400 text-sm font-medium hover:bg-amber-200 transition-colors cursor-pointer">Image 5</div>
      <div class="aspect-square bg-rose-100 rounded-xl overflow-hidden flex items-center justify-center text-rose-400 text-sm font-medium hover:bg-rose-200 transition-colors cursor-pointer">Image 6</div>
    </div>
  </div>
</section>
HTML;
    }

    public function getFlowbiteTemplate(): string
    {
        return <<<'HTML'
<section class="bg-white dark:bg-gray-900 py-16">
  <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
    <div class="mx-auto max-w-screen-sm text-center mb-8 lg:mb-16">
      <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">Our Gallery</h2>
      <p class="font-light text-gray-500 sm:text-xl dark:text-gray-400">A glimpse into our world — the moments, projects, and spaces that define us.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors cursor-pointer">Image 1</div>
      <div class="h-48 bg-gray-300 dark:bg-gray-600 rounded-lg flex items-center justify-center text-gray-500 hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors cursor-pointer">Image 2</div>
      <div class="h-48 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center text-blue-400 hover:bg-blue-200 transition-colors cursor-pointer">Image 3</div>
      <div class="h-48 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center text-green-400 hover:bg-green-200 transition-colors cursor-pointer">Image 4</div>
      <div class="h-48 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center text-yellow-400 hover:bg-yellow-200 transition-colors cursor-pointer">Image 5</div>
      <div class="h-48 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-200 transition-colors cursor-pointer">Image 6</div>
    </div>
  </div>
</section>
HTML;
    }

    public function getShadcnTemplate(): string
    {
        return <<<'HTML'
<section class="py-16 bg-background">
  <div class="container mx-auto px-4 max-w-6xl">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold tracking-tight text-foreground mb-3">Our Gallery</h2>
      <p class="text-muted-foreground max-w-xl mx-auto">A glimpse into our world — the moments, projects, and spaces that define us.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div class="aspect-square rounded-lg border border-border bg-muted flex items-center justify-center text-muted-foreground text-sm hover:bg-accent transition-colors cursor-pointer">Image 1</div>
      <div class="aspect-square rounded-lg border border-border bg-muted/60 flex items-center justify-center text-muted-foreground text-sm hover:bg-accent transition-colors cursor-pointer">Image 2</div>
      <div class="aspect-square rounded-lg border border-border bg-primary/5 flex items-center justify-center text-primary/40 text-sm hover:bg-primary/10 transition-colors cursor-pointer">Image 3</div>
      <div class="aspect-square rounded-lg border border-border bg-primary/10 flex items-center justify-center text-primary/50 text-sm hover:bg-primary/20 transition-colors cursor-pointer">Image 4</div>
      <div class="aspect-square rounded-lg border border-border bg-muted flex items-center justify-center text-muted-foreground text-sm hover:bg-accent transition-colors cursor-pointer">Image 5</div>
      <div class="aspect-square rounded-lg border border-border bg-muted/60 flex items-center justify-center text-muted-foreground text-sm hover:bg-accent transition-colors cursor-pointer">Image 6</div>
    </div>
  </div>
</section>
HTML;
    }
}
