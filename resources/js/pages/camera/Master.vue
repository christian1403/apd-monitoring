<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import CameraActions from './CameraActions.vue';

type Location = {
    id: number;
    name: string;
};

type Camera = {
    id: number;
    name: string;
    ip_address: string;
    status: string;
    image?: string | null;
    location?: Location | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedCameras = {
    data: Camera[];
    links?: PaginationLink[];
};

const props = withDefaults(
    defineProps<{
        cameras?: PaginatedCameras;
        locations?: Location[];
        filters?: {
            search?: string;
        };
    }>(),
    {
        cameras: () => ({
            data: [],
            links: [],
        }),
        locations: () => [],
        filters: () => ({
            search: '',
        }),
    },
);

const search = ref(props.filters.search ?? '');
const showCreate = ref(false);

const form = useForm({
    name: '',
    ip_address: '',
    status: 'active',
    location_id: '',
    image: null as File | null,
});

function setImage(event: Event) {
    form.image = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submitCreate() {
    form.post('/camera', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showCreate.value = false;
            form.reset();
            form.status = 'active';
        },
    });
}

function doSearch() {
    router.get(
        '/camera',
        { search: search.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function imageUrl(path?: string | null) {
    return path ? `/storage/${path}` : '';
}
</script>

<template>
    <Head title="Camera" />

    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Camera</h1>

                <p class="text-sm text-gray-500">
                    Kelola data kamera berdasarkan lokasi.
                </p>
            </div>

            <button
                type="button"
                class="rounded bg-black px-4 py-2 text-white hover:bg-gray-800"
                @click="showCreate = true"
            >
                Tambah Camera
            </button>
        </div>

        <div class="flex gap-2">
            <input
                v-model="search"
                type="text"
                placeholder="Cari camera, IP, status, atau lokasi..."
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
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">IP Address</th>
                        <th class="px-4 py-3 text-left">Lokasi</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="camera in props.cameras.data"
                        :key="camera.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">
                            <img
                                v-if="camera.image"
                                :src="imageUrl(camera.image)"
                                class="h-12 w-16 rounded object-cover"
                                alt="camera"
                            />

                            <span v-else>-</span>
                        </td>

                        <td class="px-4 py-3 font-medium">
                            {{ camera.name }}
                        </td>

                        <td class="px-4 py-3">
                            {{ camera.ip_address }}
                        </td>

                        <td class="px-4 py-3">
                            {{ camera.location?.name ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs">
                                {{ camera.status }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <CameraActions
                                :camera="camera"
                                :locations="props.locations"
                            />
                        </td>
                    </tr>

                    <tr v-if="props.cameras.data.length === 0">
                        <td
                            colspan="6"
                            class="px-4 py-6 text-center text-gray-500"
                        >
                            Data camera belum tersedia.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="props.cameras.links && props.cameras.links.length > 0"
            class="flex flex-wrap gap-2"
        >
            <template
                v-for="link in props.cameras.links"
                :key="link.label"
            >
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="rounded border px-3 py-1 text-sm"
                    :class="{ 'bg-black text-white': link.active }"
                    v-html="link.label"
                />

                <span
                    v-else
                    class="rounded border px-3 py-1 text-sm text-gray-400"
                    v-html="link.label"
                />
            </template>
        </div>

        <div
            v-if="showCreate"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        >
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h2 class="mb-4 text-lg font-semibold">
                    Tambah Camera
                </h2>

                <form class="space-y-4" @submit.prevent="submitCreate">
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
                                v-for="location in props.locations"
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