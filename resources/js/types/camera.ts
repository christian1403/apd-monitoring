import type { DataTableFilters } from './pagination';
import type { Location } from './location';

export type CameraStatus = 'active' | 'inactive' | 'maintenance';

export type Camera = {
    id: number;
    name: string;
    ip_address: string;
    status: CameraStatus;
    image: string | null;
    image_url: string | null;
    location_id: number | null;
    location: Location | null;
    created_at: string;
    updated_at: string;
};

export type CameraFilters = DataTableFilters & {
    status: 'all' | CameraStatus;
};