<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Blocks\Samples;

use CybertronianKelvin\Graper\Blocks\Block;

class ContactSectionBlock extends Block
{
    public static function getId(): string
    {
        return 'contact-section';
    }

    public static function getName(): string
    {
        return 'Contact Section';
    }

    public static function getCategory(): string
    {
        return 'Content';
    }

    public static function getOrder(): int
    {
        return 70;
    }

    public function getTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-slate-50">
  <div class="container mx-auto px-4 max-w-5xl">
    <div class="grid md:grid-cols-2 gap-16 items-start">
      <div>
        <h2 class="text-3xl font-bold text-slate-900 mb-4">Get in touch</h2>
        <p class="text-slate-600 mb-8">Have a question or want to work together? We'd love to hear from you. Fill out the form and we'll get back to you as soon as possible.</p>
        <div class="space-y-4">
          <div class="flex items-center gap-3 text-slate-700">
            <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>hello@example.com</span>
          </div>
          <div class="flex items-center gap-3 text-slate-700">
            <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>+1 (555) 000-0000</span>
          </div>
          <div class="flex items-center gap-3 text-slate-700">
            <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span>123 Main Street, City, State 00000</span>
          </div>
        </div>
      </div>
      <form class="bg-white rounded-2xl p-8 shadow-sm border border-slate-200 space-y-5">
        <div class="grid sm:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">First name</label>
            <input type="text" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="John">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Last name</label>
            <input type="text" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Doe">
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
          <input type="email" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="john@example.com">
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
          <textarea rows="4" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none" placeholder="Tell us how we can help..."></textarea>
        </div>
        <button type="submit" class="w-full py-3 px-6 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors text-sm">Send message</button>
      </form>
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
    <div class="grid grid-cols-1 gap-10 md:grid-cols-2">
      <div>
        <h2 class="mb-4 text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white">Get in touch</h2>
        <p class="mb-8 font-light text-gray-500 dark:text-gray-400 sm:text-xl">Have a question or want to work together? We'd love to hear from you.</p>
        <ul class="mb-6 md:mb-0 space-y-4">
          <li class="flex space-x-2">
            <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-blue-700 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span class="text-gray-500 dark:text-gray-400">hello@example.com</span>
          </li>
          <li class="flex space-x-2">
            <svg class="flex-shrink-0 w-5 h-5 mt-0.5 text-blue-700 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span class="text-gray-500 dark:text-gray-400">+1 (555) 000-0000</span>
          </li>
        </ul>
      </div>
      <form action="#" class="space-y-8">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">First name</label>
            <input type="text" class="block p-3 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="John" required>
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Last name</label>
            <input type="text" class="block p-3 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Doe" required>
          </div>
        </div>
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">Email</label>
          <input type="email" class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="name@example.com" required>
        </div>
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">Your message</label>
          <textarea rows="6" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg shadow-sm border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Leave a comment..."></textarea>
        </div>
        <button type="submit" class="py-3 px-5 text-sm font-medium text-center text-white rounded-lg bg-blue-700 sm:w-fit hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700">Send message</button>
      </form>
    </div>
  </div>
</section>
HTML;
    }

    public function getShadcnTemplate(): string
    {
        return <<<'HTML'
<section class="py-20 bg-muted/30">
  <div class="container mx-auto px-4 max-w-5xl">
    <div class="grid md:grid-cols-2 gap-16 items-start">
      <div>
        <h2 class="text-3xl font-bold tracking-tight text-foreground mb-4">Get in touch</h2>
        <p class="text-muted-foreground mb-8">Have a question or want to work together? We'd love to hear from you. Fill out the form and we'll get back to you shortly.</p>
        <div class="space-y-4">
          <div class="flex items-center gap-3 text-foreground">
            <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span class="text-sm">hello@example.com</span>
          </div>
          <div class="flex items-center gap-3 text-foreground">
            <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span class="text-sm">+1 (555) 000-0000</span>
          </div>
        </div>
      </div>
      <form class="rounded-lg border border-border bg-card p-8 shadow-sm space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-sm font-medium text-foreground">First name</label>
            <input type="text" class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="John">
          </div>
          <div class="space-y-1.5">
            <label class="text-sm font-medium text-foreground">Last name</label>
            <input type="text" class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="Doe">
          </div>
        </div>
        <div class="space-y-1.5">
          <label class="text-sm font-medium text-foreground">Email</label>
          <input type="email" class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring" placeholder="john@example.com">
        </div>
        <div class="space-y-1.5">
          <label class="text-sm font-medium text-foreground">Message</label>
          <textarea rows="4" class="w-full px-3 py-2 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring resize-none" placeholder="Tell us how we can help..."></textarea>
        </div>
        <button type="submit" class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 shadow transition-colors">Send message</button>
      </form>
    </div>
  </div>
</section>
HTML;
    }
}
