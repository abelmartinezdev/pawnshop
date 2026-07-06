<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    canResetPassword: {
        type: Boolean,
        default: false,
    },
    status: {
        type: String,
        default: null,
    },
})

const showPassword = ref(false)

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const appName = computed(() => {
    return import.meta.env.VITE_APP_NAME || 'SICEM'
})

const submit = () => {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Iniciar sesión" />

    <div class="relative min-h-screen overflow-hidden bg-[#0f172a]">
        <!-- Fondo decorativo -->
        <div class="absolute inset-0">
            <div class="absolute -left-32 -top-32 h-96 w-96 rounded-full bg-amber-400/20 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-32 h-[34rem] w-[34rem] rounded-full bg-yellow-700/25 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/2 h-96 w-96 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/5 blur-3xl"></div>

            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.14),transparent_28%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.08),transparent_28%)]"></div>

            <div class="absolute inset-0 opacity-[0.04]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;"></div>
        </div>

        <div class="relative grid min-h-screen lg:grid-cols-[1.15fr_0.85fr]">
            <!-- Panel izquierdo -->
            <section class="hidden flex-col justify-between p-10 text-white lg:flex xl:p-14">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-400 text-slate-950 shadow-xl shadow-amber-500/25">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div>
                        <p class="text-2xl font-black tracking-tight">
                            {{ appName }}
                        </p>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-200/80">
                            Control prendario
                        </p>
                    </div>
                </div>

                <div class="max-w-2xl">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-amber-100 backdrop-blur">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        Plataforma administrativa
                    </div>

                    <h1 class="text-5xl font-black leading-tight tracking-tight xl:text-6xl">
                        Administra empeños, caja y sucursales con mayor control.
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-8 text-slate-300">
                        Accede al sistema, selecciona tu sucursal de trabajo y opera clientes,
                        empeños, pagos, cortes y movimientos de caja desde un solo lugar.
                    </p>

                    <div class="mt-10 grid max-w-2xl gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-amber-300">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 3 20 7v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                                    <path d="m9 12 2 2 4-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <p class="text-sm font-black">Acceso seguro</p>
                            <p class="mt-1 text-xs leading-5 text-slate-300">
                                Usuarios, roles y permisos.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-amber-300">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 21V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                    <path d="M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M9 21v-5h6v5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                                </svg>
                            </div>

                            <p class="text-sm font-black">Multi-sucursal</p>
                            <p class="mt-1 text-xs leading-5 text-slate-300">
                                Operación por oficina.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-amber-300">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 7h16M6 7V5.5A1.5 1.5 0 0 1 7.5 4h9A1.5 1.5 0 0 1 18 5.5V7M6 7l1.2 12.5A1.7 1.7 0 0 0 8.9 21h6.2a1.7 1.7 0 0 0 1.7-1.5L18 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M10 11v5M14 11v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>
                            </div>

                            <p class="text-sm font-black">Caja y cortes</p>
                            <p class="mt-1 text-xs leading-5 text-slate-300">
                                Movimientos trazables.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm text-slate-400">
                    <p>© {{ new Date().getFullYear() }} {{ appName }}.</p>
                    <p>Sistema de control de empeños</p>
                </div>
            </section>

            <!-- Panel derecho / Login -->
            <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
                <div class="w-full max-w-md">
                    <!-- Logo móvil -->
                    <div class="mb-8 flex justify-center lg:hidden">
                        <div class="flex items-center gap-4 text-white">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-400 text-slate-950 shadow-xl shadow-amber-500/25">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                    <path
                                        d="M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="M3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <div>
                                <p class="text-2xl font-black tracking-tight">
                                    {{ appName }}
                                </p>
                                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-200/80">
                                    Control prendario
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white shadow-2xl shadow-black/30">
                        <div class="border-b border-slate-100 px-7 py-6 sm:px-8">
                            <p class="text-sm font-black uppercase tracking-[0.25em] text-amber-600">
                                Bienvenido
                            </p>

                            <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                                Iniciar sesión
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Introduce tus datos de acceso para continuar.
                            </p>
                        </div>

                        <div class="px-7 py-7 sm:px-8">
                            <div
                                v-if="status"
                                class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"
                            >
                                {{ status }}
                            </div>

                            <form class="space-y-5" @submit.prevent="submit">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700">
                                        Correo electrónico
                                    </label>

                                    <div class="relative">
                                        <div class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v11A2.5 2.5 0 0 1 17.5 20h-11A2.5 2.5 0 0 1 4 17.5v-11Z" stroke="currentColor" stroke-width="1.8" />
                                                <path d="m5.5 7 5.2 4.1a2.1 2.1 0 0 0 2.6 0L18.5 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>

                                        <input
                                            v-model="form.email"
                                            type="email"
                                            autocomplete="username"
                                            autofocus
                                            class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100"
                                            placeholder="correo@empresa.com"
                                        />
                                    </div>

                                    <p v-if="form.errors.email" class="mt-2 text-sm font-semibold text-red-600">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700">
                                        Contraseña
                                    </label>

                                    <div class="relative">
                                        <div class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                <path d="M6.5 10h11A1.5 1.5 0 0 1 19 11.5v7A1.5 1.5 0 0 1 17.5 20h-11A1.5 1.5 0 0 1 5 18.5v-7A1.5 1.5 0 0 1 6.5 10Z" stroke="currentColor" stroke-width="1.8" />
                                            </svg>
                                        </div>

                                        <input
                                            v-model="form.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            autocomplete="current-password"
                                            class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-12 pr-12 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100"
                                            placeholder="••••••••"
                                        />

                                        <button
                                            type="button"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-slate-700"
                                            @click="showPassword = !showPassword"
                                        >
                                            <svg v-if="showPassword" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                <path d="M10.7 10.7A2 2 0 0 0 13.3 13.3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                                <path d="M8.5 5.8A9.4 9.4 0 0 1 12 5c5 0 8.5 4.5 9.5 7a12.4 12.4 0 0 1-2.2 3.4M6.1 6.8A12.4 12.4 0 0 0 2.5 12c1 2.5 4.5 7 9.5 7a9.7 9.7 0 0 0 5-1.4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>

                                            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8" />
                                            </svg>
                                        </button>
                                    </div>

                                    <p v-if="form.errors.password" class="mt-2 text-sm font-semibold text-red-600">
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <label class="flex cursor-pointer items-center gap-3 text-sm font-semibold text-slate-600">
                                        <input
                                            v-model="form.remember"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-400"
                                        />
                                        Recordarme
                                    </label>
                                </div>

                                <button
                                    type="submit"
                                    class="group flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 text-sm font-black text-white shadow-lg shadow-slate-950/20 transition hover:-translate-y-0.5 hover:bg-amber-500 hover:text-slate-950 disabled:cursor-not-allowed disabled:opacity-60"
                                    :disabled="form.processing"
                                >
                                    <svg class="h-5 w-5 transition group-hover:scale-110" viewBox="0 0 24 24" fill="none">
                                        <path d="M12 3 20 7v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                                        <path d="m9 12 2 2 4-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>

                                    {{ form.processing ? 'Entrando...' : 'Entrar al sistema' }}
                                </button>
                            </form>
                        </div>

                        <div class="border-t border-slate-100 bg-slate-50 px-7 py-4 text-center text-xs font-medium text-slate-500 sm:px-8">
                            Acceso exclusivo para personal autorizado.
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>