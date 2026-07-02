import type { DataTableFilters } from './pagination';

export type Item = {
    id: number;
    name: string;
    code: string;
    description: string | null;
    image: string | null;
    image_url: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
};

export type ItemFilters = DataTableFilters & {
    status: 'all' | 'active' | 'inactive';
};