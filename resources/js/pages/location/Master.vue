```vue
<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';

import LocationController from '@/actions/App/Http/Controllers/LocationController';
import { index } from '@/routes/location';

import InputError from '@/components/InputError.vue';
import LocationActions from './locationActions.vue';

import { Button } from '@/components/ui/button';

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

import { Spinner } from '@/components/ui/spinner';

import { Plus } from 'lucide-vue-next';

import type { DataTableFilters, PaginatedData } from '@/types/pagination';

import type { Location } from '@/types/location';

// ─────────────────────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────────────────────

const props = defineProps<{
    items: PaginatedData<Location>;
    pageTitle: string;
    filters: DataTableFilters;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Location', href: index() }],
    },
});

// ─────────────────────────────────────────────────────────────
// Dialog State
// ─────────────────────────────────────────────────────────────

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);

const targetItem = ref<Location | null>(null);

const deleting = ref(false);

// ─────────────────────────────────────────────────────────────
// Create Form
// ─────────────────────────────────────────────────────────────

const createForm = useForm({
    name: '',
    description: '',
    address: '',
    latitude: '',
    longitude: '',
});

function openCreate() {
    createForm.reset();

    showCreateDialog.value = true;
}

function submitCreate() {
    createForm.post(LocationController.store.url(), {
        onSuccess: () => {
            showCreateDialog.value = false;
            createForm.reset();
        },
    });
}

// ─────────────────────────────────────────────────────────────
// Edit Form
// ─────────────────────────────────────────────────────────────

const editForm = useForm({
    name: '',
    description: '',
    address: '',
    latitude: '',
    longitude: '',
});

function openEdit(location: Location) {
    targetItem.value = location;

    editForm.name = location.name;
    editForm.description = location.description ?? '';
    editForm.address = location.address ?? '';
    editForm.latitude = location.latitude ?? '';
    editForm.longitude = location.longitude ?? '';

    showEditDialog.value = true;
}

function submitEdit() {
    if (!targetItem.value) return;

    editForm.put(
        LocationController.update.url(targetItem.value.id),

        {
            onSuccess: () => {
                showEditDialog.value = false;
            },
        },
    );
}

// ─────────────────────────────────────────────────────────────
// Delete
// ─────────────────────────────────────────────────────────────

function openDelete(location: Location) {
    targetItem.value = location;

    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!targetItem.value) return;

    deleting.value = true;

    router.delete(
        LocationController.destroy.url(targetItem.value.id),

        {
            onFinish: () => {
                deleting.value = false;
                showDeleteDialog.value = false;
            },
        },
    );
}

// ─────────────────────────────────────────────────────────────
// Filters
// ─────────────────────────────────────────────────────────────

function handleFilterChange(
    updates: Partial<DataTableFilters & { page: number }>,
) {
    router.get(
        index(),
        {
            ...props.filters,
            ...updates,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

// ─────────────────────────────────────────────────────────────
// Table Columns
// ─────────────────────────────────────────────────────────────

const columns: ColumnDef<Location>[] = [
    {
        accessorKey: 'name',

        enableSorting: true,

        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column,
                title: 'Name',
            }),

        cell: ({ row }) =>
            h(
                'span',
                {
                    class: 'font-medium',
                },
                row.original.name,
            ),
    },

    {
        accessorKey: 'description',

        header: 'Description',

        cell: ({ row }) =>
            h(
                'span',
                {
                    class: 'line-clamp-2 max-w-xs text-muted-foreground',
                },
                row.original.description ?? '—',
            ),
    },

    {
        accessorKey: 'address',

        header: 'Address',

        cell: ({ row }) =>
            h(
                'span',
                {
                    class: 'max-w-xs',
                },
                row.original.address ?? '—',
            ),
    },

    {
        accessorKey: 'latitude',

        header: 'Latitude',

        cell: ({ row }) => h('span', {}, row.original.latitude ?? '—'),
    },

    {
        accessorKey: 'longitude',

        header: 'Longitude',

        cell: ({ row }) => h('span', {}, row.original.longitude ?? '—'),
    },

    {
        id: 'actions',

        header: () =>
            h(
                'span',
                {
                    class: 'sr-only',
                },
                'Actions',
            ),

        cell: ({ row }) =>
            h(LocationActions, {
                item: row.original,

                onEdit: (location: Location) => openEdit(location),

                onDelete: (location: Location) => openDelete(location),
            }),
    },
];
</script>

<template>
    <Head :title="props.pageTitle" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Locations</h1>

                <p class="text-sm text-muted-foreground">
                    Manage location data
                </p>
            </div>

            <Button @click="openCreate">
                <Plus class="mr-2 size-4" />
                Add Location
            </Button>
        </div>

        <!-- Table -->
        <DataTable
            :columns="columns"
            :data="items.data"
            :meta="{
                current_page: items.current_page,
                last_page: items.last_page,
                per_page: items.per_page,
                total: items.total,
                from: items.from,
                to: items.to,
            }"
            :filters="props.filters"
            search-placeholder="Search locations..."
            @filter-change="handleFilterChange"
        />
    </div>

    <!-- Create Dialog -->
    <Dialog v-model:open="showCreateDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add Location</DialogTitle>

                <DialogDescription>
                    Fill in location information.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitCreate">
                <div class="grid gap-1.5">
                    <Label>Name</Label>

                    <Input
                        v-model="createForm.name"
                        placeholder="Location name"
                    />

                    <InputError :message="createForm.errors.name" />
                </div>

                <div class="grid gap-1.5">
                    <Label>Description</Label>

                    <textarea
                        v-model="createForm.description"
                        rows="3"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    />

                    <InputError :message="createForm.errors.description" />
                </div>

                <div class="grid gap-1.5">
                    <Label>Address</Label>

                    <Input v-model="createForm.address" placeholder="Address" />

                    <InputError :message="createForm.errors.address" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label>Latitude</Label>

                        <Input
                            v-model="createForm.latitude"
                            placeholder="-7.2575"
                        />
                    </div>

                    <div class="grid gap-1.5">
                        <Label>Longitude</Label>

                        <Input
                            v-model="createForm.longitude"
                            placeholder="112.7521"
                        />
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="showCreateDialog = false"
                    >
                        Cancel
                    </Button>

                    <Button type="submit" :disabled="createForm.processing">
                        <Spinner
                            v-if="createForm.processing"
                            class="mr-2 size-4"
                        />

                        Save
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Edit Dialog -->
    <Dialog v-model:open="showEditDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Edit Location</DialogTitle>

                <DialogDescription>
                    Update location information.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitEdit">
                <div class="grid gap-1.5">
                    <Label>Name</Label>

                    <Input v-model="editForm.name" />

                    <InputError :message="editForm.errors.name" />
                </div>

                <div class="grid gap-1.5">
                    <Label>Description</Label>

                    <textarea
                        v-model="editForm.description"
                        rows="3"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    />

                    <InputError :message="editForm.errors.description" />
                </div>

                <div class="grid gap-1.5">
                    <Label>Address</Label>

                    <Input v-model="editForm.address" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="grid gap-1.5">
                        <Label>Latitude</Label>

                        <Input v-model="editForm.latitude" />
                    </div>

                    <div class="grid gap-1.5">
                        <Label>Longitude</Label>

                        <Input v-model="editForm.longitude" />
                    </div>
                </div>

                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        @click="showEditDialog = false"
                    >
                        Cancel
                    </Button>

                    <Button type="submit" :disabled="editForm.processing">
                        <Spinner
                            v-if="editForm.processing"
                            class="mr-2 size-4"
                        />

                        Update
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
    <Dialog v-model:open="showDeleteDialog">
        <DialogContent class="sm:max-w-sm">
            <DialogHeader>
                <DialogTitle> Delete Location </DialogTitle>

                <DialogDescription>
                    Are you sure want to delete
                    <strong>{{ targetItem?.name }}</strong> ?
                </DialogDescription>
            </DialogHeader>

            <DialogFooter>
                <Button variant="outline" @click="showDeleteDialog = false">
                    Cancel
                </Button>

                <Button
                    variant="destructive"
                    :disabled="deleting"
                    @click="confirmDelete"
                >
                    <Spinner v-if="deleting" class="mr-2 size-4" />

                    Delete
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
```
