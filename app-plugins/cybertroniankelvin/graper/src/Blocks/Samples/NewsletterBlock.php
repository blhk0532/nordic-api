<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class NewsletterBlock extends Block
{
    public static function getId(): string
    {
        return 'newsletter';
    }

    public static function getName(): string
    {
        return 'Newsletter Signup';
    }

    public static function getCategory(): string
    {
        return 'Marketing';
    }

    public static function getOrder(): int
    {
        return 65;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-16 bg-indigo-600">
  <div class="container mx-auto px-4 max-w-2xl text-center">
    <h2 class="text-3xl font-bold text-white mb-3">Stay in the loop</h2>
    <p class="text-indigo-200 mb-8">Get the latest updates, articles, and resources delivered straight to your inbox.</p>
    <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
      <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-3 rounded-lg text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-white">
      <button type="submit" class="px-6 py-3 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition-colors text-sm">Subscribe</button>
    </form>
    <p class="text-indigo-300 text-xs mt-4">No spam, unsubscribe at any time.</p>
  </div>
</section>
HTML;
    }

    public function getFlowbiteTemplate(): string
    {
        return <<<'HTML'
<section class="bg-blue-700 dark:bg-blue-800 py-16">
  <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
    <div class="mx-auto max-w-screen-md sm:text-center">
      <h2 class="mb-4 text-3xl tracking-tight font-extrabold text-white sm:text-4xl">Stay in the loop</h2>
      <p class="mx-auto mb-8 max-w-2xl text-blue-200 md:mb-12 sm:text-xl">Get the latest updates, articles, and resources delivered straight to your inbox.</p>
      <form action="#">
        <div class="items-center mx-auto mb-3 space-y-4 max-w-screen-sm sm:flex sm:space-y-0">
          <div class="relative w-full">
            <input class="block p-3 w-full text-sm text-gray-900 bg-white rounded-lg border border-gray-300 sm:rounded-none sm:rounded-l-lg focus:ring-primary-500 focus:border-primary-500" placeholder="Enter your email" type="email" required>
          </div>
          <div>
            <button type="submit" class="py-3 px-5 w-full text-sm font-medium text-center text-white rounded-lg border cursor-pointer bg-blue-900 border-blue-900 sm:rounded-none sm:rounded-r-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300">Subscribe</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
HTML;
    }

    public function getShadcnTemplate(): string
    {
        return <<<'HTML'
<section class="py-16 bg-primary">
  <div class="container mx-auto px-4 max-w-2xl text-center">
    <h2 class="text-3xl font-bold tracking-tight text-primary-foreground mb-3">Stay in the loop</h2>
    <p class="text-primary-foreground/70 mb-8">Get the latest updates, articles, and resources delivered straight to your inbox.</p>
    <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
      <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-2.5 rounded-md bg-background text-foreground text-sm border border-input focus:outline-none focus:ring-2 focus:ring-ring">
      <button type="submit" class="px-6 py-2.5 bg-background text-foreground font-medium rounded-md hover:bg-accent transition-colors text-sm">Subscribe</button>
    </form>
    <p class="text-primary-foreground/50 text-xs mt-4">No spam, unsubscribe at any time.</p>
  </div>
</section>
HTML;
    }
}
