import { ref, computed, shallowRef, watch } from 'vue';
import { useCoordinateConverter } from './useCoordinateConverter.composables';
import { useToolManager } from './useToolManager.composables';
import { useImageLoader } from './useImageLoader.composables';

export interface CoordinateConverterOptions {
    originalImageWidth: number;
    originalImageHeight: number;
    scale: number;
    offsetX: number;
    offsetY: number;
}

export interface ZoneEditorOptions {
    originalImageWidth: number;
    originalImageHeight: number;
    cameraName: string;
    apiBaseUrl?: string;
}

export interface ZoneEditorState {
    scale: number;
    offsetX: number;
    offsetY: number;
    isDragging: boolean;
    lastMousePos: { x: number; y: number };
    isLoading: boolean;
    error: string | null;
}

export function useZoneEditor(options: ZoneEditorOptions) {
    const {
        originalImageWidth,
        originalImageHeight,
        cameraName,
        apiBaseUrl = '/api',
    } = options;

    // Initialize helper composables
    const coordinateConverter = useCoordinateConverter({
        originalImageWidth,
        originalImageHeight,
        scale: 1,
        offsetX: 0,
        offsetY: 0,
    });

    const toolManager = useToolManager();
    const imageLoader = useImageLoader();

    // Editor state
    const scale = ref(1);
    const offsetX = ref(0);
    const offsetY = ref(0);
    const isDragging = ref(false);
    const lastMousePos = ref({ x: 0, y: 0 });
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    // Computed properties
    const transformedState = computed(() => {
        return {
            polygons: toolManager.state.value.polygons.map(polygon => ({
                ...polygon,
                points: polygon.points.map(point => ({
                    ...point,
                    screenX: coordinateConverter.imageToScreen(point.x, point.y).x,
                    screenY: coordinateConverter.imageToScreen(point.x, point.y).y,
                })),
            })),
            greenLines: toolManager.state.value.greenLines.map(line => ({
                ...line,
                start: {
                    ...line.start,
                    screenX: coordinateConverter.imageToScreen(line.start.x, line.start.y).x,
                    screenY: coordinateConverter.imageToScreen(line.start.x, line.start.y).y,
                },
                end: {
                    ...line.end,
                    screenX: coordinateConverter.imageToScreen(line.end.x, line.end.y).x,
                    screenY: coordinateConverter.imageToScreen(line.end.x, line.end.y).y,
                },
            })),
        };
    });

    const config = computed(() => {
        return {
            green_line: toolManager.state.value.greenLines.map(line => [
                [line.start.x, line.start.y],
                [line.end.x, line.end.y]
            ]),
            red_zone_polygons: toolManager.state.value.polygons.map(polygon =>
                polygon.points.map(point => [point.x, point.y])
            ),
        };
    });

    // Event handlers
    function handleMouseDown(e: MouseEvent) {
        if (toolManager.state.value.currentTool === 'none') return;

        const rect = document.getElementById('canvas-container')?.getBoundingClientRect();
        if (!rect) return;

        const screenX = e.clientX - rect.left;
        const screenY = e.clientY - rect.top;

        const imageCoord = coordinateConverter.screenToImage(screenX, screenY);
        const clampedCoord = coordinateConverter.clampImageCoordinates(imageCoord.x, imageCoord.y);

        isDragging.value = true;
        lastMousePos.value = { x: e.clientX, y: e.clientY };

        switch (toolManager.state.value.currentTool) {
            case 'polygon':
                toolManager.startPolygon(clampedCoord.x, clampedCoord.y);
                break;
            case 'greenLine':
                if (toolManager.state.value.currentGreenLinePoints.length === 0) {
                    toolManager.startGreenLine(clampedCoord.x, clampedCoord.y);
                } else {
                    toolManager.addGreenLinePoint(clampedCoord.x, clampedCoord.y);
                    toolManager.completeGreenLine();
                }
                break;
        }
    }

    function handleMouseMove(e: MouseEvent) {
        if (!isDragging.value || toolManager.state.value.currentTool === 'none') return;

        const rect = document.getElementById('canvas-container')?.getBoundingClientRect();
        if (!rect) return;

        const currentX = e.clientX - rect.left;
        const currentY = e.clientY - rect.top;
        const deltaX = currentX - lastMousePos.value.x;
        const deltaY = currentY - lastMousePos.value.y;

        offsetX.value += deltaX / scale.value;
        offsetY.value += deltaY / scale.value;

        lastMousePos.value = { x: currentX, y: currentY };
    }

    function handleMouseUp() {
        isDragging.value = false;
    }

    function handleWheel(e: WheelEvent) {
        e.preventDefault();
        const rect = document.getElementById('canvas-container')?.getBoundingClientRect();
        if (!rect) return;

        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;
        const oldScale = scale.value;

        const delta = e.deltaY > 0 ? 0.9 : 1.1;
        const newScale = Math.max(0.5, Math.min(3, oldScale * delta));

        const imageX = (mouseX - offsetX.value) / oldScale;
        const imageY = (mouseY - offsetY.value) / oldScale;

        scale.value = newScale;

        offsetX.value = mouseX - imageX * newScale;
        offsetY.value = mouseY - imageY * newScale;
    }

    function handleKeyDown(e: KeyboardEvent) {
        if (e.ctrlKey && e.key === 'z') {
            e.preventDefault();
            toolManager.undo();
        } else if (e.ctrlKey && e.key === 'y') {
            e.preventDefault();
            toolManager.redo();
        } else if (e.key === 'r') {
            e.preventDefault();
            toolManager.reset();
        }
    }

    // API methods
    async function loadConfig() {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`${apiBaseUrl}/config`);

            if (!response.ok) {
                throw new Error(`Failed to load config: ${response.statusText}`);
            }

            const config = await response.json();
            toolManager.load(config);
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error occurred';
            console.error('[Zone] Error loading config:', err);
        } finally {
            isLoading.value = false;
        }
    }

    async function saveConfig() {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch(`${apiBaseUrl}/config`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(config.value),
            });

            if (!response.ok) {
                throw new Error(`Failed to save config: ${response.statusText}`);
            }

            console.log('[Zone] Configuration saved successfully');
        } catch (err) {
            error.value = err instanceof Error ? err.message : 'Unknown error occurred';
            console.error('[Zone] Error saving config:', err);
        } finally {
            isLoading.value = false;
        }
    }

    function setTool(tool: 'polygon' | 'greenLine') {
        if (tool === 'polygon' && toolManager.state.value.currentTool !== 'polygon') {
            toolManager.startPolygon(0, 0);
        } else if (tool === 'greenLine' && toolManager.state.value.currentTool !== 'greenLine') {
            toolManager.startGreenLine(0, 0);
        }
    }

    function completeCurrentDrawing() {
        if (toolManager.state.value.currentTool === 'polygon' && toolManager.state.value.currentPolygonPoints.length >= 3) {
            toolManager.completePolygon();
        } else if (toolManager.state.value.currentTool === 'greenLine' && toolManager.state.value.currentGreenLinePoints.length === 2) {
            toolManager.completeGreenLine();
        }
    }

    function initialize(cameraImageUrl: string) {
        imageLoader.loadImage(cameraImageUrl);
    }

    // Watch for scale changes to update coordinate converter
    watch([scale, offsetX, offsetY], () => {
        coordinateConverter.update({ scale: scale.value, offsetX: offsetX.value, offsetY: offsetY.value });
    });

    return {
        // State
        scale: computed(() => scale.value),
        offsetX: computed(() => offsetX.value),
        offsetY: computed(() => offsetY.value),
        isDragging: computed(() => isDragging.value),
        isLoading: computed(() => isLoading.value),
        error: computed(() => error.value),
        cameraName: computed(() => cameraName),
        toolManager,
        imageLoader,
        transformedState,
        config,

        // Methods
        handleMouseDown,
        handleMouseMove,
        handleMouseUp,
        handleWheel,
        handleKeyDown,
        loadConfig,
        saveConfig,
        setTool,
        completeCurrentDrawing,
        initialize,
        coordinateConverter,
    };
}
export type { ZoneEditorOptions, ZoneEditorState };