<script setup lang="ts">
import { Trash2, X } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';

interface Calculation {
    id: number;
    expression: string;
    result: string;
    created_at: string;
}

const calculations = ref<Calculation[]>([]);
const loading = ref(false);
const clearing = ref(false);

async function fetchCalculations() {
    loading.value = true;
    try {
        const res = await fetch('/api/calculations');
        if (!res.ok) throw new Error('Failed to fetch');
        calculations.value = await res.json();
    } catch (e) {
        console.error('Failed to load calculations', e);
    } finally {
        loading.value = false;
    }
}

async function deleteOne(id: number) {
    try {
        const res = await fetch(`/api/calculations/${id}`, {
            method: 'DELETE',
        });
        if (!res.ok) throw new Error('Failed to delete');
        calculations.value = calculations.value.filter((c) => c.id !== id);
    } catch (e) {
        console.error('Failed to delete calculation', e);
    }
}

async function clearAll() {
    clearing.value = true;
    try {
        const res = await fetch('/api/calculations', { method: 'DELETE' });
        if (!res.ok) throw new Error('Failed to clear');
        calculations.value = [];
    } catch (e) {
        console.error('Failed to clear calculations', e);
    } finally {
        clearing.value = false;
    }
}

const emit = defineEmits<{
    paste: [expression: string];
}>();

defineExpose({ fetchCalculations });

onMounted(fetchCalculations);
</script>

<template>
    <div class="flex h-full flex-col">
        <!-- Header -->
        <div class="mb-3 flex items-center justify-between">
            <h2
                class="font-display text-xl tracking-wide text-white drop-shadow"
            >
                Ticker Tape
            </h2>
            <Button
                v-if="calculations.length > 0"
                variant="destructive"
                size="sm"
                :disabled="clearing"
                class="gap-1 text-xs"
                @click="clearAll"
            >
                <Trash2 class="size-3" />
                Clear All
            </Button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="py-6 text-center text-white/50">
            Loading…
        </div>

        <!-- Empty state -->
        <div
            v-else-if="calculations.length === 0"
            class="flex flex-1 flex-col items-center justify-center gap-2 py-10 text-white/40"
        >
            <span class="text-4xl">🧮</span>
            <p class="font-accent text-sm">No calculations yet</p>
        </div>

        <!-- List -->
        <ul v-else class="flex flex-col gap-2 overflow-y-auto pr-1">
            <li
                v-for="calc in calculations"
                :key="calc.id"
                class="group flex cursor-pointer items-center justify-between gap-3 rounded-lg bg-white/5 px-3 py-2 ring-1 ring-white/10 transition hover:bg-white/10"
                title="Click to paste into calculator"
                @click="emit('paste', calc.expression)"
            >
                <div class="min-w-0 flex-1">
                    <p class="truncate font-accent text-sm text-white/70">
                        {{ calc.expression }}
                    </p>
                    <p class="font-accent text-base font-semibold text-white">
                        = {{ calc.result }}
                    </p>
                </div>
                <button
                    class="shrink-0 rounded p-1 text-white/30 opacity-0 transition group-hover:opacity-100 hover:text-red-400"
                    title="Delete"
                    @click.stop="deleteOne(calc.id)"
                >
                    <X class="size-4" />
                </button>
            </li>
        </ul>
    </div>
</template>
