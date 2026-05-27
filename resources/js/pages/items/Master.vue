<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { h, ref } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import ItemController from '@/actions/App/Http/Controllers/ItemController';
import { index } from '@/routes/items';
import ImagePreview from '@/components/ImagePreview.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
    Card,
    CardContent,
} from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Plus } from 'lucide-vue-next';
import type { DataTableFilters, PaginatedData } from '@/types/pagination';
import type { Item, ItemFilters } from '@/types/item';
import ItemActions from './ItemActions.vue';
import type { AcceptableValue } from 'reka-ui'

const props = defineProps<{
    items: PaginatedData<Item>;
    pageTitle: string;
    filters: ItemFilters;
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Items', href: index() }],
    },
});

// ─── Dialogs ──────────────────────────────────────────────────────────────────

const showCreateDialog = ref(false);
const showEditDialog = ref(false);
const showDeleteDialog = ref(false);
const targetItem = ref<Item | null>(null);

// ─── Create ───────────────────────────────────────────────────────────────────

const createForm = useForm({
    name: '',
    description: '',
    image: null as File | null,
    is_active: true,
});

function openCreate() {
    createForm.reset();
    showCreateDialog.value = true;
}

function submitCreate() {
    createForm.post(ItemController.store.url(), {
        forceFormData: true,
        onSuccess: () => {
            showCreateDialog.value = false;
            createForm.reset();
        },
    });
}

// ─── Edit ─────────────────────────────────────────────────────────────────────

const editForm = useForm({
    name: '',
    description: '',
    image: null as File | null,
    is_active: true,
});

function openEdit(item: Item) {
    targetItem.value = item;
    editForm.name = item.name;
    editForm.description = item.description ?? '';
    editForm.image = null;
    editForm.is_active = item.is_active;
    showEditDialog.value = true;
}

function submitEdit() {
    if (!targetItem.value) return;
    editForm.put(ItemController.update.url(targetItem.value.id), {
        forceFormData: true,
        onSuccess: () => {
            showEditDialog.value = false;
        },
    });
}

// ─── Delete ───────────────────────────────────────────────────────────────────

const deleting = ref(false);

function openDelete(item: Item) {
    targetItem.value = item;
    showDeleteDialog.value = true;
}

function confirmDelete() {
    if (!targetItem.value) return;
    deleting.value = true;
    router.delete(ItemController.destroy.url(targetItem.value.id), {
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

function handleFilterChange(updates: Partial<ItemFilters & { page: number }>) {
    router.get(index(), { ...props.filters, ...updates }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function handleStatusChange(value: AcceptableValue) {
    handleFilterChange({ status: value as ItemFilters['status'], page: 1 });
}

// ─── Column definitions ───────────────────────────────────────────────────────

const columns: ColumnDef<Item>[] = [
    {
        id: 'image',
        header: 'Image',
        cell: ({ row }) =>
            h(ImagePreview, {
                src: row.original.image_url,
                alt: row.original.name,
                title: row.original.name,
                description: row.original.description,
            }),
    },
    {
        accessorKey: 'name',
        enableSorting: true,
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Name' }),
        cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.name),
    },
    {
        accessorKey: 'description',
        header: 'Description',
        cell: ({ row }) =>
            h(
                'span',
                { class: 'line-clamp-2 max-w-xs text-muted-foreground' },
                row.original.description ?? '—',
            ),
    },
    {
        accessorKey: 'is_active',
        enableSorting: true,
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),
        cell: ({ row }) =>
            h(
                Badge,
                { variant: row.original.is_active ? 'default' : 'secondary' },
                () => (row.original.is_active ? 'Active' : 'Inactive'),
            ),
    },
    {
        id: 'actions',
        header: () => h('span', { class: 'sr-only' }, 'Actions'),
        cell: ({ row }) =>
            h(ItemActions, {
                item: row.original,
                onEdit: (item: Item) => openEdit(item),
                onDelete: (item: Item) => openDelete(item),
            }),
    },
];
</script>

<template>
    <Head :title="props.pageTitle" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4">
        <!-- Page header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Items</h1>
                <p class="text-sm text-muted-foreground">Manage detection items</p>
            </div>
            <Button @click="openCreate">
                <Plus />
                Add Item
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
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- DataTable -->
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
            :filters="filters"
            search-placeholder="Search items..."
            @filter-change="handleFilterChange"
        />
    </div>

    <!-- ── Create Dialog ──────────────────────────────────────────────────── -->
    <Dialog v-model:open="showCreateDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Add Item</DialogTitle>
                <DialogDescription>Fill in the details to create a new item.</DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitCreate">
                <div class="grid gap-1.5">
                    <Label for="c-name">Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="c-name"
                        v-model="createForm.name"
                        placeholder="Item name"
                        required
                    />
                    <InputError :message="createForm.errors.name" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="c-desc">Description</Label>
                    <textarea
                        id="c-desc"
                        v-model="createForm.description"
                        rows="3"
                        placeholder="Optional description"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />
                    <InputError :message="createForm.errors.description" />
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

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="c-active"
                        v-model="createForm.is_active"
                    />
                    <Label for="c-active" class="cursor-pointer font-normal">Active</Label>
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
                <DialogTitle>Edit Item</DialogTitle>
                <DialogDescription>
                    Updating <strong>{{ targetItem?.name }}</strong
                    >.
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submitEdit">
                <div class="grid gap-1.5">
                    <Label for="e-name">Name <span class="text-destructive">*</span></Label>
                    <Input
                        id="e-name"
                        v-model="editForm.name"
                        placeholder="Item name"
                        required
                    />
                    <InputError :message="editForm.errors.name" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-desc">Description</Label>
                    <textarea
                        id="e-desc"
                        v-model="editForm.description"
                        rows="3"
                        placeholder="Optional description"
                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />
                    <InputError :message="editForm.errors.description" />
                </div>

                <div class="grid gap-1.5">
                    <Label for="e-image">Image</Label>
                    <ImagePreview
                        v-if="targetItem?.image_url"
                        :src="targetItem.image_url"
                        :alt="targetItem.name"
                        :title="targetItem.name"
                        :description="targetItem.description"
                    />
                    <p v-if="targetItem?.image" class="text-xs text-muted-foreground">
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

                <div class="flex items-center gap-2">
                    <Checkbox
                        id="e-active"
                        v-model="editForm.is_active"
                    />
                    <Label for="e-active" class="cursor-pointer font-normal">Active</Label>
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
                <DialogTitle>Delete Item</DialogTitle>
                <DialogDescription>
                    Are you sure you want to delete
                    <strong>{{ targetItem?.name }}</strong
                    >? This action cannot be undone.
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