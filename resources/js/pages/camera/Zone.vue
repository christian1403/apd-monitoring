<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, nextTick, onMounted, onUnmounted, computed } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import type { Camera } from '@/types/camera';

import {
    Stage as KonvaStage,
    Layer as KonvaLayer,
    Image as KonvaImage,
    Line as KonvaLine,
} from 'vue-konva';

const props = defineProps<{
    camera: Camera;
}>();

// ─── Types ────────────────────────────────────────────────────────────────────────

interface Point { x: number; y: number; }
interface PolygonData { points: Point[]; id: string; }
interface GreenLineData { start: Point; end: Point; id: string; }

// ─── Konva State ──────────────────────────────────────────────────────────────────

const stageWidth = ref(0);
const stageHeight = ref(0);
const stageImage = ref<HTMLImageElement | null>(null);
const viewScale = ref(1);     // zoom multiplier
const viewX = ref(0);         // pan offset (screen px)
const viewY = ref(0);
const isPanning = ref(false);
const lastMousePos = ref({ x: 0, y: 0 });

// Drawing state
const currentTool = ref<'none' | 'polygon' | 'greenLine'>('none');
const currentPoints = ref<Point[]>([]);
const polygons = ref<PolygonData[]>([]);
const greenLines = ref<GreenLineData[]>([]);
const history = ref<any[]>([]);
const redoStack = ref<any[]>([]);
const saving = ref(false);
const loading = ref(false);

// ─── Image dimensions ──────────────────────────────────────────────────────────────

const imgW = computed(() => stageImage.value?.naturalWidth ?? 1280);
const imgH = computed(() => stageImage.value?.naturalHeight ?? 720);

// ─── Coordinate Engine ─────────────────────────────────────────────────────────────
//
//   image coords   → (multiply by scale, add zoom/pan)  → screen coords
//   screen coords  → (subtract zoom/pan, divide by scale) → image coords
//
// scale = display size of image / natural size of image
// Since we stretch image to fill canvas:
//   scaleX = stageWidth  / imgW
//   scaleY = stageHeight / imgH
//
// We use separate X/Y scales because KonvaImage stretches both dimensions.
// But for drawing accuracy, we use aspect-aware behaviour:
//   scale = min(scaleX, scaleY) PLUS centering for fit — OR — stretch both.
//
// For zone-editor use, the image is STRETCHED to fill the canvas,
// so we use independent X/Y scales.

const scaleX = computed(() => stageWidth.value / imgW.value);
const scaleY = computed(() => stageHeight.value / imgH.value);

function imgToScreenX(ix: number): number {
    return ix * scaleX.value * viewScale.value + viewX.value;
}
function imgToScreenY(iy: number): number {
    return iy * scaleY.value * viewScale.value + viewY.value;
}

function screenToImg(sx: number, sy: number): Point {
    const ix = (sx - viewX.value) / (scaleX.value * viewScale.value);
    const iy = (sy - viewY.value) / (scaleY.value * viewScale.value);
    return {
        x: Math.max(0, Math.min(ix, imgW.value)),
        y: Math.max(0, Math.min(iy, imgH.value)),
    };
}

// ─── Canvas sizing ─────────────────────────────────────────────────────────────────

function updateSize(): void {
    nextTick(() => {
        const el = document.getElementById('canvas-container');
        if (el) { stageWidth.value = el.clientWidth; stageHeight.value = el.clientHeight; }
    });
}

// ─── Image loading ─────────────────────────────────────────────────────────────────

function loadImage(src: string | null): void {
    if (!src) { toast.error('No image available'); return; }
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => { stageImage.value = img; updateSize(); };
    img.onerror = () => toast.error('Failed to load camera image');
    img.src = src;
}

// ─── History ───────────────────────────────────────────────────────────────────────

function clone<T>(o: T): T { return JSON.parse(JSON.stringify(o)); }
function snap() { return { polygons: clone(polygons.value), greenLines: clone(greenLines.value) }; }
function push() { history.value.push(snap()); redoStack.value = []; }
function id(): string { return `${Date.now()}-${Math.random().toString(36).substring(2, 8)}`; }

// ─── Stage click: screen → image ───────────────────────────────────────────────────

function handleStageClick(e: any): void {
    const pos = e.target.getStage()?.getPointerPosition();
    if (!pos || currentTool.value === 'none') return;

    const p = screenToImg(pos.x, pos.y);

    if (currentTool.value === 'polygon') {
        if (currentPoints.value.length === 0) push();
        currentPoints.value.push(p);

        if (currentPoints.value.length >= 3) {
            const f = currentPoints.value[0];
            const dx = p.x - f.x;
            const dy = p.y - f.y;
            if (Math.sqrt(dx * dx + dy * dy) < 15 / viewScale.value / scaleX.value) {
                polygons.value = [{ points: clone(currentPoints.value), id: id() }];
                currentPoints.value = [];
                currentTool.value = 'none';
                toast.success('Polygon completed');
            }
        }
    } else if (currentTool.value === 'greenLine') {
        if (currentPoints.value.length === 0) push();
        currentPoints.value.push(p);
        if (currentPoints.value.length === 2) {
            greenLines.value = [{
                start: { ...currentPoints.value[0] },
                end: { ...currentPoints.value[1] },
                id: id(),
            }];
            currentPoints.value = [];
            currentTool.value = 'none';
            toast.success('Green line completed');
        }
    }
}

// ─── Pan & Zoom ────────────────────────────────────────────────────────────────────

function handleMouseDown(e: MouseEvent): void {
    isPanning.value = true;
    lastMousePos.value = { x: e.clientX, y: e.clientY };
}
function handleMouseMove(e: MouseEvent): void {
    if (!isPanning.value) return;
    const dx = e.clientX - lastMousePos.value.x;
    const dy = e.clientY - lastMousePos.value.y;
    viewX.value += dx;
    viewY.value += dy;
    lastMousePos.value = { x: e.clientX, y: e.clientY };
}
function handleMouseUp(): void { isPanning.value = false; }

function handleWheel(e: WheelEvent): void {
    e.preventDefault();
    const rect = document.getElementById('canvas-container')?.getBoundingClientRect();
    if (!rect) return;
    const mx = e.clientX - rect.left;
    const my = e.clientY - rect.top;
    const os = viewScale.value;
    const ns = Math.max(0.5, Math.min(5, os * (e.deltaY > 0 ? 0.92 : 1.08)));
    viewScale.value = ns;
    viewX.value = mx - ((mx - viewX.value) / os) * ns;
    viewY.value = my - ((my - viewY.value) / os) * ns;
}

// ─── Tooling ───────────────────────────────────────────────────────────────────────

function setTool(tool: 'polygon' | 'greenLine'): void {
    if (currentTool.value === tool) { currentTool.value = 'none'; currentPoints.value = []; return; }
    currentTool.value = tool; currentPoints.value = [];
}

function undo(): void {
    if (currentPoints.value.length > 0) {
        currentPoints.value.pop();
        if (currentPoints.value.length === 0) {
            currentTool.value = 'none';
            const prev = history.value.pop();
            if (prev) { redoStack.value.push(snap()); polygons.value = clone(prev.polygons); greenLines.value = clone(prev.greenLines); }
        }
        return;
    }
    if (history.value.length === 0) return;
    redoStack.value.push(snap());
    const prev = history.value.pop();
    polygons.value = clone(prev.polygons);
    greenLines.value = clone(prev.greenLines);
}

function redo(): void {
    if (redoStack.value.length === 0) return;
    history.value.push(snap());
    const next = redoStack.value.pop();
    polygons.value = clone(next.polygons);
    greenLines.value = clone(next.greenLines);
}

function reset(): void {
    if (!polygons.value.length && !greenLines.value.length && !currentPoints.value.length) return;
    push();
    polygons.value = []; greenLines.value = []; currentPoints.value = []; currentTool.value = 'none';
    toast.info('All drawings cleared');
}

function handleKeyDown(e: KeyboardEvent): void {
    if (e.ctrlKey && e.key === 'z') { e.preventDefault(); undo(); }
    else if (e.ctrlKey && e.key === 'y') { e.preventDefault(); redo(); }
    else if (e.key === 'r' && !e.ctrlKey) { e.preventDefault(); reset(); }
}

// ─── Save / Load (via Laravel proxy) ───────────────────────────────────────────────

function roundCoords(p: Point): number[] {
    return [Math.round(p.x), Math.round(p.y)];
}

async function save(): Promise<void> {
    if (saving.value) return;
    saving.value = true;
    const config = {
        green_line: greenLines.value.length > 0
            ? [roundCoords(greenLines.value[0].start), roundCoords(greenLines.value[0].end)]
            : [],
        red_zone_polygons: polygons.value.map(p => p.points.map(pt => roundCoords(pt))),
    };
    try {
        const r = await fetch('/api/camera/' + props.camera.id + '/zone/config', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(config),
        });
        const data = await r.json();
        if (!r.ok) throw new Error(data?.error || r.statusText);
        toast.success('Zone configuration saved');
    } catch (err: any) {
        toast.error('Save failed: ' + (err.message || 'Unknown error'));
        console.error('[Zone] Save error:', err);
    } finally { saving.value = false; }
}

async function loadConfig(): Promise<void> {
    if (loading.value) return;
    loading.value = true;
    try {
        const r = await fetch('/api/camera/' + props.camera.id + '/zone/config');
        const data = await r.json();
        if (!r.ok) { if (r.status === 404) { toast.info('No saved configuration found'); return; } throw new Error(data?.error || r.statusText); }
        greenLines.value = []; polygons.value = [];
        if (data.green_line) {
            for (const line of data.green_line) {
                if (line.length === 2) greenLines.value.push({
                    start: { x: line[0][0], y: line[0][1] },
                    end: { x: line[1][0], y: line[1][1] },
                    id: id(),
                });
            }
        }
        if (data.red_zone_polygons) {
            for (const poly of data.red_zone_polygons) {
                polygons.value.push({
                    points: poly.map((pt: number[]) => ({ x: pt[0], y: pt[1] })),
                    id: id(),
                });
            }
        }
        currentPoints.value = []; currentTool.value = 'none';
        toast.success('Configuration loaded');
    } catch (err: any) {
        toast.error('Load failed: ' + (err.message || 'Unknown error'));
        console.error('[Zone] Load error:', err);
    } finally { loading.value = false; }
}

function goBack(): void { router.visit('/camera'); }

// ─── Lifecycle ─────────────────────────────────────────────────────────────────────

onMounted(() => {
    updateSize();
    loadImage(props.camera.image_url);
    window.addEventListener('resize', () => setTimeout(updateSize, 100));
    window.addEventListener('keydown', handleKeyDown);
});
onUnmounted(() => {
    window.removeEventListener('resize', () => {});
    window.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <Head title="Zone Editor" />
    <div class="flex h-screen flex-col overflow-hidden bg-background">
        <!-- Header -->
        <header class="flex shrink-0 items-center justify-between border-b bg-card px-6 py-3">
            <Button variant="ghost" size="sm" @click="goBack">← Back</Button>
            <div class="text-center">
                <span class="text-xs text-muted-foreground">Camera:</span>
                <p class="text-sm font-medium leading-tight">{{ props.camera.name }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" size="sm" :disabled="loading" @click="loadConfig">{{ loading ? 'Loading…' : 'Load Config' }}</Button>
                <Button variant="outline" size="sm" :disabled="saving" @click="save">{{ saving ? 'Saving…' : 'Save Zone' }}</Button>
            </div>
        </header>

        <!-- Canvas -->
        <main
            id="canvas-container"
            class="flex-1 px-6 pb-4 pt-2 select-none"
            :style="{ height: 'calc(100vh - 140px)' }"
            @mousedown="handleMouseDown"
            @mousemove="handleMouseMove"
            @mouseup="handleMouseUp"
            @mouseleave="handleMouseUp"
            @wheel="handleWheel"
        >
            <div class="relative h-full w-full overflow-hidden rounded-lg border bg-card">
                <div v-if="!stageImage" class="flex h-full w-full items-center justify-center text-sm text-muted-foreground">
                    Loading image…
                </div>

                <KonvaStage
                    v-if="stageImage && stageWidth > 0"
                    :config="{ width: stageWidth, height: stageHeight }"
                    @click="handleStageClick"
                >
                    <KonvaLayer>
                        <!-- Background — stretched to fill canvas -->
                        <KonvaImage
                            :config="{
                                image: stageImage,
                                x: 0, y: 0,
                                width: stageWidth, height: stageHeight,
                            }"
                        />

                        <!-- Completed polygons — image coords → screen coords -->
                        <KonvaLine
                            v-for="p in polygons" :key="p.id"
                            :config="{
                                points: p.points.flatMap(pt => [imgToScreenX(pt.x), imgToScreenY(pt.y)]),
                                fill: 'rgba(255,0,0,0.3)',
                                stroke: 'red',
                                strokeWidth: 2,
                                closed: true,
                            }"
                        />

                        <!-- Completed green lines -->
                        <KonvaLine
                            v-for="l in greenLines" :key="l.id"
                            :config="{
                                points: [
                                    imgToScreenX(l.start.x), imgToScreenY(l.start.y),
                                    imgToScreenX(l.end.x), imgToScreenY(l.end.y),
                                ],
                                stroke: 'limegreen',
                                strokeWidth: 4,
                                lineCap: 'round',
                            }"
                        />

                        <!-- Current drawing -->
                        <KonvaLine
                            v-if="currentPoints.length > 0"
                            :config="{
                                points: currentPoints.flatMap(pt => [imgToScreenX(pt.x), imgToScreenY(pt.y)]),
                                fill: currentTool === 'polygon' ? 'rgba(255,0,0,0.15)' : undefined,
                                stroke: currentTool === 'polygon' ? 'red' : 'limegreen',
                                strokeWidth: currentTool === 'polygon' ? 2 : 4,
                                closed: false,
                                lineCap: 'round',
                            }"
                        />
                    </KonvaLayer>
                </KonvaStage>
            </div>
        </main>

        <!-- Toolbar -->
        <footer class="flex shrink-0 items-center justify-center gap-3 border-t bg-card px-6 py-3">
            <Button variant="outline" size="sm" :class="{ 'bg-blue-100 border-blue-400': currentTool === 'polygon' }" @click="setTool('polygon')">Polygon</Button>
            <Button variant="outline" size="sm" :class="{ 'bg-green-100 border-green-400': currentTool === 'greenLine' }" @click="setTool('greenLine')">Green Line</Button>
            <Separator orientation="vertical" class="h-6" />
            <Button variant="ghost" size="sm" :disabled="history.length === 0 && currentPoints.length === 0" @click="undo">Undo</Button>
            <Button variant="ghost" size="sm" @click="reset">Reset</Button>
            <Separator orientation="vertical" class="h-6" />
            <span class="text-xs text-muted-foreground">Zoom: {{ Math.round(viewScale * 100) }}%</span>
            <span v-if="currentTool !== 'none'" class="text-xs font-medium" :class="currentTool === 'polygon' ? 'text-blue-600' : 'text-green-600'">
                ● {{ currentTool === 'polygon' ? 'Click to add points — click near first point to close' : 'Click 2 points to draw line' }}
            </span>
        </footer>
    </div>
</template>
