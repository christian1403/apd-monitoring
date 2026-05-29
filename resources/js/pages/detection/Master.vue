<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DetectionActions from './DetectionActions.vue';

const props = defineProps<{
    detections: any;
    items: any[];
    cameras: any[];
    locations: any[];
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters?.search ?? '');
const showCreate = ref(false);

const form = useForm({
    item_id: '',
    camera_id: '',
    location_id: '',
    status: 'safe',
    detected_at: '',
    image: null as File | null,
});

function doSearch() {
    router.get('/detection', {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
}

function setImage(event: Event) {
    form.image = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submitCreate() {
    form.post('/detection', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            form.reset();
            form.status = 'safe';
        },
    });
}

function imageUrl(path: string | null) {
    return path ? `/storage/${path}` : '';
}
</script>

<template>
    <Head title="Data Detection" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Data Detection</h1>
                <p class="text-sm text-gray-500">
                    Kelola hasil deteksi APD berdasarkan item, kamera, dan lokasi.
                </p>
            </div>

            <button
                type="button"
                class="rounded bg-black px-4 py-2 text-white hover:bg-gray-800"
                @click="showCreate = true"
            >
                Tambah Detection
            </button>
        </div>

        <div class="flex gap-2">
            <input
                v-model="search"
                type="text"
                placeholder="Cari detection..."
                class="w-full rounded border px-3 py-2"
                @keyup.enter="doSearch"
            />

            <button
                type="button"
                class="rounded border px-4 py-2 hover:bg-gray-50"
                @click="doSearch"
            >
                Cari
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border bg-white">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Image</th>
                        <th class="px-4 py-3 text-left">Item</th>
                        <th class="px-4 py-3 text-left">Camera</th>
                        <th class="px-4 py-3 text-left">Lokasi</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="detection in detections.data"
                        :key="detection.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            <img
                                v-if="detection.image"
                                :src="imageUrl(detection.image)"
                                class="h-12 w-16 rounded object-cover"
                                alt="detection"
                            />
                            <span v-else>-</span>
                        </td>

                        <td class="px-4 py-3">
                            {{ detection.item?.name ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ detection.camera?.name ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ detection.location?.name ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs">
                                {{ detection.status }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            {{ detection.detected_at ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-right">
                            <DetectionActions
                                :detection="detection"
                                :items="items"
                                :cameras="cameras"
                                :locations="locations"
                            />
                        </td>
                    </tr>

                    <tr v-if="detections.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                            Data detection belum tersedia.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="showCreate"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">Tambah Detection</h2>

                <form class="space-y-4" @submit.prevent="submitCreate">
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
                            @click="showCreate = false"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="rounded bg-black px-4 py-2 text-white"
                            :disabled="form.processing"
                        >
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
