<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { Plus } from 'lucide-vue-next';
import type { AcceptableValue } from 'reka-ui';
import { h, ref } from 'vue';
import CameraController from '@/actions/App/Http/Controllers/CameraController';
import ImagePreview from '@/components/ImagePreview.vue';
import InputError from '@/components/InputError.vue';
import RtspPreview from '@/components/RtspPreview.vue';
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
import { index } from '@/routes/camera';
import type { Camera, CameraFilters, CameraStatus } from '@/types/camera';
import type { Location } from '@/types/location';
import type { PaginatedData } from '@/types/pagination';
import CameraActions from './CameraActions.vue';

const props = defineProps<{
    cameras: PaginatedData<Camera>;
    locations: Location[];
    filters: CameraFilters;
    statuses: Array<CameraStatus>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Camera', href: index() }],
    },
});

// ─── Dialogs ──────────────────────────────────────────────────────────────────

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const showPreviewDialog = ref(false);
const targetCamera = ref<Camera | null>(null);

// ─── Create ───────────────────────────────────────────────────────────────────

const createForm = useForm({
    name: '',
    ip_address: '',
    rtsp_url: '',
    status: 'active' as Camera['status'],
    location_id: '' as number | '',
    image: null as File | null,
});

function openCreate() {
    createForm.reset();
    createForm.status = 'active';
    showCreateDialog.value = true;
}

function submitCreate() {
    createForm.post(CameraController.store.url(), {
        forceFormData: true,
        onSuccess: () => {
            showCreateDialog.value = false;
            createForm.reset();
            createForm.status = 'active';
        },
    });
}

// ─── Edit ─────────────────────────────────────────────────────────────────────

const editForm = useForm({
    name: '',
    ip_address: '',
    rtsp_url: '',
    status: 'active' as Camera['status'],
    location_id: '' as number | '',
    image: null as File | null,
});

function openEdit(camera: Camera) {
    targetCamera.value = camera;
    editForm.name = camera.name;
    editForm.ip_address = camera.ip_address;
    editForm.rtsp_url = camera.rtsp_url ?? '';
    editForm.status = camera.status;
    editForm.location_id = camera.location_id ?? '';
    editForm.image = null;
    showEditDialog.value = true;
}

function submitEdit() {
    if (!targetCamera.value) {
return;
}

    editForm.put(CameraController.update.url(targetCamera.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEditDialog.value = false;
        },
    });
}

// ─── Delete ───────────────────────────────────────────────────────────────────

const deleting = ref(false);

function openDelete(camera: Camera) {
    targetCamera.value = camera;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!targetCamera.value) {
return;
}

    deleting.value = true;
    router.delete(CameraController.destroy.url(targetCamera.value.id), {
        onFinish: () => {
            deleting.value = false;
            showDeleteDialog.value = false;
        },
    });
}

// ─── Preview ──────────────────────────────────────────────────────────────────

const previewStreamUrl = ref<string>('');
const previewLoading = ref(false);

async function openPreview(camera: Camera) {
    targetCamera.value = camera;
    previewStreamUrl.value = '';
    previewLoading.value = true;
    showPreviewDialog.value = true;

    try {
        const response = await fetch(`/camera/${camera.id}/stream`);

        if (!response.ok) {
throw new Error('Failed to fetch stream info');
}

        const data = await response.json();
        previewStreamUrl.value = data.proxy_url ?? data.rtsp_url ?? '';
    } catch {
        previewStreamUrl.value = camera.rtsp_url ?? '';
    } finally {
        previewLoading.value = false;
    }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function onFileChange(form: typeof createForm | typeof editForm, e: Event) {
    form.image = (e.target as HTMLInputElement).files?.[0] ?? null;
}

// ─── DataTable filter handler ─────────────────────────────────────────────────

function handleFilterChange(updates: Partial<CameraFilters & { page: number }>) {
    router.get(index(), { ...props.filters, ...updates }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function handleStatusChange(value: AcceptableValue) {
    handleFilterChange({ status: value as CameraFilters['status'], page: 1 });
}

// ─── Status badge variant ─────────────────────────────────────────────────────

const statusVariant: Record<Camera['status'], 'default' | 'secondary' | 'outline'> = {
    active: 'default',
    inactive: 'secondary',
    maintenance: 'outline',
};

// ─── Column definitions ───────────────────────────────────────────────────────

const columns: ColumnDef<Camera>[] = [
    {
        id: 'image',
        header: 'Image',
        cell: ({ row }) =>
            h(ImagePreview, {
                src: row.original.image_url,
                alt: row.original.name,
                title: row.original.name,
            }),
    },
    {
        accessorKey: 'name',
        enableSorting: true,
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Name' }),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.name),
    },
    {
        accessorKey: 'ip_address',
        enableSorting: true,
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'IP Address' }),
        cell: ({ row }) => h('span', { class: 'font-mono text-sm' }, row.original.ip_address),
    },
    {
        accessorKey: 'rtsp_url',
        enableSorting: false,
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'RTSP URL' }),
        cell: ({ row }) =>
            row.original.rtsp_url
                ? h('a', {
                      class: 'font-mono text-sm text-blue-600 underline',
                      href: row.original.rtsp_url,
                      target: '_blank',
                      title: row.original.rtsp_url,
                  }, row.original.rtsp_url)
                : h('span', { class: 'text-muted-foreground' }, '—'),
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
        id: 'actions',
        header: () => h('span', { class: 'sr-only' }, 'Actions'),
        cell: ({ row }) =>
            h(CameraActions, {
                camera: row.original,
                onPreview: (camera: Camera) => openPreview(camera),
                onEdit: (camera: Camera) => openEdit(camera),
                onDelete: (camera: Camera) => openDelete(camera),
            }),
    },
];
</script>

<template>
    <Head title="Camera" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <!-- Page header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Camera</h1>
                <p class="text-sm text-muted-foreground">Manage cameras by location</p>
            </div>
            <Button @click="openCreate">
                <Plus />
                Add Camera
            </Button>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Label class="text-sm font-medium">Status</Label>
                        <Select v-model="filters.status" @update:model-value="handleStatusChange">
                            <SelectTrigger class="w-40">
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
            :data="cameras.data"
            :meta="{
                current_page: cameras.current_page,
                last_page: cameras.last_page,
                per_page: cameras.per_page,
                total: cameras.total,
                from: cameras.from,
                to: cameras.to,
            }"
            :filters="filters"
            :export-formats="['xlsx', 'csv']"
            export-base-url="/camera/export"
            search-placeholder="Search cameras..."
            @filter-change="handleFilterChange"
        />
    </div>

    <!-- ── Create Dialog ──────────────────────────────────────────────────── -->
    <Dialog v-model:open="showCreateDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add Camera</DialogTitle>
                <DialogDescription>Fill in the details to add a new camera.</DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitCreate">
                <div class="grid gap-1.5">
                    <Label for="c-name">Name <span class="text-destructive">*</span></Label>
                    <Input id="c-name" v-model="createForm.name" placeholder="Camera name" required />
                    <InputError :message="createForm.errors.name" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-ip">IP Address <span class="text-destructive">*</span></Label>
                    <Input id="c-ip" v-model="createForm.ip_address" placeholder="192.168.1.100" required />
                    <InputError :message="createForm.errors.ip_address" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-rtsp">RTSP URL</Label>
                    <Input id="c-rtsp" v-model="createForm.rtsp_url" placeholder="rtsp://192.168.1.100:554/stream" />
                    <InputError :message="createForm.errors.rtsp_url" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-location">Location <span class="text-destructive">*</span></Label>
                    <Select v-model="createForm.location_id">
                        <SelectTrigger id="c-location" class="w-full">
                            <SelectValue placeholder="Select location" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="location in locations"
                                :key="location.id"
                                :value="location.id"
                            >
                                {{ location.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="createForm.errors.location_id" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-status">Status <span class="text-destructive">*</span></Label>
                    <Select v-model="createForm.status">
                        <SelectTrigger id="c-status" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="status in statuses" :value="status">{{ capitalizeFirstLetter(status) }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="createForm.errors.status" />
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
                <DialogTitle>Edit Camera</DialogTitle>
                <DialogDescription>
                    Updating <strong>{{ targetCamera?.name }}</strong>.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitEdit">
                <div class="grid gap-1.5">
                    <Label for="e-name">Name <span class="text-destructive">*</span></Label>
                    <Input id="e-name" v-model="editForm.name" placeholder="Camera name" required />
                    <InputError :message="editForm.errors.name" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-ip">IP Address <span class="text-destructive">*</span></Label>
                    <Input id="e-ip" v-model="editForm.ip_address" placeholder="192.168.1.100" required />
                    <InputError :message="editForm.errors.ip_address" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-rtsp">RTSP URL</Label>
                    <Input id="e-rtsp" v-model="editForm.rtsp_url" placeholder="rtsp://192.168.1.100:554/stream" />
                    <InputError :message="editForm.errors.rtsp_url" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-location">Location <span class="text-destructive">*</span></Label>
                    <Select v-model="editForm.location_id">
                        <SelectTrigger id="e-location" class="w-full">
                            <SelectValue placeholder="Select location" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="location in locations"
                                :key="location.id"
                                :value="location.id"
                            >
                                {{ location.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="editForm.errors.location_id" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-status">Status <span class="text-destructive">*</span></Label>
                    <Select v-model="editForm.status">
                        <SelectTrigger id="e-status" class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="status in statuses" :value="status">{{ capitalizeFirstLetter(status) }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="editForm.errors.status" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-image">Image</Label>
                    <ImagePreview
                        v-if="targetCamera?.image_url"
                        :src="targetCamera.image_url"
                        :alt="targetCamera.name"
                        :title="targetCamera.name"
                    />
                    <p v-if="targetCamera?.image" class="text-xs text-muted-foreground">
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

    <!-- ── Preview Dialog ───────────────────────────────────────────────────── -->
    <Dialog v-model:open="showPreviewDialog">
        <DialogContent class="sm:max-w-3xl">
            <DialogHeader>
                <DialogTitle>Preview — {{ targetCamera?.name }}</DialogTitle>
                <DialogDescription>
                    Streaming from <span class="font-mono text-sm">{{ targetCamera?.rtsp_url }}</span>
                </DialogDescription>
            </DialogHeader>

            <div v-if="previewLoading" class="flex aspect-video w-full items-center justify-center rounded-lg bg-black">
                <Spinner class="size-8 text-white" />
            </div>
            <RtspPreview
                v-else-if="previewStreamUrl"
                :stream-url="previewStreamUrl"
                :camera-name="targetCamera?.name ?? ''"
            />

            <DialogFooter>
                <Button variant="outline" @click="showPreviewDialog = false">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- ── Delete Confirmation Dialog ────────────────────────────────────── -->
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle>Delete Camera</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete
                    <strong>{{ targetCamera?.name }}</strong>? This action cannot be undone.
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
