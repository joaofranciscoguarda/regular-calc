<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import type { HistoryEntry, Paginated } from '@/types';

defineProps<{
    history: Paginated<HistoryEntry>;
}>();

const emit = defineEmits<{
    paste: [expression: string];
}>();

const clearing = ref(false);

function goToPage(url: string | null) {
    if (!url) return;
    router.visit(url, { preserveScroll: true });
}

function deleteOne(id: number) {
    router.delete(`/calculations/${id}`, { preserveScroll: true });
}

function clearAll() {
    clearing.value = true;
    router.delete('/calculations', {
        preserveScroll: true,
        onFinish: () => {
            clearing.value = false;
        },
    });
}
</script>

<template>
    <div class="flex h-full flex-col gap-3">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2
                class="font-display text-5xl tracking-wide text-white drop-shadow"
            >
                Ticker Tape
            </h2>
            <Button
                v-if="history.data.length > 0"
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

        <!-- Empty state -->
        <div
            v-if="history.data.length === 0"
            class="flex flex-1 flex-col items-center justify-center gap-2 py-10 text-white/40"
        >
            <span class="text-4xl">🧮</span>
            <p class="font-accent text-sm">No calculations yet</p>
        </div>

        <!-- List -->
        <ul
            v-else
            class="flex flex-col gap-2 overflow-y-auto pr-1 lg:max-h-120 lg:overflow-y-auto"
        >
            <li
                v-for="entry in history.data"
                :key="entry.id"
                class="group flex cursor-pointer items-center justify-between gap-3 rounded-lg bg-white/5 px-3 py-2 ring-1 ring-white/10 transition hover:bg-white/10"
                title="Click to paste into calculator"
                @click="emit('paste', entry.expression)"
            >
                <div class="min-w-0 flex-1">
                    <p class="truncate font-accent text-sm text-white/70">
                        {{ entry.expression }}
                    </p>
                    <p class="font-accent text-base font-semibold text-white">
                        = {{ entry.result }}
                    </p>
                </div>
                <button
                    class="shrink-0 rounded p-1 text-white/30 opacity-0 transition group-hover:opacity-100 hover:text-red-400"
                    title="Delete"
                    @click.stop="deleteOne(entry.id)"
                >
                    <X class="size-4" />
                </button>
            </li>
        </ul>

        <!-- Pagination -->
        <div
            v-if="history.meta.last_page > 1"
            class="mt-auto flex items-center justify-between border-t border-white/10 pt-3"
        >
            <button
                :disabled="!history.links.prev"
                class="flex items-center gap-1 rounded px-2 py-1 font-accent text-xs text-white/50 transition hover:text-white disabled:pointer-events-none disabled:opacity-25"
                @click="goToPage(history.links.prev)"
            >
                <ChevronLeft class="size-3" />
                Prev
            </button>

            <span class="font-accent text-xs text-white/40">
                {{ history.meta.current_page }} / {{ history.meta.last_page }}
            </span>

            <button
                :disabled="!history.links.next"
                class="flex items-center gap-1 rounded px-2 py-1 font-accent text-xs text-white/50 transition hover:text-white disabled:pointer-events-none disabled:opacity-25"
                @click="goToPage(history.links.next)"
            >
                Next
                <ChevronRight class="size-3" />
            </button>
        </div>
    </div>
</template>
