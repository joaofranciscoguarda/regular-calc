<script setup lang="ts">
import CalcButton from '@/components/CalcButton.vue';
import { type CalcBtn } from '@/types';

defineProps<{
    buttons: CalcBtn[][];
    isLoading: boolean;
}>();
</script>

<template>
    <div class="grid grid-cols-5 gap-2">
        <template v-for="(row, rowIdx) in buttons" :key="rowIdx">
            <template v-for="(btn, btnIdx) in row" :key="`${rowIdx}-${btnIdx}`">
                <CalcButton
                    :variant="btn.variant ?? 'outline'"
                    :key-shortcut="btn.keyShortcut"
                    :tooltip="btn.tooltip"
                    :disabled="isLoading"
                    :class="[
                        'h-14 rounded-xl border border-white/10 bg-black/50 text-lg text-white backdrop-blur transition hover:bg-white/10 active:scale-95',
                        btn.variant === 'destructive' &&
                            'border-red-500/30 bg-red-900/40 text-red-300 hover:bg-red-800/60',
                        btn.variant === 'secondary' &&
                            'border-violet-400/20 bg-violet-900/30 text-violet-300 hover:bg-violet-800/50',
                        btn.label === '=' &&
                            'border-violet-400/40 bg-violet-700/80 text-white hover:bg-violet-600/90',
                        btn.class,
                    ]"
                    size="lg"
                    @click="btn.action"
                >
                    {{ btn.label }}
                </CalcButton>
            </template>
        </template>
    </div>
</template>
