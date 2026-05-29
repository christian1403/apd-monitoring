<script setup lang="ts" generic="TData">
import { ref, watch, computed } from 'vue';
import { useDebounceFn } from '@vueuse/core';
import type { ColumnDef, SortingState } from '@tanstack/vue-table';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { ChevronDown, Download, FileSpreadsheet, FileText, Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import DataTablePagination from './DataTablePagination.vue';
import type { DataTableFilters, PaginatedMeta } from '@/types/pagination';

const props = withDefaults(
    defineProps<{
        columns: ColumnDef<TData, any>[];
        data: TData[];
        meta: PaginatedMeta;
        filters: DataTableFilters;
        searchPlaceholder?: string;
        exportFormats?: ('xlsx' | 'csv')[];
        exportBaseUrl?: string;
    }>(),
    { searchPlaceholder: 'Search...' },
);

const exportFormatLabels: Record<'xlsx' | 'csv', { label: string; icon: typeof FileSpreadsheet }> = {
    xlsx: { label: 'Excel (.xlsx)', icon: FileSpreadsheet },
    csv:  { label: 'CSV (.csv)',    icon: FileText },
};

function handleExport(format: 'xlsx' | 'csv') {
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(props.filters)) {
        if (value != null && value !== '' && value !== 'all' && key !== 'per_page') {
            params.set(key, String(value));
        }
    }
    const qs = params.toString();
    window.location.href = `${props.exportBaseUrl}/${format}${qs ? '?' + qs : ''}`;
}

const emit = defineEmits<{
    (e: 'filter-change', value: Partial<DataTableFilters & { page: number }>): void;
}>();

// ─── Local search with debounce ───────────────────────────────────────────────

const searchValue = ref(props.filters.search ?? '');

watch(
    () => props.filters.search,
    (val) => {
        searchValue.value = val ?? '';
    },
);

const debouncedEmitSearch = useDebounceFn((val: string) => {
    emit('filter-change', { search: val, page: 1 });
}, 350);

watch(searchValue, (val) => debouncedEmitSearch(val));

// ─── TanStack Table (manual server-side mode) ─────────────────────────────────

const sorting = computed<SortingState>(() =>
    props.filters.sort_by
        ? [{ id: props.filters.sort_by, desc: props.filters.sort_dir === 'desc' }]
        : [],
);

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    get rowCount() {
        return props.meta.total;
    },
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    manualSorting: true,
    manualFiltering: true,
    state: {
        get pagination() {
            return {
                pageIndex: (props.meta.current_page ?? 1) - 1,
                pageSize: props.meta.per_page ?? 10,
            };
        },
        get sorting() {
            return sorting.value;
        },
    },
    onSortingChange: (updaterOrValue) => {
        const next =
            typeof updaterOrValue === 'function'
                ? updaterOrValue(sorting.value)
                : updaterOrValue;
        if (next.length > 0) {
            emit('filter-change', {
                sort_by: next[0].id,
                sort_dir: next[0].desc ? 'desc' : 'asc',
                page: 1,
            });
        } else {
            emit('filter-change', { sort_by: '', sort_dir: 'asc', page: 1 });
        }
    },
});
</script>

<template>
    <div class="space-y-4">
        <!-- Toolbar -->
        <div class="flex items-center gap-2">
            <div class="relative max-w-xs flex-1">
                <Search class="absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="searchValue" :placeholder="searchPlaceholder" class="pl-9" />
            </div>
            <slot name="toolbar" />
            <DropdownMenu v-if="exportFormats?.length && exportBaseUrl">
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="sm" class="ml-auto">
                        <Download class="size-4" />
                        Export
                        <ChevronDown class="size-4" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem
                        v-for="format in exportFormats"
                        :key="format"
                        class="cursor-pointer"
                        @click="handleExport(format)"
                    >
                        <component :is="exportFormatLabels[format].icon" class="size-4" />
                        {{ exportFormatLabels[format].label }}
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>

        <!-- Table -->
        <div class="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow
                        v-for="headerGroup in table.getHeaderGroups()"
                        :key="headerGroup.id"
                    >
                        <TableHead
                            v-for="header in headerGroup.headers"
                            :key="header.id"
                        >
                            <FlexRender
                                v-if="!header.isPlaceholder"
                                :render="header.column.columnDef.header"
                                :props="header.getContext()"
                            />
                        </TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <template v-if="table.getRowModel().rows.length">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell
                            :colspan="columns.length"
                            class="h-24 text-center text-muted-foreground"
                        >
                            No results found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <DataTablePagination
            :meta="meta"
            @page-change="(page) => emit('filter-change', { page })"
            @per-page-change="(perPage) => emit('filter-change', { per_page: perPage, page: 1 })"
        />
    </div>
</template>
