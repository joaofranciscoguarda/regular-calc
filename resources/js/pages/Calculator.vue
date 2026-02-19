<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CalcButton from '@/components/CalcButton.vue';
import TickerTape from '@/components/TickerTape.vue';

// ─── Helpers ──────────────────────────────────────────────────────────────────
function balanceParens(expr: string): string {
    let depth = 0;
    for (const ch of expr) {
        if (ch === '(') depth++;
        else if (ch === ')') depth--;
    }
    return depth > 0 ? expr + ')'.repeat(depth) : expr;
}

// ─── State ────────────────────────────────────────────────────────────────────
const expression = ref('');
const result = ref<string | null>(null);
const error = ref<string | null>(null);
const isLoading = ref(false);
const tickerTape = ref<InstanceType<typeof TickerTape> | null>(null);

// ─── Computed ─────────────────────────────────────────────────────────────────
const displayExpression = computed(() => expression.value || '0');

// ─── Input helpers ────────────────────────────────────────────────────────────
function append(value: string) {
    // If we just got a result and the user presses a digit, start fresh
    if (result.value !== null && /\d/.test(value)) {
        expression.value = value;
        result.value = null;
        error.value = null;
        return;
    }
    // If we just got a result and the user presses an operator, continue from result
    if (result.value !== null && /[+\-*/^]/.test(value)) {
        expression.value = result.value + value;
        result.value = null;
        error.value = null;
        return;
    }
    result.value = null;
    error.value = null;
    expression.value += value;
}

function backspace() {
    result.value = null;
    error.value = null;
    expression.value = expression.value.slice(0, -1);
}

function clear() {
    expression.value = '';
    result.value = null;
    error.value = null;
}

// ─── Evaluate ─────────────────────────────────────────────────────────────────
async function evaluate() {
    const raw = expression.value.trim();
    if (!raw) return;

    // Auto-close any unclosed parentheses so e.g. "sqrt(4" becomes "sqrt(4)"
    const expr = balanceParens(raw);
    expression.value = expr;

    isLoading.value = true;
    error.value = null;
    result.value = null;

    try {
        const res = await fetch('/api/calculations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ expression: expr }),
        });

        const data = await res.json();

        if (!res.ok) {
            error.value = data.error ?? 'Unknown error';
        } else {
            result.value = data.result;
            // Refresh ticker tape
            tickerTape.value?.fetchCalculations();
        }
    } catch {
        error.value = 'Network error — could not reach the server.';
    } finally {
        isLoading.value = false;
    }
}

// ─── Ticker tape paste ────────────────────────────────────────────────────────
function pasteFromTicker(expr: string) {
    const last = expression.value.slice(-1);
    // If the expression already ends with a digit, "." or ")", we need a "+"
    // separator so we don't mash two numbers together. For operators or open
    // parens (or an empty expression) we just append directly.
    if (expression.value !== '' && /[\d.)]/.test(last)) {
        expression.value += '+' + expr;
    } else {
        expression.value += expr;
    }
    result.value = null;
    error.value = null;
}

// ─── Button definitions ───────────────────────────────────────────────────────
type CalcBtn = {
    label: string;
    action: () => void;
    keyShortcut?: string;
    variant?:
        | 'default'
        | 'destructive'
        | 'outline'
        | 'secondary'
        | 'ghost'
        | 'link';
    class?: string;
    wide?: boolean;
};

const buttons: CalcBtn[][] = [
    // Row 1
    [
        {
            label: 'C',
            action: clear,
            keyShortcut: 'Escape',
            variant: 'destructive',
            class: 'text-lg',
        },
        {
            label: '⌫',
            action: backspace,
            keyShortcut: 'Backspace',
            variant: 'outline',
            class: 'text-lg',
        },
        {
            label: '(',
            action: () => append('('),
            keyShortcut: '(',
            variant: 'secondary',
        },
        {
            label: ')',
            action: () => append(')'),
            keyShortcut: ')',
            variant: 'secondary',
        },
        { label: '√', action: () => append('sqrt('), variant: 'secondary' },
    ],
    // Row 2
    [
        { label: '7', action: () => append('7'), keyShortcut: '7' },
        { label: '8', action: () => append('8'), keyShortcut: '8' },
        { label: '9', action: () => append('9'), keyShortcut: '9' },
        {
            label: '÷',
            action: () => append('/'),
            keyShortcut: '/',
            variant: 'secondary',
        },
        {
            label: '^',
            action: () => append('^'),
            keyShortcut: '^',
            variant: 'secondary',
        },
    ],
    // Row 3
    [
        { label: '4', action: () => append('4'), keyShortcut: '4' },
        { label: '5', action: () => append('5'), keyShortcut: '5' },
        { label: '6', action: () => append('6'), keyShortcut: '6' },
        {
            label: '×',
            action: () => append('*'),
            keyShortcut: '*',
            variant: 'secondary',
        },
        {
            label: '%',
            action: () => append('%'),
            keyShortcut: '%',
            variant: 'secondary',
        },
    ],
    // Row 4
    [
        { label: '1', action: () => append('1'), keyShortcut: '1' },
        { label: '2', action: () => append('2'), keyShortcut: '2' },
        { label: '3', action: () => append('3'), keyShortcut: '3' },
        {
            label: '−',
            action: () => append('-'),
            keyShortcut: '-',
            variant: 'secondary',
        },
        {
            label: '=',
            action: evaluate,
            keyShortcut: 'Enter',
            variant: 'default',
            class: 'row-span-2 bg-violet-600 hover:bg-violet-500 text-xl',
        },
    ],
    // Row 5
    [
        {
            label: '0',
            action: () => append('0'),
            keyShortcut: '0',
            class: 'col-span-2',
        },
        {
            label: '.',
            action: () => append('.'),
            keyShortcut: '.',
            variant: 'secondary',
        },
        {
            label: '+',
            action: () => append('+'),
            keyShortcut: '+',
            variant: 'secondary',
        },
        // = is already in row 4 spanning
    ],
];
</script>

<template>
    <Head title="Regular Calc" />

    <!-- Full-page background -->
    <div
        class="relative flex min-h-screen w-full items-center justify-center overflow-hidden p-4"
        style="
            background-image: url('/images/regular_calc_bg.png');
            background-size: cover;
            background-position: center;
        "
    >
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" />

        <!-- Content -->
        <div
            class="relative z-10 flex w-full max-w-4xl flex-col gap-6 lg:flex-row lg:items-start"
        >
            <!-- ── Calculator Panel ──────────────────────────────────────────── -->
            <div class="flex flex-col gap-4 lg:w-[360px]">
                <!-- Title -->
                <h1
                    class="text-center font-display text-4xl tracking-widest text-white drop-shadow-[0_2px_12px_rgba(168,85,247,0.8)]"
                >
                    Regular Calc
                </h1>

                <!-- Display -->
                <div
                    class="relative rounded-xl border border-white/10 bg-black/60 px-5 py-4 shadow-inner shadow-black/40 backdrop-blur"
                >
                    <!-- Expression -->
                    <p
                        class="min-h-[1.5rem] text-right font-accent text-lg break-all text-white/60 transition-all"
                        :class="{ 'text-white/20': !expression }"
                    >
                        {{ displayExpression }}
                    </p>

                    <!-- Result / Error -->
                    <transition name="slide-up" mode="out-in">
                        <p
                            v-if="result !== null"
                            :key="'result'"
                            class="mt-1 text-right font-accent text-4xl font-bold text-white drop-shadow"
                        >
                            = {{ result }}
                        </p>
                        <p
                            v-else-if="error"
                            :key="'error'"
                            class="mt-1 text-right font-accent text-sm text-red-400"
                        >
                            {{ error }}
                        </p>
                        <p
                            v-else-if="isLoading"
                            :key="'loading'"
                            class="mt-1 animate-pulse text-right font-accent text-3xl text-white/40"
                        >
                            …
                        </p>
                    </transition>
                </div>

                <!-- Button grid -->
                <!-- We use a CSS grid with 5 columns. The = button spans 2 rows via a wrapper trick -->
                <div class="grid grid-cols-5 gap-2">
                    <!-- Row 1 -->
                    <template v-for="(row, rowIdx) in buttons" :key="rowIdx">
                        <template
                            v-for="(btn, btnIdx) in row"
                            :key="`${rowIdx}-${btnIdx}`"
                        >
                            <CalcButton
                                :variant="btn.variant ?? 'outline'"
                                :key-shortcut="btn.keyShortcut"
                                :class="[
                                    btn.class,
                                    btn.wide ? 'col-span-2' : '',
                                    'h-14 rounded-xl border border-white/10 bg-black/50 text-lg text-white backdrop-blur transition hover:bg-white/10 active:scale-95',
                                    btn.variant === 'destructive'
                                        ? 'border-red-500/30 bg-red-900/40 text-red-300 hover:bg-red-800/60'
                                        : '',
                                    btn.variant === 'secondary'
                                        ? 'border-violet-400/20 bg-violet-900/30 text-violet-300 hover:bg-violet-800/50'
                                        : '',
                                    btn.label === '='
                                        ? 'row-span-2 h-[7.5rem] border-violet-400/40 bg-violet-700/80 text-white hover:bg-violet-600/90'
                                        : '',
                                ]"
                                :disabled="isLoading"
                                size="lg"
                                @click="btn.action"
                            >
                                {{ btn.label }}
                            </CalcButton>
                        </template>
                    </template>
                </div>

                <!-- Hint -->
                <p class="text-center font-accent text-xs text-white/30">
                    Keyboard shortcuts enabled · Try sqrt( or ^
                </p>
            </div>

            <!-- ── Ticker Tape Panel ──────────────────────────────────────────── -->
            <div
                class="flex-1 rounded-xl border border-white/10 bg-black/50 p-4 shadow-xl backdrop-blur lg:max-h-[600px] lg:overflow-y-auto"
            >
                <TickerTape ref="tickerTape" @paste="pasteFromTicker" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(6px);
}
</style>
