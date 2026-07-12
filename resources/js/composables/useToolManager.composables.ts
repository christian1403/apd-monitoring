import { ref, computed } from 'vue';

export interface Point {
    x: number;
    y: number;
    id: string;
}

export interface Polygon {
    points: Point[];
    id: string;
}

export interface GreenLine {
    start: Point;
    end: Point;
    id: string;
}

export interface ToolState {
    currentTool: 'none' | 'polygon' | 'greenLine';
    polygons: Polygon[];
    greenLines: GreenLine[];
    currentPolygonPoints: Point[];
    currentGreenLinePoints: Point[];
    history: HistoryEntry[];
    redoStack: HistoryEntry[];
}

export interface HistoryEntry {
    polygons: Polygon[];
    greenLines: GreenLine[];
}

function generateId(): string {
    return `${Date.now()}-${Math.random().toString(36).substring(2, 8)}`;
}

function deepClone<T>(obj: T): T {
    return JSON.parse(JSON.stringify(obj));
}

export function useToolManager() {
    const state = ref<ToolState>({
        currentTool: 'none',
        polygons: [],
        greenLines: [],
        currentPolygonPoints: [],
        currentGreenLinePoints: [],
        history: [],
        redoStack: [],
    });

    function snapshot(): HistoryEntry {
        return {
            polygons: deepClone(state.value.polygons),
            greenLines: deepClone(state.value.greenLines),
        };
    }

    function pushHistory() {
        state.value.history.push(snapshot());
        state.value.redoStack = [];
    }

    // ── Public API ─────────────────────────────────────────────────

    function startPolygon(x: number, y: number) {
        pushHistory();
        state.value.currentTool = 'polygon';
        state.value.currentPolygonPoints = [{ x, y, id: generateId() }];
    }

    function addPolygonPoint(x: number, y: number) {
        if (state.value.currentTool !== 'polygon') return;

        const point: Point = { x, y, id: generateId() };
        state.value.currentPolygonPoints.push(point);

        // Check if clicking near the first point to close the polygon
        const firstPoint = state.value.currentPolygonPoints[0];
        const distance = Math.sqrt(
            (x - firstPoint.x) ** 2 + (y - firstPoint.y) ** 2
        );

        // Auto-close when clicking within 15 pixels of the first point
        if (
            state.value.currentPolygonPoints.length >= 3 &&
            distance < 15 * options.value.scale
        ) {
            completePolygon();
        }
    }

    function completePolygon() {
        if (
            state.value.currentPolygonPoints.length < 3 ||
            state.value.currentTool !== 'polygon'
        ) {
            return;
        }

        const newPolygon: Polygon = {
            points: deepClone(state.value.currentPolygonPoints),
            id: generateId(),
        };

        // Replace existing polygon (only one allowed)
        state.value.polygons = [newPolygon];
        state.value.currentPolygonPoints = [];
        state.value.currentTool = 'none';
    }

    function startGreenLine(x: number, y: number) {
        pushHistory();
        state.value.currentTool = 'greenLine';
        state.value.currentGreenLinePoints = [{ x, y, id: generateId() }];
    }

    function addGreenLinePoint(x: number, y: number) {
        if (
            state.value.currentTool !== 'greenLine' ||
            state.value.currentGreenLinePoints.length >= 2
        ) {
            return;
        }

        const point: Point = { x, y, id: generateId() };
        state.value.currentGreenLinePoints.push(point);

        if (state.value.currentGreenLinePoints.length === 2) {
            completeGreenLine();
        }
    }

    function completeGreenLine() {
        if (
            state.value.currentGreenLinePoints.length !== 2 ||
            state.value.currentTool !== 'greenLine'
        ) {
            return;
        }

        const newLine: GreenLine = {
            start: deepClone(state.value.currentGreenLinePoints[0]),
            end: deepClone(state.value.currentGreenLinePoints[1]),
            id: generateId(),
        };

        // Replace existing green line (only one allowed)
        state.value.greenLines = [newLine];
        state.value.currentGreenLinePoints = [];
        state.value.currentTool = 'none';
    }

    function cancelCurrentDrawing() {
        state.value.currentPolygonPoints = [];
        state.value.currentGreenLinePoints = [];
        state.value.currentTool = 'none';
    }

    /**
     * If we are mid-drawing, undo removes the last added point.
     * If no drawing in progress, undo restores the previous snapshot.
     */
    function undo() {
        // Mid-drawing undo (remove last point)
        if (state.value.currentTool === 'polygon' && state.value.currentPolygonPoints.length > 0) {
            state.value.currentPolygonPoints.pop();
            if (state.value.currentPolygonPoints.length === 0) {
                state.value.currentTool = 'none';
                // Restore previous polygons
                const prev = state.value.history.pop();
                if (prev) {
                    state.value.redoStack.push(prev);
                    state.value.polygons = deepClone(prev.polygons);
                    state.value.greenLines = deepClone(prev.greenLines);
                }
            }
            return;
        }

        if (state.value.currentTool === 'greenLine' && state.value.currentGreenLinePoints.length > 0) {
            state.value.currentGreenLinePoints.pop();
            if (state.value.currentGreenLinePoints.length === 0) {
                state.value.currentTool = 'none';
            }
            return;
        }

        // Full history undo
        if (state.value.history.length === 0) return;

        const prev = state.value.history.pop();
        state.value.redoStack.push(snapshot());
        state.value.polygons = deepClone(prev.polygons);
        state.value.greenLines = deepClone(prev.greenLines);
        state.value.currentPolygonPoints = [];
        state.value.currentGreenLinePoints = [];
        state.value.currentTool = 'none';
    }

    function redo() {
        if (state.value.redoStack.length === 0) return;

        const next = state.value.redoStack.pop();
        state.value.history.push(snapshot());
        state.value.polygons = deepClone(next.polygons);
        state.value.greenLines = deepClone(next.greenLines);
    }

    function reset() {
        if (
            state.value.polygons.length === 0 &&
            state.value.greenLines.length === 0 &&
            state.value.currentPolygonPoints.length === 0 &&
            state.value.currentGreenLinePoints.length === 0
        ) {
            return;
        }

        pushHistory();
        state.value.polygons = [];
        state.value.greenLines = [];
        state.value.currentPolygonPoints = [];
        state.value.currentGreenLinePoints = [];
        state.value.currentTool = 'none';
    }

    function load(config: { green_line?: number[][][]; red_zone_polygons?: number[][][] }) {
        pushHistory();

        // Load green lines
        state.value.greenLines = [];
        if (config.green_line) {
            for (const [i, line] of config.green_line.entries()) {
                if (line.length === 2) {
                    state.value.greenLines.push({
                        start: { x: line[0][0], y: line[0][1], id: generateId() },
                        end: { x: line[1][0], y: line[1][1], id: generateId() },
                        id: generateId(),
                    });
                }
            }
        }

        // Load polygons
        state.value.polygons = [];
        if (config.red_zone_polygons) {
            for (const polygon of config.red_zone_polygons) {
                state.value.polygons.push({
                    points: polygon.map(p => ({
                        x: p[0],
                        y: p[1],
                        id: generateId(),
                    })),
                    id: generateId(),
                });
            }
        }

        state.value.currentPolygonPoints = [];
        state.value.currentGreenLinePoints = [];
        state.value.currentTool = 'none';
    }

    return {
        state: computed(() => state.value),
        startPolygon,
        addPolygonPoint,
        completePolygon,
        startGreenLine,
        addGreenLinePoint,
        completeGreenLine,
        cancelCurrentDrawing,
        undo,
        redo,
        reset,
        load,
    };
}