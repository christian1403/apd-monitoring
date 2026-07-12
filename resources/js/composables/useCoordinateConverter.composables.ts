import { ref, computed } from 'vue';

export interface CoordinateConverterOptions {
    originalImageWidth: number;
    originalImageHeight: number;
    scale: number;
    offsetX: number;
    offsetY: number;
}

export interface ConvertedCoordinates {
    screenX: number;
    screenY: number;
    imageX: number;
    imageY: number;
}

export function useCoordinateConverter(initialOptions: CoordinateConverterOptions) {
    const options = ref<CoordinateConverterOptions>({ ...initialOptions });
    const isDirty = ref(false);

    const screenToImage = (screenX: number, screenY: number): ConvertedCoordinates => {
        const imageX = (screenX - options.value.offsetX) / options.value.scale;
        const imageY = (screenY - options.value.offsetY) / options.value.scale;
        return { screenX, screenY, imageX, imageY };
    };

    const imageToScreen = (imageX: number, imageY: number): ConvertedCoordinates => {
        const screenX = imageX * options.value.scale + options.value.offsetX;
        const screenY = imageY * options.value.scale + options.value.offsetY;
        return { screenX, screenY, imageX, imageY };
    };

    const clampImageCoordinates = (x: number, y: number): { x: number; y: number } => {
        return {
            x: Math.max(0, Math.min(x, options.value.originalImageWidth - 1)),
            y: Math.max(0, Math.min(y, options.value.originalImageHeight - 1)),
        };
    };

    const update = (newOptions: Partial<CoordinateConverterOptions>) => {
        options.value = { ...options.value, ...newOptions };
        isDirty.value = true;
    };

    const reset = () => {
        options.value = { ...initialOptions };
        isDirty.value = false;
    };

    return {
        options: computed(() => options.value),
        isDirty: computed(() => isDirty.value),
        screenToImage,
        imageToScreen,
        clampImageCoordinates,
        update,
        reset,
    };
}