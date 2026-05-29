<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    detection: any;
    items: any[];
    cameras: any[];
    locations: any[];
}>();

const showEdit = ref(false);

const form = useForm({
    item_id: '',
    camera_id: '',
    location_id: '',
    status: 'safe',
    detected_at: '',
    image: null as File | null,
    _method: 'put',
});

function openEdit() {
    form.item_id = String(props.detection.item_id ?? '');
    form.camera_id = String(props.detection.camera_id ?? '');
    form.location_id = String(props.detection.location_id ?? '');
    form.status = props.detection.status ?? 'safe';

    form.detected_at = props.detection.detected_at
        ? props.detection.detected_at.substring(0, 16)
        : '';

    form.image = null;
    form.clearErrors();

    showEdit.value = true;
}

function setImage(event: Event) {
    form.image = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submitEdit() {
    form.post(`/detection/${props.detection.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showEdit.value = false;
            form.reset();
        },
    });
}

function deleteDetection() {
    if (!confirm('Hapus data detection ini?')) return;

    router.delete(`/detection/${props.detection.id}`, {
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
            @click="deleteDetection"
        >
            Hapus
        </button>
    </div>

    <div
        v-if="showEdit"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
    >
        <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
            <h2 class="mb-4 text-lg font-semibold">Edit Detection</h2>

            <form class="space-y-4" @submit.prevent="submitEdit">
                <div>
                    <label class="text-sm font-medium">Item APD</label>
                    <select
                        v-model="form.item_id"
                        class="w-full rounded border px-3 py-2"
                    >
                        <option value="">Pilih Item</option>
                        <option
                            v-for="item in items"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.item_id" class="text-sm text-red-600">
                        {{ form.errors.item_id }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium">Camera</label>
                    <select
                        v-model="form.camera_id"
                        class="w-full rounded border px-3 py-2"
                    >
                        <option value="">Pilih Camera</option>
                        <option
                            v-for="camera in cameras"
                            :key="camera.id"
                            :value="camera.id"
                        >
                            {{ camera.name }} - {{ camera.ip_address }}
                        </option>
                    </select>
                    <p v-if="form.errors.camera_id" class="text-sm text-red-600">
                        {{ form.errors.camera_id }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium">Lokasi</label>
                    <select
                        v-model="form.location_id"
                        class="w-full rounded border px-3 py-2"
                    >
                        <option value="">Otomatis dari Camera</option>
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
                        <option value="safe">Safe</option>
                        <option value="warning">Warning</option>
                        <option value="unsafe">Unsafe</option>
                    </select>
                    <p v-if="form.errors.status" class="text-sm text-red-600">
                        {{ form.errors.status }}
                    </p>
                </div>

                <div>
                    <label class="text-sm font-medium">Waktu Deteksi</label>
                    <input
                        v-model="form.detected_at"
                        type="datetime-local"
                        class="w-full rounded border px-3 py-2"
                    />
                    <p v-if="form.errors.detected_at" class="text-sm text-red-600">
                        {{ form.errors.detected_at }}
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
