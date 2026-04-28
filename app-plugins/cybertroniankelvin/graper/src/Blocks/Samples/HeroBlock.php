<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class HeroBlock extends Block
{
    public static function getId(): string
    {
        return 'hero';
    }

    public static function getName(): string
    {
        return 'Hero Section';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 10;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="relative bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-800 text-white py-24 lg:py-40 overflow-hidden">
  <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1557804506-669a67965ba0?w=1600')] bg-cover bg-center opacity-20"></div>
  <div class="container mx-auto px-4 relative z-10 text-center max-w-4xl">
    <span class="inline-block bg-indigo-500/30 text-indigo-200 text-sm font-medium px-4 py-1.5 rounded-full mb-6">New Feature Available</span>
    <h1 class="text-4xl lg:text-7xl font-bold mb-6 leading-tight">Build Beautiful Websites Without Code</h1>
    <p class="text-xl lg:text-2xl mb-10 text-indigo-100 max-w-2xl mx-auto">Drag, drop, and publish stunning web pages in minutes. No design skills required — just your ideas.</p>
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="/get-started" class="bg-white text-indigo-900 hover:bg-indigo-50 font-bold py-4 px-10 rounded-xl transition-all shadow-lg hover:shadow-xl text-lg">
        Get Started Free
      </a>
      <a href="/demo" class="border-2 border-white/30 hover:border-white/60 text-white font-semibold py-4 px-10 rounded-xl transition-all text-lg">
        Watch Demo
      </a>
    </div>
  </div>
</section>
HTML;
    }
}
