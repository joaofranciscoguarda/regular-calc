<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    expression: string;
    result: string | null;
    error: string | null;
    isLoading: boolean;
}>();

const displayExpression = computed(() => props.expression || '0');
</script>

<template>
    <div
        class="rounded-xl border border-white/10 bg-black/60 px-5 py-4 shadow-inner shadow-black/40 backdrop-blur"
    >
        <p
            class="min-h-6 text-right font-accent text-lg break-all transition-all"
            :class="expression ? 'text-white/60' : 'text-white/20'"
        >
            {{ displayExpression }}
        </p>

        <div>
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
        </div>
    </div>
</template>
