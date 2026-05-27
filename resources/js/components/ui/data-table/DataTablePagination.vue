<script setup lang="ts">
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { PaginatedMeta } from '@/types/pagination';

const props = defineProps<{ meta: PaginatedMeta }>();

const emit = defineEmits<{
    (e: 'page-change', page: number): void;
    (e: 'per-page-change', perPage: number): void;
}>();

const pageSizes = [10, 15, 25, 50];
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <!-- Result summary -->
        <p class="text-sm text-muted-foreground">
            <template v-if="meta.from && meta.to">
                Showing {{ meta.from }}&ndash;{{ meta.to }} of {{ meta.total }} results
            </template>
            <template v-else>No results</template>
        </p>

        <div class="flex items-center gap-4">
            <!-- Per-page selector -->
            <div class="flex items-center gap-2">
                <span class="text-sm text-muted-foreground">Rows per page</span>
                <Select
                    :model-value="String(meta.per_page)"
                    @update:model-value="emit('per-page-change', Number($event))"
                >
                    <SelectTrigger class="h-8 w-[70px]">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent side="top">
                        <SelectItem
                            v-for="size in pageSizes"
                            :key="size"
                            :value="String(size)"
                        >
                            {{ size }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Page indicator -->
            <span class="min-w-[80px] text-center text-sm text-muted-foreground">
                Page {{ meta.current_page }} / {{ meta.last_page }}
            </span>

            <!-- Prev / Next -->
            <div class="flex items-center gap-1">
                <Button
                    variant="outline"
                    size="icon"
                    class="size-8"
                    :disabled="meta.current_page <= 1"
                    @click="emit('page-change', meta.current_page - 1)"
                >
                    <ChevronLeft class="size-4" />
                    <span class="sr-only">Previous page</span>
                </Button>
                <Button
                    variant="outline"
                    size="icon"
                    class="size-8"
                    :disabled="meta.current_page >= meta.last_page"
                    @click="emit('page-change', meta.current_page + 1)"
                >
                    <ChevronRight class="size-4" />
                    <span class="sr-only">Next page</span>
                </Button>
            </div>
        </div>
    </div>
</template>
