import type { DataTableFilters } from './pagination';

export type Location = {
    id: number;
    name: string;
    description: string | null;
    address: string | null;
    latitude: string | null;
    longitude: string | null;
    created_at: string;
    updated_at: string;
};

export type LocationFilters = DataTableFilters & {
    status: 'all' | 'active' | 'inactive';
};
