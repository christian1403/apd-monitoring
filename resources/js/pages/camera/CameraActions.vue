<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    camera: any;
    locations: any[];
}>();

const showEdit = ref(false);

const form = useForm({
    name: '',
    ip_address: '',
    status: 'active',
    location_id: '',
    image: null as File | null,
    _method: 'put',
});

function openEdit() {
    form.name = props.camera.name;
    form.ip_address = props.camera.ip_address;
    form.status = props.camera.status;
    form.location_id = String(props.camera.location_id ?? '');
    form.image = null;
    form.clearErrors();

    showEdit.value = true;
}

function setImage(event: Event) {
    form.image = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submitEdit() {
    form.post(`/camera/${props.camera.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showEdit.value = false;
            form.reset();
        },
    });
}

function deleteCamera() {
    if (!confirm(`Hapus camera "${props.camera.name}"?`)) return;

    router.delete(`/camera/${props.camera.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="flex justify-end gap-2">
        <button
            type="button"
            class="rounded border px-3 py-1 text-xs hover:bg-gray-50"
            @click="openEdit"
        >
            Edit
        </button>

        <button
            type="button"
            class="rounded bg-red-600 px-3 py-1 text-xs text-white hover:bg-red-700"
            @click="deleteCamera"
        >
            Hapus
        </button>
    </div>

    <div
        v-if="showEdit"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
            <h2 class="mb-4 text-lg font-semibold">Edit Camera</h2>

            <form class="space-y-4" @submit.prevent="submitEdit">
                <div>
                    <label class="text-sm font-medium">Nama Camera</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded border px-3 py-2"
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-600">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium">IP Address</label>
                    <input
                        v-model="form.ip_address"
                        type="text"
                        class="w-full rounded border px-3 py-2"
                    />
                    <p v-if="form.errors.ip_address" class="text-sm text-red-600">
                        {{ form.errors.ip_address }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium">Lokasi</label>
                    <select
                        v-model="form.location_id"
                        class="w-full rounded border px-3 py-2"
                    >
                        <option value="">Pilih Lokasi</option>
                        <option
                            v-for="location in locations"
                            :key="location.id"
                            :value="location.id"
                        >
                            {{ location.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.location_id" class="text-sm text-red-600">
                        {{ form.errors.location_id }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium">Status</label>
                    <select
                        v-model="form.status"
                        class="w-full rounded border px-3 py-2"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                    <p v-if="form.errors.status" class="text-sm text-red-600">
                        {{ form.errors.status }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium">Image</label>
                    <input
                        type="file"
                        accept="image/*"
                        class="w-full rounded border px-3 py-2"
                        @change="setImage"
                    />
                    <p v-if="form.errors.image" class="text-sm text-red-600">
                        {{ form.errors.image }}
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded border px-4 py-2"
                        @click="showEdit = false"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="rounded bg-black px-4 py-2 text-white"
                        :disabled="form.processing"
                    >
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
