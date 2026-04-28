<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class Cta50_50Block extends Block
{
    public static function getId(): string
    {
        return 'cta-50-50';
    }

    public static function getName(): string
    {
        return 'Split CTA Section';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 25;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-slate-50">
  <div class="container mx-auto px-4">
    <div class="grid lg:grid-cols-2 gap-12 items-center max-w-6xl mx-auto">
      <div>
        <span class="text-indigo-600 font-semibold text-sm uppercase tracking-wider">Get Started Today</span>
        <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mt-3 mb-6">Everything you need to launch faster</h2>
        <p class="text-lg text-slate-600 mb-8">Our platform gives you the tools, templates, and team collaboration features to go from idea to published page in under an hour.</p>
        <ul class="space-y-4">
          <li class="flex items-center gap-3 text-slate-700"><svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Pre-built component library</li>
          <li class="flex items-center gap-3 text-slate-700"><svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Real-time collaboration</li>
          <li class="flex items-center gap-3 text-slate-700"><svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> One-click publishing</li>
        </ul>
      </div>
      <div class="text-center lg:text-right">
        <div class="bg-white rounded-2xl shadow-xl p-8 inline-block">
          <h3 class="text-2xl font-bold text-slate-900 mb-2">Start Building Now</h3>
          <p class="text-slate-500 mb-6">No commitment. Cancel anytime.</p>
          <a href="/get-started" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl transition-all text-lg">
            Get Started Free
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
HTML;
    }
}
