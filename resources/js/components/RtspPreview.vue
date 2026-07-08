<script setup lang="ts">
import Hls from 'hls.js';
import { AlertCircle, RefreshCw } from 'lucide-vue-next';
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    streamUrl: string;
    cameraName: string;
}>();

type StreamState = 'idle' | 'loading' | 'playing' | 'error' | 'rtsp-unsupported';

const videoRef = ref<HTMLVideoElement | null>(null);
const state = ref<StreamState>('idle');
const errorMessage = ref<string>('');
let hlsInstance: Hls | null = null;

const isHlsStream = (url: string): boolean => {
    return url.toLowerCase().includes('.m3u8');
};

const isRtspStream = (url: string): boolean => {
    return url.toLowerCase().startsWith('rtsp://');
};

const cleanup = () => {
    if (hlsInstance) {
        hlsInstance.destroy();
        hlsInstance = null;
    }
};

const loadStream = () => {
    cleanup();
    state.value = 'loading';
    errorMessage.value = '';

    if (!videoRef.value) {
        state.value = 'error';
        errorMessage.value = 'Video element not available';

        return;
    }

    if (isRtspStream(props.streamUrl)) {
        state.value = 'rtsp-unsupported';
        errorMessage.value = 'RTSP streams require a proxy server for browser playback';

        return;
    }

    if (!isHlsStream(props.streamUrl)) {
        state.value = 'error';
        errorMessage.value = 'Unsupported stream format. Only HLS (.m3u8) is supported.';

        return;
    }

    if (Hls.isSupported()) {
        hlsInstance = new Hls({
            enableWorker: true,
            lowLatencyMode: true,
        });

        hlsInstance.loadSource(props.streamUrl);
        hlsInstance.attachMedia(videoRef.value);

        hlsInstance.on(Hls.Events.MANIFEST_PARSED, () => {
            videoRef.value?.play().catch(() => {
                // Autoplay may be blocked by browser
            });
            state.value = 'playing';
        });

        hlsInstance.on(Hls.Events.ERROR, (_event, data) => {
            if (data.fatal) {
                state.value = 'error';
                errorMessage.value = data.details || 'Failed to load stream';
            }
        });
    } else if (videoRef.value.canPlayType('application/vnd.apple.mpegurl')) {
        // Native HLS support (Safari)
        videoRef.value.src = props.streamUrl;
        videoRef.value.play().catch(() => {
            // Autoplay may be blocked by browser
        });
        state.value = 'playing';
    } else {
        state.value = 'error';
        errorMessage.value = 'HLS is not supported in this browser';
    }
};

watch(
    () => props.streamUrl,
    () => {
        if (props.streamUrl) {
            nextTick(() => loadStream());
        } else {
            cleanup();
            state.value = 'idle';
        }
    },
);

onMounted(() => {
    if (props.streamUrl) {
        nextTick(() => loadStream());
    }
});

onUnmounted(() => {
    cleanup();
});

const retry = () => {
    loadStream();
};
</script>

<template>
    <div class="relative w-full aspect-video bg-black rounded-lg overflow-hidden">
        <!-- Video Element -->
        <video
            v-show="state === 'playing'"
            ref="videoRef"
            controls
            autoplay
            muted
            playsinline
            class="w-full h-full object-contain"
        />

        <!-- Loading State -->
        <div
            v-if="state === 'loading' || state === 'idle'"
            class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/80"
        >
            <Spinner class="size-8 text-white" />
            <span class="text-sm text-white/70">
                {{ state === 'loading' ? 'Connecting to stream...' : 'Waiting for stream...' }}
            </span>
        </div>

        <!-- RTSP Unsupported State -->
        <div
            v-if="state === 'rtsp-unsupported'"
            class="absolute inset-0 flex flex-col items-center justify-center gap-4 p-6 bg-black/90"
        >
            <AlertCircle class="size-10 text-amber-500" />
            <div class="text-center max-w-md">
                <h3 class="text-lg font-semibold text-white mb-2">RTSP Stream Detected</h3>
                <p class="text-sm text-white/70 mb-4">
                    {{ errorMessage }}. RTSP streams cannot be played directly in browsers.
                </p>
                <div class="text-left bg-white/10 rounded-lg p-4">
                    <p class="text-xs text-white/50 uppercase tracking-wide mb-2">Solution</p>
                    <p class="text-sm text-white/90 mb-2">
                        Use a proxy service like <code class="bg-white/20 px-1 rounded">ffmpeg</code> to convert RTSP to HLS:
                    </p>
                    <code class="block text-xs text-white/70 bg-black/40 rounded p-2 mt-2">
                        ffmpeg -i rtsp://camera:554/stream -c:v copy -f hls /var/www/hls/stream.m3u8
                    </code>
                    <p class="text-xs text-white/60 mt-3">
                        Or use a WebRTC gateway for real-time streaming.
                    </p>
                </div>
            </div>
            <Button variant="outline" size="sm" @click="retry" class="mt-2">
                <RefreshCw class="size-4" />
                Retry
            </Button>
        </div>

        <!-- Error State -->
        <div
            v-if="state === 'error'"
            class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-black/90"
        >
            <AlertCircle class="size-10 text-red-500" />
            <div class="text-center">
                <h3 class="text-lg font-semibold text-white mb-1">Stream Error</h3>
                <p class="text-sm text-white/70">{{ errorMessage }}</p>
            </div>
            <Button variant="outline" size="sm" @click="retry">
                <RefreshCw class="size-4" />
                Retry
            </Button>
        </div>

        <!-- Camera Name Overlay -->
        <div
            v-if="state === 'playing'"
            class="absolute top-0 left-0 right-0 px-3 py-2 bg-gradient-to-b from-black/70 to-transparent"
        >
            <span class="text-sm font-medium text-white">{{ cameraName }}</span>
        </div>
    </div>
</template>
