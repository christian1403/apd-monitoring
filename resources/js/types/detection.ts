import type { DataTableFilters } from './pagination';
import type { Item } from './item';
import type { Camera } from './camera';
import type { Location } from './location';

export type Detection = {
    id: number;
    item_id: number | null;
    camera_id: number | null;
    location_id: number | null;
    status: 'safe' | 'warning' | 'unsafe';
    image: string | null;
    image_url: string | null;
    detected_at: string | null;
    item: Pick<Item, 'id' | 'name'> | null;
    camera: Pick<Camera, 'id' | 'name' | 'ip_address'> | null;
    location: Pick<Location, 'id' | 'name'> | null;
    created_at: string;
    updated_at: string;
};

export type DetectionFilters = DataTableFilters & {
    status: 'all' | 'safe' | 'warning' | 'unsafe';
};
