<script setup lang="ts">
import { MoreHorizontal, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Location } from '@/types/location';

const props = defineProps<{ item: Location }>();

const emit = defineEmits<{
    (e: 'edit', item: Location): void;
    (e: 'delete', item: Location): void;
}>();
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon-sm">
                <MoreHorizontal />
                <span class="sr-only">Row actions</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuItem @click="emit('edit', props.item)">
                <Pencil />
                Edit
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                class="text-destructive focus:text-destructive"
                @click="emit('delete', props.item)"
            >
                <Trash2 />
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
