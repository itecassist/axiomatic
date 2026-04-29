<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, onMounted, onUnmounted, ref } from "vue";
import Switch from "../components/ui/Switch.vue";
import { Sun, Moon } from "lucide-vue-next";

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash);
const logoUrl = "/images/axiomatic_logowhite-300x158.png";
const logoAlt = "axiomatic logo reversed";
const isMenuOpen = ref(false);
const isDarkMode = ref(false);
const isSideMenu = ref(false);
const menuRef = ref(null);

function applyTheme() {
    document.documentElement.classList.toggle("dark", isDarkMode.value);
    localStorage.setItem("theme", isDarkMode.value ? "dark" : "light");
}

function applyMenuLayout() {
    document.body.dataset.menuLayout = isSideMenu.value ? "side" : "top";
    localStorage.setItem("menuLayout", isSideMenu.value ? "side" : "top");
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

function toggleMenuLayout() {
    isSideMenu.value = !isSideMenu.value;
    applyMenuLayout();
}

onMounted(() => {
    isDarkMode.value = localStorage.getItem("theme") === "dark";
    isSideMenu.value = localStorage.getItem("menuLayout") === "side";
    applyTheme();
    applyMenuLayout();
    document.addEventListener("click", handleOutsideClick);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleOutsideClick);
});

onUnmounted(() => {
    setTimeout(() => {
        if (flash.value.success) {
            page.props.flash.success = null;
        }
    }, 3000);
});
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
                            <Sun class="w-4 h-4" />
                            <Switch
                                :model-value="isDarkMode"
                                @update:model-value="toggleTheme"
                            />
                            <Moon class="w-4 h-4" />
                        </div>
                        <div class="flex justify-between items-center p-2">
                            <span
                                class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                >{{
                                isSideMenu
                                    ? "Switch to top menu"
                                    : "Switch to side menu"
                            }}</span
                            >
                            <Switch
                                :model-value="isSideMenu"
                                @update:model-value="toggleMenuLayout"
                            />
                        </div>
                        <Link
                            :href="route('dashboard')"
                            class="block px-2 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-800"
                        >
                            Profile
                        </Link>

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

        <div
            v-if="flash.success"
            class="max-w-7xl mx-auto mt-4 px-4 sm:px-6 lg:px-8"
        >
            <div
                class="bg-green-100 border border-green-300 text-green-800 rounded px-4 py-2 text-sm"
            >
                {{ flash.success }}
            </div>
        </div>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>
