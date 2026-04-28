<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class FaqAccordionBlock extends Block
{
    public static function getId(): string
    {
        return 'faq-accordion';
    }

    public static function getName(): string
    {
        return 'FAQ Accordion';
    }

    public static function getCategory(): string
    {
        return 'Content';
    }

    public static function getOrder(): int
    {
        return 50;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-slate-50">
  <div class="container mx-auto px-4 max-w-3xl">
    <div class="text-center mb-16">
      <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Frequently Asked Questions</h2>
      <p class="text-lg text-slate-600">Everything you need to know about getting started with our platform.</p>
    </div>
    <div class="space-y-4">
      <details class="bg-white rounded-xl p-6 border border-slate-200 group" open>
        <summary class="font-semibold text-slate-900 cursor-pointer text-lg flex justify-between items-center">
          How do I get started with the page builder?
          <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <p class="mt-4 text-slate-600 leading-relaxed">Simply create an account, navigate to your dashboard, and click "Create New Page." You'll be able to drag and drop blocks onto your canvas, customize text and images, and publish with one click — no coding required.</p>
      </details>
      <details class="bg-white rounded-xl p-6 border border-slate-200 group">
        <summary class="font-semibold text-slate-900 cursor-pointer text-lg flex justify-between items-center">
          Can I use my own custom domain?
          <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <p class="mt-4 text-slate-600 leading-relaxed">Yes! Connect any domain you own through our DNS settings. We support automatic SSL certificates so your pages are always served over HTTPS.</p>
      </details>
      <details class="bg-white rounded-xl p-6 border border-slate-200 group">
        <summary class="font-semibold text-slate-900 cursor-pointer text-lg flex justify-between items-center">
          Is there a free plan available?
          <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <p class="mt-4 text-slate-600 leading-relaxed">We offer a generous free tier that lets you build and publish up to 5 pages per month. Upgrade to Pro for unlimited pages, custom domains, and advanced analytics.</p>
      </details>
      <details class="bg-white rounded-xl p-6 border border-slate-200 group">
        <summary class="font-semibold text-slate-900 cursor-pointer text-lg flex justify-between items-center">
          Do you offer customer support?
          <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <p class="mt-4 text-slate-600 leading-relaxed">Pro users get priority email support with a 24-hour response time. Free users have access to our comprehensive documentation and community forums.</p>
      </details>
    </div>
  </div>
</section>
HTML;
    }
}
