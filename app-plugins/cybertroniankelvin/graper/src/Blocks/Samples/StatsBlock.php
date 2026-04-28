<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class StatsBlock extends Block
{
    public static function getId(): string
    {
        return 'stats';
    }

    public static function getName(): string
    {
        return 'Statistics Banner';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 30;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-gradient-to-r from-indigo-600 to-purple-700 text-white">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
      <div>
        <p class="text-5xl lg:text-6xl font-bold mb-3">10k+</p>
        <p class="text-lg text-indigo-100">Active Users</p>
      </div>
      <div>
        <p class="text-5xl lg:text-6xl font-bold mb-3">50k+</p>
        <p class="text-lg text-indigo-100">Pages Published</p>
      </div>
      <div>
        <p class="text-5xl lg:text-6xl font-bold mb-3">99.9%</p>
        <p class="text-lg text-indigo-100">Uptime SLA</p>
      </div>
      <div>
        <p class="text-5xl lg:text-6xl font-bold mb-3">4.9/5</p>
        <p class="text-lg text-indigo-100">User Rating</p>
      </div>
    </div>
  </div>
</section>
HTML;
    }
}
