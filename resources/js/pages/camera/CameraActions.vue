<script setup lang="ts">
import { MoreHorizontal, Pencil, Trash2, Play } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { Camera } from '@/types/camera';

const props = defineProps<{ camera: Camera }>();

const emit = defineEmits<{
    (e: 'preview', camera: Camera): void;
    (e: 'edit', camera: Camera): void;
    (e: 'delete', camera: Camera): void;
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
            <DropdownMenuItem v-if="camera.rtsp_url" @click="emit('preview', props.camera)">
                <Play />
                Preview
            </DropdownMenuItem>
            <DropdownMenuSeparator v-if="camera.rtsp_url" />
            <DropdownMenuItem @click="emit('edit', props.camera)">
                <Pencil />
                Edit
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                class="text-destructive focus:text-destructive"
                @click="emit('delete', props.camera)"
            >
                <Trash2 />
                Delete
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
