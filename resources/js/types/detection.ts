import type { Camera } from './camera';
import type { Item } from './item';
import type { Location } from './location';
import type { DataTableFilters } from './pagination';

export type DetectionStatus = 'safe' | 'warning' | 'unsafe';

export type DetectionItem = {
    id: number;
    item_id: number;
    detection_id: number;
    status: DetectionStatus;
    item: Pick<Item, 'id' | 'name'> | null;
};

export type Detection = {
    id: number;
    camera_id: number | null;
    location_id: number | null;
    status: DetectionStatus;
    image: string | null;
    image_url: string | null;
    detected_at: string | null;
    detection_items: DetectionItem[];
    camera: Pick<Camera, 'id' | 'name' | 'ip_address'> | null;
    location: Pick<Location, 'id' | 'name'> | null;
    created_at: string;
    updated_at: string;
};

export type DetectionFilters = DataTableFilters & {
    status: 'all' | DetectionStatus;
};
