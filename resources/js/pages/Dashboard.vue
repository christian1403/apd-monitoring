<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import ApexChart from 'vue3-apexcharts';
import { computed } from 'vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    stats: {
        items: number;
        locations: number;
        cameras: number;
        detections: number;
    };

    chartData: {
        safe: number;
        violation: number;
    };

    latestDetections: any[];
}>();

const chartSeries = computed(() => [
    {
        name: 'Detections',
        data: [props.chartData.safe, props.chartData.violation],
    },
]);

// Konfigurasi chart disesuaikan dengan token warna tema gelap Shadcn (slate-950/900)
const chartOptions = {
    chart: {
        type: 'bar',
        toolbar: {
            show: false,
        },
        fontFamily: 'inherit',
        background: 'transparent',
    },
    theme: {
        mode: 'dark',
    },
    // Menggunakan CSS Variable atau hex yang matching dengan Radix/Shadcn emerald & destructive
    colors: ['#10b981', '#ef4444'],
    plotOptions: {
        bar: {
            horizontal: false,
            borderRadius: 4,
            columnWidth: '45%',
            distributed: true,
        },
    },
    dataLabels: {
        enabled: true,
        style: {
            colors: ['#fff'],
            fontSize: '12px',
            fontWeight: '600',
        },
        offsetY: -6,
    },
    xaxis: {
        categories: ['Safe', 'Violation'],
        labels: {
            style: {
                colors: '#a1a1aa', // muted-foreground
                fontSize: '12px',
            },
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: {
                colors: '#71717a',
            },
        },
    },
    grid: {
        borderColor: '#27272a', // border-input / border-muted
        strokeDashArray: 4,
    },
    legend: {
        show: false,
    },
    tooltip: {
        theme: 'dark',
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Overview of system detection and statistics
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="bg-card text-card-foreground">
                <CardContent class="p-6">
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Total Item
                    </p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight">
                        {{ stats.items }}
                    </h2>
                </CardContent>
            </Card>

            <Card class="bg-card text-card-foreground">
                <CardContent class="p-6">
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Total Lokasi
                    </p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight">
                        {{ stats.locations }}
                    </h2>
                </CardContent>
            </Card>

            <Card class="bg-card text-card-foreground">
                <CardContent class="p-6">
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Total Kamera
                    </p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight">
                        {{ stats.cameras }}
                    </h2>
                </CardContent>
            </Card>

            <Card class="bg-card text-card-foreground">
                <CardContent class="p-6">
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        Total Detection
                    </p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight">
                        {{ stats.detections }}
                    </h2>
                </CardContent>
            </Card>
        </div>

        <Card class="bg-card text-card-foreground">
            <CardHeader
                class="flex flex-col gap-4 border-b border-border pb-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <CardTitle class="text-lg font-semibold tracking-tight"
                        >Statistik APD</CardTitle
                    >
                    <CardDescription
                        >Visual comparison of safety standard
                        compliance</CardDescription
                    >
                </div>

                <div class="flex items-center gap-2 text-xs font-medium">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-md border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-emerald-500"
                    >
                        Safe: {{ chartData.safe }}
                    </span>
                    <span
                        class="inline-flex items-center gap-1.5 rounded-md border border-destructive/20 bg-destructive/10 px-2.5 py-1 text-destructive"
                    >
                        Violation: {{ chartData.violation }}
                    </span>
                </div>
            </CardHeader>
            <CardContent class="p-6">
                <div class="h-[320px] w-full">
                    <ApexChart
                        type="bar"
                        height="100%"
                        :options="chartOptions"
                        :series="chartSeries"
                    />
                </div>
            </CardContent>
        </Card>

        <Card class="overflow-hidden bg-card text-card-foreground">
            <CardHeader class="border-b border-border">
                <CardTitle class="text-lg font-semibold tracking-tight"
                    >Detection Terbaru</CardTitle
                >
                <CardDescription
                    >Real-time updates from live camera streams</CardDescription
                >
            </CardHeader>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-border bg-muted/40 text-xs font-medium tracking-wider text-muted-foreground uppercase"
                        >
                            <th class="p-4 pl-6">Item</th>
                            <th class="p-4">Kamera</th>
                            <th class="p-4">Lokasi</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 pr-6">Waktu</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border text-sm">
                        <tr
                            v-for="detection in latestDetections"
                            :key="detection.id"
                            class="group transition-colors hover:bg-muted/40"
                        >
                            <td class="p-4 pl-6 font-medium text-foreground">
                                {{ detection.item?.name || '—' }}
                            </td>

                            <td class="p-4 text-muted-foreground">
                                {{ detection.camera?.name || '—' }}
                            </td>

                            <td class="p-4 text-muted-foreground">
                                {{ detection.location?.name || '—' }}
                            </td>

                            <td class="p-4">
                                <Badge
                                    :variant="
                                        detection.status?.toLowerCase() ===
                                        'safe'
                                            ? 'default'
                                            : 'destructive'
                                    "
                                    class="capitalize"
                                >
                                    {{ detection.status }}
                                </Badge>
                            </td>

                            <td
                                class="p-4 pr-6 font-mono text-xs text-muted-foreground"
                            >
                                {{ detection.detected_at }}
                            </td>
                        </tr>

                        <tr
                            v-if="
                                !latestDetections ||
                                latestDetections.length === 0
                            "
                        >
                            <td
                                colspan="5"
                                class="p-8 text-center text-muted-foreground"
                            >
                                No recent detections recorded.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>
