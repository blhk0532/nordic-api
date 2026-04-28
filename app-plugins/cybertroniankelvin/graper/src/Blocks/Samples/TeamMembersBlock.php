<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class TeamMembersBlock extends Block
{
    public static function getId(): string
    {
        return 'team-members';
    }

    public static function getName(): string
    {
        return 'Team Members Grid';
    }

    public static function getCategory(): string
    {
        return 'Social Proof';
    }

    public static function getOrder(): int
    {
        return 45;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-white">
  <div class="container mx-auto px-4 max-w-6xl">
    <div class="text-center mb-16">
      <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Meet our team</h2>
      <p class="text-lg text-slate-600">The people behind the product you love.</p>
    </div>
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
      <div class="text-center">
        <div class="w-40 h-40 mx-auto mb-5 rounded-full bg-gradient-to-br from-indigo-200 to-purple-200 flex items-center justify-center text-2xl font-bold text-indigo-700">AJ</div>
        <h3 class="text-xl font-semibold text-slate-900">Alexandra Johnson</h3>
        <p class="text-indigo-600 font-medium">CEO & Co-founder</p>
        <p class="text-slate-500 text-sm mt-2">Former PM at Stripe. 10+ years in product.</p>
      </div>
      <div class="text-center">
        <div class="w-40 h-40 mx-auto mb-5 rounded-full bg-gradient-to-br from-emerald-200 to-teal-200 flex items-center justify-center text-2xl font-bold text-emerald-700">MK</div>
        <h3 class="text-xl font-semibold text-slate-900">Marcus Kim</h3>
        <p class="text-indigo-600 font-medium">CTO & Co-founder</p>
        <p class="text-slate-500 text-sm mt-2">Built infrastructure at Shopify. MIT grad.</p>
      </div>
      <div class="text-center">
        <div class="w-40 h-40 mx-auto mb-5 rounded-full bg-gradient-to-br from-amber-200 to-orange-200 flex items-center justify-center text-2xl font-bold text-amber-700">SR</div>
        <h3 class="text-xl font-semibold text-slate-900">Sofia Rodriguez</h3>
        <p class="text-indigo-600 font-medium">Head of Design</p>
        <p class="text-slate-500 text-sm mt-2">Award-winning UX designer. Ex-Figma.</p>
      </div>
      <div class="text-center">
        <div class="w-40 h-40 mx-auto mb-5 rounded-full bg-gradient-to-br from-sky-200 to-blue-200 flex items-center justify-center text-2xl font-bold text-sky-700">JT</div>
        <h3 class="text-xl font-semibold text-slate-900">James Thompson</h3>
        <p class="text-indigo-600 font-medium">VP of Engineering</p>
        <p class="text-slate-500 text-sm mt-2">Full-stack architect. Open source maintainer.</p>
      </div>
    </div>
  </div>
</section>
HTML;
    }
}
