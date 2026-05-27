export type PaginatedMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

export type PaginatedData<T> = PaginatedMeta & {
    data: T[];
};

export type DataTableFilters = {
    search: string;
    sort_by: string;
    sort_dir: 'asc' | 'desc';
    per_page: number;
};
