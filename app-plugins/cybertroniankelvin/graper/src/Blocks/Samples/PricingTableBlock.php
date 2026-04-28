<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class PricingTableBlock extends Block
{
    public static function getId(): string
    {
        return 'pricing-table';
    }

    public static function getName(): string
    {
        return 'Pricing Table';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 35;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-slate-50">
  <div class="container mx-auto px-4">
    <div class="text-center mb-16">
      <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Simple, transparent pricing</h2>
      <p class="text-lg text-slate-600">Choose the plan that fits your needs. No hidden fees.</p>
    </div>
    <div class="grid lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
      <div class="bg-white rounded-2xl p-8 border border-slate-200">
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Starter</h3>
        <div class="mb-6">
          <span class="text-5xl font-bold text-slate-900">$0</span>
          <span class="text-slate-500">/month</span>
        </div>
        <p class="text-slate-600 mb-6">Perfect for trying things out.</p>
        <ul class="space-y-3 mb-8">
          <li class="flex items-center gap-2 text-slate-700"><svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 5 pages per month</li>
          <li class="flex items-center gap-2 text-slate-700"><svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Basic block library</li>
          <li class="flex items-center gap-2 text-slate-700"><svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Community support</li>
        </ul>
        <a href="/signup" class="block text-center w-full py-3 rounded-xl border-2 border-slate-300 text-slate-700 font-semibold hover:border-slate-400 transition-colors">Get Started</a>
      </div>
      <div class="bg-indigo-600 rounded-2xl p-8 text-white relative">
        <span class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-indigo-400 text-white text-sm font-bold px-4 py-1 rounded-full">Popular</span>
        <h3 class="text-xl font-semibold mb-2">Pro</h3>
        <div class="mb-6">
          <span class="text-5xl font-bold">$29</span>
          <span class="text-indigo-200">/month</span>
        </div>
        <p class="text-indigo-100 mb-6">For growing teams and businesses.</p>
        <ul class="space-y-3 mb-8">
          <li class="flex items-center gap-2"><svg class="w-5 h-5 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Unlimited pages</li>
          <li class="flex items-center gap-2"><svg class="w-5 h-5 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Custom domains</li>
          <li class="flex items-center gap-2"><svg class="w-5 h-5 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Priority support</li>
          <li class="flex items-center gap-2"><svg class="w-5 h-5 text-indigo-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Advanced analytics</li>
        </ul>
        <a href="/signup" class="block text-center w-full py-3 rounded-xl bg-white text-indigo-600 font-bold hover:bg-indigo-50 transition-colors">Get Started</a>
      </div>
      <div class="bg-white rounded-2xl p-8 border border-slate-200">
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Enterprise</h3>
        <div class="mb-6">
          <span class="text-5xl font-bold text-slate-900">Custom</span>
        </div>
        <p class="text-slate-600 mb-6">For large organizations with custom needs.</p>
        <ul class="space-y-3 mb-8">
          <li class="flex items-center gap-2 text-slate-700"><svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Everything in Pro</li>
          <li class="flex items-center gap-2 text-slate-700"><svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Dedicated account manager</li>
          <li class="flex items-center gap-2 text-slate-700"><svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> SSO & advanced security</li>
          <li class="flex items-center gap-2 text-slate-700"><svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> SLA guarantee</li>
        </ul>
        <a href="/contact" class="block text-center w-full py-3 rounded-xl border-2 border-indigo-600 text-indigo-600 font-semibold hover:bg-indigo-50 transition-colors">Contact Sales</a>
      </div>
    </div>
  </div>
</section>
HTML;
    }
}
