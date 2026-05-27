<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ItemController from '@/actions/App/Http/Controllers/ItemController';
import { index } from '@/routes/items';
import InputError from '@/components/InputError.vue';
import ImagePreview from '@/components/ImagePreview.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { Item } from '@/types/item';

const props = defineProps<{
    items: Item[];
    pageTitle: string;
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

        <!-- Table -->
        <Card>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b bg-muted/50 text-left">
                                <th class="px-4 py-3 font-medium text-muted-foreground">Image</th>
                                <th class="px-4 py-3 font-medium text-muted-foreground">Name</th>
                                <th class="px-4 py-3 font-medium text-muted-foreground">Description</th>
                                <th class="px-4 py-3 font-medium text-muted-foreground">Status</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="props.items.length === 0">
                                <td
                                    colspan="5"
                                    class="px-4 py-12 text-center text-muted-foreground"
                                >
                                    No items found. Create one to get started.
                                </td>
                            </tr>
                            <tr
                                v-for="item in props.items"
                                :key="item.id"
                                class="border-b transition-colors last:border-0 hover:bg-muted/30"
                            >
                                <!-- Image -->
                                <td class="px-4 py-3">
                                    <ImagePreview
                                        :src="item.image_url"
                                        :alt="item.name"
                                        :title="item.name"
                                        :description="item.description"
                                    />
                                </td>
                                <!-- Name -->
                                <td class="px-4 py-3 font-medium">{{ item.name }}</td>
                                <!-- Description -->
                                <td class="max-w-xs px-4 py-3 text-muted-foreground">
                                    <span class="line-clamp-2">{{
                                        item.description ?? '—'
                                    }}</span>
                                </td>
                                <!-- Status -->
                                <td class="px-4 py-3">
                                    <Badge :variant="item.is_active ? 'default' : 'secondary'">
                                        {{ item.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <!-- Actions -->
                                <td class="px-4 py-3 text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon-sm">
                                                <MoreHorizontal />
                                                <span class="sr-only">Row actions</span>
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="openEdit(item)">
                                                <Pencil />
                                                Edit
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                class="text-destructive focus:text-destructive"
                                                @click="openDelete(item)"
                                            >
                                                <Trash2 />
                                                Delete
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
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