<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class CtaBlock extends Block
{
    public static function getId(): string
    {
        return 'cta';
    }

    public static function getName(): string
    {
        return 'Call to Action Banner';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 20;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="bg-gradient-to-r from-indigo-600 to-purple-600 py-20">
  <div class="container mx-auto px-4 text-center max-w-3xl">
    <h2 class="text-3xl lg:text-5xl font-bold text-white mb-6">Ready to transform your workflow?</h2>
    <p class="text-xl text-white/90 mb-10">Join over 10,000 teams who build faster with our platform. Start your free trial today — no credit card required.</p>
    <a href="/signup" class="inline-block bg-white text-indigo-700 font-bold py-4 px-12 rounded-xl hover:bg-indigo-50 transition-all shadow-lg text-lg">
      Start Free Trial
    </a>
  </div>
</section>
HTML;
    }
}
