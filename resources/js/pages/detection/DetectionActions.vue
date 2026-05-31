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
import type { Detection } from '@/types/detection';

const props = defineProps<{ detection: Detection }>();

const emit = defineEmits<{
    (e: 'edit', detection: Detection): void;
    (e: 'delete', detection: Detection): void;
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
            <DropdownMenuItem @click="emit('edit', props.detection)">
                <Pencil />
                Edit
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                class="text-destructive focus:text-destructive"
                @click="emit('delete', props.detection)"
            >
                <Trash2 />
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

