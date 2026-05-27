<script setup lang="ts">
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { ImageIcon } from 'lucide-vue-next';

const props = defineProps<{
    src: string | null;
    alt: string;
    title?: string;
    description?: string | null;
}>();

const open = ref(false);

function handleClick() {
    if (!props.src) return;
    open.value = true;
}
</script>

<template>
    <!-- Thumbnail -->
    <div
        class="flex size-16 items-center justify-center overflow-hidden rounded-md border bg-muted"
        :class="src ? 'cursor-pointer' : ''"
        @click="handleClick"
    >
        <img
            v-if="src"
            :src="src"
            :alt="alt"
            class="h-full w-full object-cover transition-opacity hover:opacity-80"
        />
        <ImageIcon v-else class="size-4 text-muted-foreground" />
    </div>

    <!-- Preview Dialog -->
    <Dialog v-model:open="open">
        <DialogContent class="flex flex-col items-center gap-4 sm:max-w-xl">
            <DialogHeader v-if="title || description" class="w-full">
                <DialogTitle v-if="title">{{ title }}</DialogTitle>
                <DialogDescription v-if="description">{{ description }}</DialogDescription>
            </DialogHeader>
            <img
                v-if="src"
                :src="src"
                :alt="alt"
                class="max-h-[60vh] w-full rounded-md object-contain"
            />
        </DialogContent>
    </Dialog>
</template>
