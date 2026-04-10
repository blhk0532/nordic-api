<x-filament-panels::page>
    @php
        $formatDate = fn (?string $value): string => $value ? \Carbon\Carbon::parse($value)->format('M j, Y') : '';

        $period = __('Showing every available transaction.');

        if ($this->from && $this->to) {
            $period = __('Showing transactions from :from to :to.', [
                'from' => $formatDate($this->from),
                'to' => $formatDate($this->to),
            ]);
        } elseif ($this->from) {
            $period = __('Showing transactions from :from onward.', [
                'from' => $formatDate($this->from),
            ]);
        } elseif ($this->to) {
            $period = __('Showing transactions until :to.', [
                'to' => $formatDate($this->to),
            ]);
        }

        $tables = [
            [
                'title' => __('Income'),
                'description' => __('Payments that have been collected for services and products.'),
                'content' => $this->renderTable('income'),
            ],
            [
                'title' => __('Outcome'),
                'description' => __('Expenses that affect the bottom line.'),
                'content' => $this->renderTable('outcome'),
            ],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/60 px-6 py-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">
            {{ $period }}
        </div>

        <div class="grid gap-8 lg:grid-cols-2">
            @foreach ($tables as $table)
                <section class="space-y-4">
                    <header class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.4em] text-slate-500 dark:text-slate-400">
                            {{ $table['title'] }}
                        </p>

                        @if (filled($table['description']))
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                {{ $table['description'] }}
                            </p>
                        @endif
                    </header>

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm shadow-slate-900/5 dark:border-slate-800 dark:bg-slate-900/40">
                        {{ $table['content'] }}
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
