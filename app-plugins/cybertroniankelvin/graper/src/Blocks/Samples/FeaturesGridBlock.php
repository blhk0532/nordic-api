<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class FeaturesGridBlock extends Block
{
    public static function getId(): string
    {
        return 'features-grid';
    }

    public static function getName(): string
    {
        return 'Features Grid';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 28;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-white">
  <div class="container mx-auto px-4 max-w-6xl">
    <div class="text-center mb-16">
      <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">Everything You Need</h2>
      <p class="text-lg text-slate-600 max-w-2xl mx-auto">Powerful features designed to help you build better, faster, and smarter.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-8">
      <div class="p-8 rounded-2xl bg-slate-50 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Lightning Fast</h3>
        <p class="text-slate-600">Optimised for speed at every layer so your pages load instantly.</p>
      </div>
      <div class="p-8 rounded-2xl bg-slate-50 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Secure by Default</h3>
        <p class="text-slate-600">Enterprise-grade security built in, not bolted on.</p>
      </div>
      <div class="p-8 rounded-2xl bg-slate-50 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Drag & Drop</h3>
        <p class="text-slate-600">Build pages visually without touching a line of code.</p>
      </div>
      <div class="p-8 rounded-2xl bg-slate-50 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Fully Responsive</h3>
        <p class="text-slate-600">Preview and perfect your design on every screen size.</p>
      </div>
      <div class="p-8 rounded-2xl bg-slate-50 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Extensible</h3>
        <p class="text-slate-600">Add custom blocks and integrations via clean PHP APIs.</p>
      </div>
      <div class="p-8 rounded-2xl bg-slate-50 hover:shadow-md transition-shadow">
        <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mb-4">
          <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Cloud Storage</h3>
        <p class="text-slate-600">Pages saved to your database — never lose a design again.</p>
      </div>
    </div>
  </div>
</section>
HTML;
    }

    public function getFlowbiteTemplate(): string
    {
        return <<<'HTML'
<section class="bg-white dark:bg-gray-900 py-20">
  <div class="py-8 px-4 mx-auto max-w-screen-xl sm:py-16 lg:px-6">
    <div class="max-w-screen-md mb-8 lg:mb-16 text-center mx-auto">
      <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">Everything You Need</h2>
      <p class="text-gray-500 sm:text-xl dark:text-gray-400">Powerful features designed to help you build better, faster, and smarter.</p>
    </div>
    <div class="space-y-8 md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-12 md:space-y-0">
      <div>
        <div class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-blue-100 lg:h-12 lg:w-12 dark:bg-blue-900">
          <svg class="w-5 h-5 text-blue-600 lg:w-6 lg:h-6 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>
        </div>
        <h3 class="mb-2 text-xl font-bold dark:text-white">Lightning Fast</h3>
        <p class="text-gray-500 dark:text-gray-400">Optimised for speed so your pages load instantly.</p>
      </div>
      <div>
        <div class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-blue-100 lg:h-12 lg:w-12 dark:bg-blue-900">
          <svg class="w-5 h-5 text-blue-600 lg:w-6 lg:h-6 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
        </div>
        <h3 class="mb-2 text-xl font-bold dark:text-white">Secure by Default</h3>
        <p class="text-gray-500 dark:text-gray-400">Enterprise-grade security built in, not bolted on.</p>
      </div>
      <div>
        <div class="flex justify-center items-center mb-4 w-10 h-10 rounded-full bg-blue-100 lg:h-12 lg:w-12 dark:bg-blue-900">
          <svg class="w-5 h-5 text-blue-600 lg:w-6 lg:h-6 dark:text-blue-300" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 000 2h11.586l-2.293 2.293a1 1 0 101.414 1.414l4-4a1 1 0 000-1.414l-4-4a1 1 0 10-1.414 1.414L14.586 4H3z"></path><path d="M17 16a1 1 0 000-2H5.414l2.293-2.293a1 1 0 10-1.414-1.414l-4 4a1 1 0 000 1.414l4 4a1 1 0 101.414-1.414L5.414 16H17z"></path></svg>
        </div>
        <h3 class="mb-2 text-xl font-bold dark:text-white">Extensible</h3>
        <p class="text-gray-500 dark:text-gray-400">Add custom blocks via clean PHP APIs.</p>
      </div>
    </div>
  </div>
</section>
HTML;
    }

    public function getShadcnTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-background">
  <div class="container mx-auto px-4 max-w-6xl">
    <div class="text-center mb-16">
      <h2 class="text-3xl lg:text-4xl font-bold tracking-tight text-foreground mb-4">Everything You Need</h2>
      <p class="text-lg text-muted-foreground max-w-2xl mx-auto">Powerful features designed to help you build better, faster, and smarter.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="rounded-lg border border-border bg-card p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-md bg-primary/10 flex items-center justify-center mb-4">
          <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h3 class="font-semibold text-card-foreground mb-2">Lightning Fast</h3>
        <p class="text-sm text-muted-foreground">Optimised for speed at every layer.</p>
      </div>
      <div class="rounded-lg border border-border bg-card p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-md bg-primary/10 flex items-center justify-center mb-4">
          <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h3 class="font-semibold text-card-foreground mb-2">Secure by Default</h3>
        <p class="text-sm text-muted-foreground">Enterprise-grade security built in.</p>
      </div>
      <div class="rounded-lg border border-border bg-card p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-10 h-10 rounded-md bg-primary/10 flex items-center justify-center mb-4">
          <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
        </div>
        <h3 class="font-semibold text-card-foreground mb-2">Drag & Drop</h3>
        <p class="text-sm text-muted-foreground">Build pages visually without code.</p>
      </div>
    </div>
  </div>
</section>
HTML;
    }
}
