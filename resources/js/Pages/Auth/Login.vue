<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Auth } from '@/types/models'
import Button from '../../components/ui/button.vue'

const form = useForm<Auth>({
    email: '',
    password: '',
    remember: false,
})

const logoUrl = '/images/axiomatic_logowhite-300x158.png'
const logoAlt = 'axiomatic logo reversed'

function submit() {
    form.post('/login', { onFinish: () => form.reset('password') })
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 rounded-3xl shadow-2xl">
        <div class="w-full max-w-md bg-white rounded-lg shadow ">
            <div class="relative mb-6 overflow-hidden bg-[#CE2031] h-28 w-full flex items-center px-6 rounded-br-[110px]">
                <img
                    width="300"
                    height="128"
                    :src="logoUrl"
                    class="relative z-10 mx-auto w-full max-w-[260px] mb-10"
                    :alt="logoAlt"
                    :srcset="`${logoUrl} 300w`"
                />
            </div>
            <div class="p-8">


            <h1 class="text-2xl font-bold text-gray-800 mb-6">Sign in</h1>

            <form @submit.prevent="submit" class="space-y-4 ">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" v-model="form.remember" type="checkbox" class="rounded" />
                    <label for="remember" class="text-sm text-gray-600">Remember me</label>
                </div>
                <Button :disabled="form.processing" class="w-full" type="submit">Sign in</Button>

            </form>
            </div>
        </div>
    </div>
</template>
