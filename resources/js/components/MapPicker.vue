<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Fix default marker icons (Leaflet + bundler issue)
import iconUrl from 'leaflet/dist/images/marker-icon.png';
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import shadowUrl from 'leaflet/dist/images/marker-shadow.png';

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl });

const props = withDefaults(
    defineProps<{
        modelValue?: { lat: number; lng: number };
        initialLat?: number;
        initialLng?: number;
        zoom?: number;
    }>(),
    {
        modelValue: undefined,
        initialLat: -2.5,
        initialLng: 118,
        zoom: 5,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: { lat: number; lng: number }];
}>();

const mapContainer = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;
const currentLat = ref<string>('');
const currentLng = ref<string>('');
const searchQuery = ref('');
const isSearching = ref(false);

function emitPosition() {
    if (currentLat.value && currentLng.value) {
        emit('update:modelValue', {
            lat: parseFloat(currentLat.value),
            lng: parseFloat(currentLng.value),
        });
    }
}

function placeMarker(lat: number, lng: number) {
    if (!map) return;

    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', () => {
            const pos = marker!.getLatLng();
            currentLat.value = pos.lat.toFixed(6);
            currentLng.value = pos.lng.toFixed(6);
            emitPosition();
        });
    }

    currentLat.value = lat.toFixed(6);
    currentLng.value = lng.toFixed(6);
    map.setView([lat, lng], map.getZoom());

    emitPosition();
}

function initMap() {
    if (!mapContainer.value) return;

    const centerLat = props.modelValue?.lat ?? props.initialLat;
    const centerLng = props.modelValue?.lng ?? props.initialLng;

    map = L.map(mapContainer.value).setView([centerLat, centerLng], props.zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    map.on('click', (e: L.LeafletMouseEvent) => {
        placeMarker(e.latlng.lat, e.latlng.lng);
    });

    // If modelValue provided, place initial marker
    if (props.modelValue?.lat && props.modelValue?.lng) {
        placeMarker(props.modelValue.lat, props.modelValue.lng);
    }

    // Invalidate size after render
    setTimeout(() => {
        map?.invalidateSize();
    }, 100);
}

async function searchAddress() {
    if (!searchQuery.value.trim() || !map) return;

    isSearching.value = true;

    try {
        const q = encodeURIComponent(searchQuery.value.trim());
        const res = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${q}&limit=1`,
        );

        const data = await res.json();

        if (data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            placeMarker(lat, lng);
            map!.setView([lat, lng], 15);
        }
    } catch {
        // Nominatim failed silently — user can still click map
    } finally {
        isSearching.value = false;
    }
}

// Watch external modelValue changes
watch(
    () => props.modelValue,
    (val) => {
        if (val?.lat && val?.lng) {
            currentLat.value = val.lat.toString();
            currentLng.value = val.lng.toString();

            if (marker) {
                marker.setLatLng([val.lat, val.lng]);
                map?.setView([val.lat, val.lng], map.getZoom());
            } else if (map) {
                placeMarker(val.lat, val.lng);
            }
        }
    },
    { deep: true },
);

onMounted(() => {
    nextTick(() => initMap());
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <div class="flex flex-col gap-3">
        <!-- Search -->
        <div class="flex gap-2">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Search address..."
                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                @keydown.enter.prevent="searchAddress"
            />
            <button
                type="button"
                class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:opacity-50"
                :disabled="isSearching || !searchQuery.trim()"
                @click="searchAddress"
            >
                {{ isSearching ? 'Searching...' : 'Search' }}
            </button>
        </div>

        <!-- Map -->
        <div
            ref="mapContainer"
            class="h-[280px] w-full rounded-md border border-border"
        ></div>

        <!-- Coordinates -->
        <div class="grid grid-cols-2 gap-3">
            <div class="grid gap-1">
                <span class="text-xs font-medium text-muted-foreground">Latitude</span>
                <span class="font-mono text-sm" :class="currentLat ? 'text-foreground' : 'text-muted-foreground'">
                    {{ currentLat || 'Click map to set' }}
                </span>
            </div>
            <div class="grid gap-1">
                <span class="text-xs font-medium text-muted-foreground">Longitude</span>
                <span class="font-mono text-sm" :class="currentLng ? 'text-foreground' : 'text-muted-foreground'">
                    {{ currentLng || 'Click map to set' }}
                </span>
            </div>
        </div>
    </div>
</template>
