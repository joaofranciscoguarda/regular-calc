<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import type { HTMLAttributes } from 'vue';
import type { ButtonVariants } from '@/components/ui/button';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';

interface Props {
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    class?: HTMLAttributes['class'];
    keyShortcut?: string;
    disabled?: boolean;
    tooltip?: string;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'lg',
});

const emit = defineEmits<{
    click: [];
}>();

function handleKeydown(event: KeyboardEvent) {
    if (!props.keyShortcut || props.disabled) return;

    const target = event.target as HTMLElement;
    if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') return;

    const key = event.key;

    if (key === props.keyShortcut) {
        event.preventDefault();
        emit('click');
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <TooltipProvider v-if="tooltip" :delay-duration="300">
        <Tooltip>
            <TooltipTrigger as-child>
                <Button
                    :variant="variant"
                    :size="size"
                    :disabled="disabled"
                    :class="
                        cn('font-accent text-white select-none', props.class)
                    "
                    @click="emit('click')"
                >
                    <slot />
                </Button>
            </TooltipTrigger>
            <TooltipContent>
                <p>{{ tooltip }}</p>
            </TooltipContent>
        </Tooltip>
    </TooltipProvider>
    <Button
        v-else
        :variant="variant"
        :size="size"
        :disabled="disabled"
        :class="cn('font-accent text-white select-none', props.class)"
        @click="emit('click')"
    >
        <slot />
    </Button>
</template>
