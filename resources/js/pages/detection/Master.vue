<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { Plus } from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import { h, ref } from 'vue';
import DetectionController from '@/actions/App/Http/Controllers/DetectionController';
import ImagePreview from '@/components/ImagePreview.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { DataTable, DataTableColumnHeader } from '@/components/ui/data-table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { capitalizeFirstLetter } from '@/lib/utils';
import { index } from '@/routes/detection';
import type { Camera } from '@/types/camera';
import type { Detection, DetectionFilters, DetectionStatus, DetectionItem } from '@/types/detection';
import type { Item } from '@/types/item';
import type { Location } from '@/types/location';
import type { PaginatedData } from '@/types/pagination';
import DetectionActions from './DetectionActions.vue';

const props = defineProps<{
    detections: PaginatedData<Detection>;
    items: Pick<Item, 'id' | 'name'>[];
    cameras: (Pick<Camera, 'id' | 'name' | 'ip_address'> & { location: Pick<Location, 'id' | 'name'> | null })[];
    locations: Pick<Location, 'id' | 'name'>[];
    filters: DetectionFilters;
    statuses: Array<DetectionStatus>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Detection', href: index() }],
    },
});

// ─── Dialogs ──────────────────────────────────────────────────────────────────

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const targetDetection = ref<Detection | null>(null);

// ─── Create ───────────────────────────────────────────────────────────────────

type ItemStatus = 'detected' | 'undetected';
type DetectionItemForm = { item_id: number | ''; status: ItemStatus };

const ITEM_STATUSES: ItemStatus[] = ['detected', 'undetected'];

const createForm = useForm({
    items: [{ item_id: '', status: 'undetected' as ItemStatus }] as DetectionItemForm[],
    camera_id: '' as number | '',
    location_id: '' as number | '',
    detected_at: '',
    image: null as File | null,
});

function addCreateItem() {
    createForm.items.push({ item_id: '', status: 'undetected' });
}

function removeCreateItem(index: number) {
    createForm.items.splice(index, 1);
}

function openCreate() {
    createForm.reset();
    createForm.items = [{ item_id: '', status: 'undetected' }];
    showCreateDialog.value = true;
}

function submitCreate() {
    createForm.post(DetectionController.store.url(), {
        forceFormData: true,
        onSuccess: () => {
            showCreateDialog.value = false;
            createForm.reset();
        },
    });
}

// ─── Edit ─────────────────────────────────────────────────────────────────────

const editForm = useForm({
    items: [{ item_id: '', status: 'undetected' as ItemStatus }] as DetectionItemForm[],
    camera_id: '' as number | '',
    location_id: '' as number | '',
    detected_at: '',
    image: null as File | null,
});

function addEditItem() {
    editForm.items.push({ item_id: '', status: 'undetected' });
}

function removeEditItem(index: number) {
    editForm.items.splice(index, 1);
}

function openEdit(detection: Detection) {
    targetDetection.value = detection;
    editForm.items = detection.detection_items.length > 0
        ? detection.detection_items.map((di: DetectionItem) => ({ item_id: di.item_id, status: di.status as ItemStatus }))
        : [{ item_id: '', status: 'undetected' }];
    editForm.camera_id = detection.camera_id ?? '';
    editForm.location_id = detection.location_id ?? '';
    editForm.detected_at = detection.detected_at ? detection.detected_at.substring(0, 16) : '';
    editForm.image = null;
    showEditDialog.value = true;
}

function submitEdit() {
    if (!targetDetection.value) {
return;
}

    editForm.put(DetectionController.update.url(targetDetection.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEditDialog.value = false;
        },
    });
}

// ─── Delete ───────────────────────────────────────────────────────────────────

const deleting = ref(false);

function openDelete(detection: Detection) {
    targetDetection.value = detection;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!targetDetection.value) {
return;
}

    deleting.value = true;
    router.delete(DetectionController.destroy.url(targetDetection.value.id), {
        onFinish: () => {
            deleting.value = false;
            showDeleteDialog.value = false;
        },
    });
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function onFileChange(form: typeof createForm | typeof editForm, e: Event) {
    form.image = (e.target as HTMLInputElement).files?.[0] ?? null;
}

// ─── DataTable filter handler ─────────────────────────────────────────────────

function handleFilterChange(updates: Partial<DetectionFilters & { page: number }>) {
    router.get(index(), { ...props.filters, ...updates }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function handleStatusChange(value: AcceptableValue) {
    handleFilterChange({ status: value as DetectionFilters['status'], page: 1 });
}

// ─── Status badge variant ─────────────────────────────────────────────────────

const statusVariant: Record<Detection['status'], 'default' | 'secondary' | 'destructive'> = {
    safe: 'default',
    warning: 'secondary',
    unsafe: 'destructive',
};

// ─── Column definitions ───────────────────────────────────────────────────────

const columns: ColumnDef<Detection>[] = [
    {
        id: 'image',
        header: 'Image',
        cell: ({ row }) =>
            h(ImagePreview, {
                src: row.original.image_url,
                alt: `detection #${row.original.id}`,
                title: `detection #${row.original.id}`,
            }),
    },
    {
        id: 'items',
        header: 'Items',
        cell: ({ row }) => {
            const items = row.original.detection_items ?? [];

            if (items.length === 0) {
return h('span', { class: 'text-muted-foreground' }, '—');
}

            return h('div', { class: 'flex flex-wrap gap-1' },
                items.map((di: DetectionItem) =>
                    h(Badge, { variant: 'outline', class: 'text-xs' }, () => di.item?.name ?? '—'),
                ),
            );
        },
    },
    {
        id: 'camera',
        header: 'Camera',
        cell: ({ row }) =>
            h('span', { class: 'text-muted-foreground' }, row.original.camera?.name ?? '—'),
    },
    {
        id: 'location',
        header: 'Location',
        cell: ({ row }) =>
            h('span', { class: 'text-muted-foreground' }, row.original.location?.name ?? '—'),
    },
    {
        accessorKey: 'status',
        enableSorting: true,
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),
        cell: ({ row }) =>
            h(
                Badge,
                { variant: statusVariant[row.original.status] },
                () => row.original.status.charAt(0).toUpperCase() + row.original.status.slice(1),
            ),
    },
    {
        accessorKey: 'detected_at',
        enableSorting: true,
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Detected At' }),
        cell: ({ row }) =>
            h('span', { class: 'text-sm' }, row.original.detected_at ?? '—'),
    },
    {
        id: 'actions',
        header: () => h('span', { class: 'sr-only' }, 'Actions'),
        cell: ({ row }) =>
            h(DetectionActions, {
                detection: row.original,
                onEdit: (d: Detection) => openEdit(d),
                onDelete: (d: Detection) => openDelete(d),
            }),
    },
];
</script>

<template>
    <Head title="Detection" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <!-- Page header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Detection</h1>
                <p class="text-sm text-muted-foreground">Manage APD detection results</p>
            </div>
            <Button @click="openCreate">
                <Plus />
                Add Detection
            </Button>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Label class="text-sm font-medium">Status</Label>
                        <Select v-model="filters.status" @update:model-value="handleStatusChange">
                            <SelectTrigger class="w-36">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All</SelectItem>
                                <SelectItem v-for="status in statuses" :value="status">{{ capitalizeFirstLetter(status) }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- DataTable -->
        <DataTable
            :columns="columns"
            :data="detections.data"
            :meta="{
                current_page: detections.current_page,
                last_page: detections.last_page,
                per_page: detections.per_page,
                total: detections.total,
                from: detections.from,
                to: detections.to,
            }"
            :filters="filters"
            :export-formats="['xlsx', 'csv']"
            export-base-url="/detection/export"
            search-placeholder="Search detections..."
            @filter-change="handleFilterChange"
        />
    </div>

    <!-- ── Create Dialog ──────────────────────────────────────────────────── -->
    <Dialog v-model:open="showCreateDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add Detection</DialogTitle>
                <DialogDescription>Fill in the details to record a new detection.</DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitCreate">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label>Item APD <span class="text-destructive">*</span></Label>
                        <Button type="button" variant="outline" size="sm" @click="addCreateItem">
                            <Plus class="size-3" />
                            Add Item
                        </Button>
                    </div>
                    <div v-for="(item, idx) in createForm.items" :key="idx" class="flex items-end gap-2">
                        <div class="grid flex-1 gap-1.5">
                            <Label v-if="idx === 0" class="text-xs text-muted-foreground">Item</Label>
                            <Select v-model="item.item_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select item" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="opt in items" :key="opt.id" :value="opt.id">
                                        {{ opt.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="grid w-32 gap-1.5">
                            <Label v-if="idx === 0" class="text-xs text-muted-foreground">Status</Label>
                            <Select v-model="item.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="status in ITEM_STATUSES" :key="status" :value="status">{{ capitalizeFirstLetter(status) }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button
                            v-if="createForm.items.length > 1"
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="text-destructive"
                            @click="removeCreateItem(idx)"
                        >
                            ✕
                        </Button>
                    </div>
                    <InputError :message="createForm.errors.items" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-camera">Camera <span class="text-destructive">*</span></Label>
                    <Select v-model="createForm.camera_id">
                        <SelectTrigger id="c-camera" class="w-full">
                            <SelectValue placeholder="Select camera" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="camera in cameras" :key="camera.id" :value="camera.id">
                                {{ camera.name }} — {{ camera.ip_address }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="createForm.errors.camera_id" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-location">Location</Label>
                    <Select v-model="createForm.location_id">
                        <SelectTrigger id="c-location" class="w-full">
                            <SelectValue placeholder="Auto from camera" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="location in locations" :key="location.id" :value="location.id">
                                {{ location.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="createForm.errors.location_id" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-detected-at">Detected At</Label>
                    <Input id="c-detected-at" v-model="createForm.detected_at" type="datetime-local" />
                    <InputError :message="createForm.errors.detected_at" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-image">Image</Label>
                    <Input
                        id="c-image"
                        type="file"
                        accept="image/jpg,image/jpeg,image/png,image/webp"
                        @change="onFileChange(createForm, $event)"
                    />
                    <InputError :message="createForm.errors.image" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showCreateDialog = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="createForm.processing">
                        <Spinner v-if="createForm.processing" class="size-4" />
                        Save
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- ── Edit Dialog ────────────────────────────────────────────────────── -->
    <Dialog v-model:open="showEditDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Edit Detection</DialogTitle>
                <DialogDescription>
                    Updating detection #{{ targetDetection?.id }}.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitEdit">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <Label>Item APD <span class="text-destructive">*</span></Label>
                        <Button type="button" variant="outline" size="sm" @click="addEditItem">
                            <Plus class="size-3" />
                            Add Item
                        </Button>
                    </div>
                    <div v-for="(item, idx) in editForm.items" :key="idx" class="flex items-end gap-2">
                        <div class="grid flex-1 gap-1.5">
                            <Label v-if="idx === 0" class="text-xs text-muted-foreground">Item</Label>
                            <Select v-model="item.item_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select item" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="opt in items" :key="opt.id" :value="opt.id">
                                        {{ opt.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="grid w-32 gap-1.5">
                            <Label v-if="idx === 0" class="text-xs text-muted-foreground">Status</Label>
                            <Select v-model="item.status">
                                <SelectTrigger class="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="status in ITEM_STATUSES" :key="status" :value="status">{{ capitalizeFirstLetter(status) }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <Button
                            v-if="editForm.items.length > 1"
                            type="button"
                            variant="ghost"
                            size="icon"
                            class="text-destructive"
                            @click="removeEditItem(idx)"
                        >
                            ✕
                        </Button>
                    </div>
                    <InputError :message="editForm.errors.items" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-camera">Camera <span class="text-destructive">*</span></Label>
                    <Select v-model="editForm.camera_id">
                        <SelectTrigger id="e-camera" class="w-full">
                            <SelectValue placeholder="Select camera" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="camera in cameras" :key="camera.id" :value="camera.id">
                                {{ camera.name }} — {{ camera.ip_address }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="editForm.errors.camera_id" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-location">Location</Label>
                    <Select v-model="editForm.location_id">
                        <SelectTrigger id="e-location" class="w-full">
                            <SelectValue placeholder="Auto from camera" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="location in locations" :key="location.id" :value="location.id">
                                {{ location.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="editForm.errors.location_id" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-detected-at">Detected At</Label>
                    <Input id="e-detected-at" v-model="editForm.detected_at" type="datetime-local" />
                    <InputError :message="editForm.errors.detected_at" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-image">Image</Label>
                    <ImagePreview
                        v-if="targetDetection?.image_url"
                        :src="targetDetection.image_url"
                        :alt="`detection #${targetDetection?.id}`"
                        :title="`detection #${targetDetection?.id}`"
                    />
                    <p v-if="targetDetection?.image" class="text-xs text-muted-foreground">
                        A new upload will replace the existing image.
                    </p>
                    <Input
                        id="e-image"
                        type="file"
                        accept="image/jpg,image/jpeg,image/png,image/webp"
                        @change="onFileChange(editForm, $event)"
                    />
                    <InputError :message="editForm.errors.image" />
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="showEditDialog = false">
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="editForm.processing">
                        <Spinner v-if="editForm.processing" class="size-4" />
                        Update
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- ── Delete Confirmation Dialog ────────────────────────────────────── -->
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>Delete Detection</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete this detection record? This action cannot be undone.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
                <Button variant="destructive" :disabled="deleting" @click="confirmDelete">
                    <Spinner v-if="deleting" class="size-4" />
                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

