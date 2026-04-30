<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import Switch from "../components/ui/Switch.vue";

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const logoUrl = "/images/axiomatic_logowhite-300x158.png";
const logoAlt = "axiomatic logo reversed";
const isMenuOpen = ref(false);
const isDarkMode = ref(false);
const menuRef = ref(null);
const toasts = ref([]);

function pushToast(type, message) {
    if (!message) return;

    const id = `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    toasts.value.push({ id, type, message });

    setTimeout(() => {
        removeToast(id);
    }, 3500);
}

function removeToast(id) {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

function applyTheme() {
    document.documentElement.classList.toggle("dark", isDarkMode.value);
    localStorage.setItem("theme", isDarkMode.value ? "dark" : "light");
}

function toggleMenu() {
    isMenuOpen.value = !isMenuOpen.value;
}

function handleOutsideClick(event) {
    if (menuRef.value && !menuRef.value.contains(event.target)) {
        isMenuOpen.value = false;
    }
}

function toggleTheme() {
    isDarkMode.value = !isDarkMode.value;
    applyTheme();
}

onMounted(() => {
    isDarkMode.value = localStorage.getItem("theme") === "dark";
    applyTheme();
    document.addEventListener("click", handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleOutsideClick);
});

watch(
    flash,
    (value) => {
        if (value?.success) pushToast("success", value.success);
        if (value?.error) pushToast("error", value.error);
        if (value?.info) pushToast("info", value.info);
    },
    { immediate: true }
);

const toastClassMap = {
    success:
        "border-green-300 bg-green-50 text-green-900 dark:border-green-800 dark:bg-green-950/80 dark:text-green-200",
    error:
        "border-red-300 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-950/80 dark:text-red-200",
    info: "border-blue-300 bg-blue-50 text-blue-900 dark:border-blue-800 dark:bg-blue-950/80 dark:text-blue-200",
};

function toastClasses(type) {
    return toastClassMap[type] ?? toastClassMap.info;
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-slate-950">
        <nav
            class="bg-white dark:bg-slate-900 flex justify-between items-center shadow"
        >
            <div
                class="relative overflow-hidden bg-[#CE2031] h-16 w-[330px] flex items-center px-6 rounded-br-[110px]"
            >
                <img
                    width="300"
                    height="100"
                    :src="logoUrl"
                    class="relative z-10 mx-auto w-full max-w-[200px] mb-10"
                    :alt="logoAlt"
                    :srcset="`${logoUrl} 300w`"
                />
            </div>
            <div
                class="flex w-full px-4 sm:px-6 lg:px-8 items-center justify-between h-16"
            >
                <p class="text-xl text-gray-600 dark:text-white">
                    Commission Notes App
                </p>
                <div class="flex justify-between gap-5">
                    <Link
                        :href="route('dashboard')"
                        class="font-semibold text-gray-800 dark:text-gray-100"
                        >Dashboard</Link
                    >
                    <Link
                        :href="route('companies.index')"
                        class="font-semibold text-gray-800 dark:text-gray-100"
                        >Companies</Link
                    >
                    <Link
                        :href="route('commission-notes.index')"
                        class="font-semibold text-gray-800 dark:text-gray-100"
                        >Commission Notes</Link
                    >
                    <Link
                        :href="route('employees.index')"
                        class="font-semibold text-gray-800 dark:text-gray-100"
                        >Employees</Link
                    >
                </div>

                <div ref="menuRef" class="relative">
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-md border border-gray-300 dark:border-slate-700 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-slate-800"
                        @click.stop="toggleMenu"
                    >
                        <span>{{ user?.name }}</span>
                        <span aria-hidden="true">▾</span>
                    </button>

                    <div
                        v-if="isMenuOpen"
                        class="absolute right-0 mt-2 w-56 rounded-md border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg py-2 z-30"
                    >
                        <div class="flex justify-between items-center p-2">
                            <span
                                class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                >{{
                                isDarkMode
                                    ? "Dark mode"
                                    : "Light mode"
                                }}</span
                            >

                            <Switch
                                :model-value="isDarkMode"
                                @update:model-value="toggleTheme"
                            />

                        </div>
                        
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="w-full px-2 py-2 text-left text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40"
                        >
                            Logout
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="pointer-events-none fixed right-4 top-4 z-[70] flex w-full max-w-sm flex-col gap-2">
            <TransitionGroup
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-2"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto rounded-lg border px-4 py-3 shadow-lg"
                    :class="toastClasses(toast.type)"
                >
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-medium">{{ toast.message }}</p>
                        <button
                            type="button"
                            class="text-xs opacity-70 hover:opacity-100"
                            @click="removeToast(toast.id)"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>
