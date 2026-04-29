<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
import { route as ziggyRoute } from "ziggy-js";
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    type ChartConfiguration,
} from "chart.js";

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
);

const props = defineProps<{
    can: {
        viewCommissionNotes: boolean;
        manageCommissionNotes: boolean;
    };
    charts: {
        branchEmployees: Array<{ label: string; value: number }>;
        branchCommissionNotes: Array<{ label: string; value: number }>;
        branchCommissionAmounts: Array<{ label: string; value: number }>;
        employeeCommissionAmountsByBranch: Array<{
            branch: string;
            employees: Array<{ label: string; value: number }>;
        }>;
    };
}>();

const maxEmployees = computed(() =>
    Math.max(...props.charts.branchEmployees.map((i) => i.value), 1),
);

const currencyFormatter = new Intl.NumberFormat("en-ZA", {
    style: "currency",
    currency: "ZAR",
    minimumFractionDigits: 2,
});

function formatCurrency(amount: number): string {
    return currencyFormatter.format(Number(amount || 0));
}

function getBranchPerformance(branchName: string) {
    const branch = props.charts.employeeCommissionAmountsByBranch.find((item) =>
        item.branch.toLowerCase().includes(branchName.toLowerCase()),
    );

    if (!branch || branch.employees.length === 0) {
        return {
            branchLabel: branchName,
            best: null as { label: string; value: number } | null,
            worst: null as { label: string; value: number } | null,
        };
    }

    const sorted = [...branch.employees].sort((a, b) => b.value - a.value);

    return {
        branchLabel: branch.branch,
        best: sorted[0] ?? null,
        worst: sorted[sorted.length - 1] ?? null,
    };
}

const bellvillePerformance = computed(() => getBranchPerformance("Bellville"));
const centuryCityPerformance = computed(() => getBranchPerformance("Century City"));

const page = usePage();
const branchCommissionTotalsCanvas = ref<HTMLCanvasElement | null>(null);
const commissionAmountsCanvases = ref<Record<string, HTMLCanvasElement | null>>(
    {},
);
let branchCommissionTotalsChart: Chart<"bar"> | null = null;
const commissionAmountsCharts = new Map<string, Chart<"bar">>();
const chartPalette = [
    "#dc2626",
    "#7E858B",
    "#7c3aed",
    "#ea580c",
    "#0891b2",
    "#65a30d",
    "#0f766e",
    "#2563eb",
];

function route(name: string, params: any = undefined, absolute = true): string {
    return ziggyRoute(
        name,
        params,
        absolute,
        page.props.ziggy as any,
    ) as string;
}

function barWidth(value: number, max: number) {
    return `${Math.max((value / max) * 100, 6)}%`;
}

function setCommissionAmountsCanvas(
    branch: string,
    element: HTMLCanvasElement | null,
) {
    commissionAmountsCanvases.value[branch] = element;
}

function getChartColor(index: number): string {
    return chartPalette[index % chartPalette.length];
}

function buildBranchCommissionTotalsChart() {
    if (!branchCommissionTotalsCanvas.value) {
        return;
    }

    branchCommissionTotalsChart?.destroy();

    const textColor = document.documentElement.classList.contains("dark")
        ? "#cbd5e1"
        : "#475569";
    const gridColor = document.documentElement.classList.contains("dark")
        ? "rgba(148, 163, 184, 0.18)"
        : "rgba(148, 163, 184, 0.2)";
    const currencyFormatter = new Intl.NumberFormat("en-ZA", {
        style: "currency",
        currency: "ZAR",
        minimumFractionDigits: 2,
    });

    const config: ChartConfiguration<"bar"> = {
        type: "bar",
        data: {
            labels: props.charts.branchCommissionAmounts.map(
                (item) => item.label,
            ),
            datasets: [
                {
                    label: "Commission total",
                    data: props.charts.branchCommissionAmounts.map(
                        (item) => item.value,
                    ),
                    backgroundColor: props.charts.branchCommissionAmounts.map(
                        (_, index) => getChartColor(index),
                    ),
                    borderRadius: 8,
                    maxBarThickness: 48,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: (context) =>
                            `Total: ${currencyFormatter.format(Number(context.parsed.y ?? 0))}`,
                    },
                },
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { display: false },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: textColor,
                        callback: (value) =>
                            currencyFormatter.format(Number(value)),
                    },
                    grid: { color: gridColor },
                },
            },
        },
    };

    branchCommissionTotalsChart = new Chart(
        branchCommissionTotalsCanvas.value,
        config,
    );
}

function buildCommissionAmountsCharts() {
    const textColor = document.documentElement.classList.contains("dark")
        ? "#cbd5e1"
        : "#475569";
    const gridColor = document.documentElement.classList.contains("dark")
        ? "rgba(148, 163, 184, 0.18)"
        : "rgba(148, 163, 184, 0.2)";
    const currencyFormatter = new Intl.NumberFormat("en-ZA", {
        style: "currency",
        currency: "ZAR",
        minimumFractionDigits: 2,
    });

    commissionAmountsCharts.forEach((chart) => chart.destroy());
    commissionAmountsCharts.clear();

    props.charts.employeeCommissionAmountsByBranch.forEach(
        (branchChart, index) => {
            const canvas = commissionAmountsCanvases.value[branchChart.branch];

            if (!canvas) {
                return;
            }

            const config: ChartConfiguration<"bar"> = {
                type: "bar",
                data: {
                    labels: branchChart.employees.map((item) => item.label),
                    datasets: [
                        {
                            label: `${branchChart.branch} commission total`,
                            data: branchChart.employees.map(
                                (item) => item.value,
                            ),
                            backgroundColor: getChartColor(index),
                            borderRadius: 8,
                            maxBarThickness: 48,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) =>
                                    `Total: ${currencyFormatter.format(Number(context.parsed.y ?? 0))}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            ticks: { color: textColor },
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                callback: (value) =>
                                    currencyFormatter.format(Number(value)),
                            },
                            grid: { color: gridColor },
                        },
                    },
                },
            };

            commissionAmountsCharts.set(
                branchChart.branch,
                new Chart(canvas, config),
            );
        },
    );
}

onMounted(() => {
    buildBranchCommissionTotalsChart();
    buildCommissionAmountsCharts();
});

watch(
    () => props.charts.branchCommissionAmounts,
    buildBranchCommissionTotalsChart,
    { deep: true },
);
watch(
    () => props.charts.employeeCommissionAmountsByBranch,
    buildCommissionAmountsCharts,
    { deep: true },
);

onBeforeUnmount(() => {
    branchCommissionTotalsChart?.destroy();
    commissionAmountsCharts.forEach((chart) => chart.destroy());
    commissionAmountsCharts.clear();
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <div class="space-y-6">
            <header>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Dashboard
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Welcome back. Choose what you want to do next.
                </p>
            </header>
            <div class="grid gap-6 md:grid-cols-3 lg:grid-cols-3">
                <section
                    class="rounded-lg border border-gray-200 bg-white dark:bg-slate-800 p-5 shadow-sm "
                >
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                    >
                        Commission Amount per Branch
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Total commission note amount grouped by branch.
                    </p>

                    <div class="mt-4 h-80">
                        <canvas ref="branchCommissionTotalsCanvas" />
                    </div>
                </section>
                <section>
                    <!-- Bellville best and worst performer -->
                    <div
                        class="rounded-lg border border-gray-200 bg-white dark:bg-slate-800 p-5 shadow-sm h-full"
                    >
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ bellvillePerformance.branchLabel }} Performers
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Best and worst commission performers.
                        </p>

                        <div v-if="bellvillePerformance.best" class="mt-4 space-y-3">
                            <div class="rounded-md border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-900 p-3">
                                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Best performer</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ bellvillePerformance.best?.label }}</p>
                                <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ formatCurrency(bellvillePerformance.best?.value ?? 0) }}</p>
                            </div>

                            <div class="rounded-md border border-rose-200 bg-rose-50 dark:bg-rose-900/20 dark:border-rose-900 p-3">
                                <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">Worst performer</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ bellvillePerformance.worst?.label }}</p>
                                <p class="text-sm text-rose-700 dark:text-rose-300">{{ formatCurrency(bellvillePerformance.worst?.value ?? 0) }}</p>
                            </div>
                        </div>

                        <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">No performer data available.</p>
                    </div>
                </section>
                <section>
                    <!-- Century City best and worst performer -->
                    <div
                        class="rounded-lg border border-gray-200 bg-white dark:bg-slate-800 p-5 shadow-sm h-full"
                    >
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ centuryCityPerformance.branchLabel }} Performers
                        </h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Best and worst commission performers.
                        </p>

                        <div v-if="centuryCityPerformance.best" class="mt-4 space-y-3">
                            <div class="rounded-md border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-900 p-3">
                                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Best performer</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ centuryCityPerformance.best?.label }}</p>
                                <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ formatCurrency(centuryCityPerformance.best?.value ?? 0) }}</p>
                            </div>

                            <div class="rounded-md border border-rose-200 bg-rose-50 dark:bg-rose-900/20 dark:border-rose-900 p-3">
                                <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">Worst performer</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ centuryCityPerformance.worst?.label }}</p>
                                <p class="text-sm text-rose-700 dark:text-rose-300">{{ formatCurrency(centuryCityPerformance.worst?.value ?? 0) }}</p>
                            </div>
                        </div>

                        <p v-else class="mt-4 text-sm text-gray-500 dark:text-gray-400">No performer data available.</p>
                    </div>
                </section>
            </div>

            <section
                class="rounded-lg border w-full border-gray-200 bg-white dark:bg-slate-800 p-5 shadow-sm"
            >
                <h2
                    class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                >
                    Employee Commission Amounts by Branch
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Separate employee commission amount charts for each branch.
                </p>

                <div class="mt-4 grid gap-4">
                    <div
                        v-for="branchChart in charts.employeeCommissionAmountsByBranch"
                        :key="branchChart.branch"
                        class="rounded-lg border border-gray-200 dark:border-slate-700 p-4"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <h3
                                class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                            >
                                {{ branchChart.branch }}
                            </h3>
                            <span
                                class="text-xs text-gray-500 dark:text-gray-400"
                                >{{
                                    branchChart.employees.length
                                }}
                                employees</span
                            >
                        </div>
                        <div class="h-72">
                            <canvas
                                :ref="
                                    (element) =>
                                        setCommissionAmountsCanvas(
                                            branchChart.branch,
                                            element as HTMLCanvasElement | null,
                                        )
                                "
                            />
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
