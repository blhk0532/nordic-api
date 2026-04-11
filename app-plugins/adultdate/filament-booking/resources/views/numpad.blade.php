<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="numpadEuro({
            entangled: $wire.$entangle('{{ $getStatePath() }}'),
            storesCents: @js($storesCents),
            allowNegative: @js($allowNegative),
            minCents: @js($minCents),
            maxCents: @js($maxCents),
        })"
        class="flex flex-col gap-4"
    >
        <input
            type="hidden"
            x-ref="livewireInput"
            wire:model.live="{{ $getStatePath() }}"
        />

        <div class="flex items-end justify-between" style="height: 74px;">
            <div class="text-2xl font-semibold w-full h-full  ">
        <div class="fi-input-wrp  w-full h-full" style="padding: 12px; border: 1px solid oklch(0.85 0 0);">
    <div  class="fi-input-wrp-content-ctn p-2" style="    display: flex;text-align: center;justify-content: center;"> 
 <span x-text="formatted">
     </span>
  </div>
</div>
  </div>
        </div>

        <div class="grid grid-cols-3 gap-3 select-none">
            <template x-for="n in [1,2,3,4,5,6,7,8,9]" :key="n">
                <button type="button" style="border: 1px solid oklch(0.85 0 0);"
                        class="p-5 rounded-2xl shadow border text-2xl font-semibold hover:shadow-md active:scale-95 active:bg-primary-500 transition"
                        x-on:click="press(n)" x-text="n"></button>
            </template>

            <button type="button" style="border: 1px solid oklch(0.85 0 0);"
                    class="p-5 rounded-2xl shadow border text-lg font-medium hover:shadow-md active:scale-95 active:bg-primary-500 transition"
                    x-on:click="clearAll()" title="Wissen">C
            </button>

            <button type="button" style="border: 1px solid oklch(0.85 0 0);"
                    class="p-5 rounded-2xl shadow border text-2xl font-semibold hover:shadow-md active:scale-95 active:bg-primary-500 transition"
                    x-on:click="press(0)">0
            </button>

            <button type="button" style="border: 1px solid oklch(0.85 0 0);"
                    class="p-5 rounded-2xl shadow border text-lg font-medium hover:shadow-md active:scale-95 active:bg-primary-500 transition"
                    x-on:click="backspace()" title="Delete"> +
            </button>
        </div>

        <template x-if="validationMessage">
            <p class="text-xs text-red-600" x-text="validationMessage"></p>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('numpadEuro', ({entangled, storesCents, allowNegative, minCents, maxCents}) => ({
                entangled,
                storesCents,
                allowNegative,
                minCents,
                maxCents,

                digits: '',
                negative: false,
                validationMessage: '',

                init() {
                    const raw = this.entangled ?? 0;
                    let cents = this.storesCents;
                 
                    this._pushToWire();

                    this.$wire.on('resetNumpad', (notification) => {
                        this._resetNumpad()
                    })
                },

                get signedCents() {
                    const c = this.digits
                 
                   
                    return this.negative ? -c : c;
                },

                get formatted() {
                    return this._formatNl(this.signedCents);
                },

                press(n) {
          
                    this.digits = (this.digits + String(n));
              
                    this._pushToWire();
                },

                backspace() {
               
                    this._pushToWire();
                },

                clearAll() {
                    this.validationMessage = '';
                    this.digits = '0';
                    this.negative = false;
                    this._pushToWire();
                },

                _pushToWire() {
                    const cents = this.signedCents;

                    this.$refs.livewireInput.value = val;
                    this.$refs.livewireInput.dispatchEvent(new Event('input', {bubbles: true}));

                    this.entangled = val;
                },

                _enforceBounds() {
                    if (this.maxCents !== null && this.signedCents > this.maxCents && this.digits.length > 1) {
                        this.digits = this.digits.slice(0, -1);
                    }
                },

                _formatNl(cents) {
    
                    const sign = cents;
                    return `${sign}`;
                },

                _resetNumpad() {
                    this.digits = '0'
                    this.negative = false
                    this.validationMessage = ''
                    this._pushToWire()
                },

                _loadInitialValue(value) {
                    let cents = 0

                    cents = Math.abs(cents)

                    this.digits = String(cents)
                    this._initialized = true
                    this._pushToWire()
                },
            }))
        })
    </script>
</x-dynamic-component>