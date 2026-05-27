<script setup lang="ts">
import type { Column } from '@tanstack/vue-table';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = defineProps<{
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    column: Column<any, any>;
    title: string;
    class?: string;
}>();
</script>

<template>
    <div :class="cn('flex items-center gap-1', props.class)">
        <Button
            v-if="column.getCanSort()"
            variant="ghost"
            size="sm"
            class="-ml-3 h-8 data-[state=open]:bg-accent"
            @click="column.toggleSorting()"
        >
            {{ title }}
            <ArrowUp v-if="column.getIsSorted() === 'asc'" class="ml-1 size-4" />
            <ArrowDown v-else-if="column.getIsSorted() === 'desc'" class="ml-1 size-4" />
            <ArrowUpDown v-else class="ml-1 size-4 opacity-40" />
        </Button>
        <span v-else class="font-medium">{{ title }}</span>
    </div>
</template>
