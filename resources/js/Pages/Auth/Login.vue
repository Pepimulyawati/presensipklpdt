<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex bg-slate-50">
        <Head title="Log In - PORTAL PKL" />

        <div class="hidden md:flex md:w-1/2 bg-gradient-to-tr from-sky-600 to-indigo-700 p-12 flex-col justify-between text-white relative overflow-hidden">
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-sky-500 rounded-full opacity-20 blur-2xl"></div>
            <div class="absolute -bottom-20 right-0 w-96 h-96 bg-indigo-500 rounded-full opacity-20 blur-3xl"></div>

            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20">
                    <i class="pi pi-graduation-cap text-xl text-sky-200"></i>
                </div>
                <div>
                    <h2 class="font-black tracking-wide text-lg uppercase">SMK Singaparna</h2>
                    <p class="text-xs text-sky-200">Sistem Informasi Manajemen PKL</p>
                </div>
            </div>

            <div class="my-auto max-w-md relative z-10 space-y-4">
                <span class="px-3 py-1 bg-sky-500/30 text-sky-200 text-xs font-semibold rounded-full border border-sky-400/20 uppercase tracking-wider">
                    Selamat Datang
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight leading-tight">
                    PORTAL PKL <br><span class="text-sky-300">SMK SINGAPARNA</span>
                </h1>
                <p class="text-slate-200 text-sm leading-relaxed">
                    Satu platform terintegrasi untuk memantau presensi, jurnal harian, bimbingan berkala, hingga akumulasi jam lembur siswa selama masa Praktik Kerja Lapangan.
                </p>
            </div>

            <div class="text-xs text-sky-200/70 relative z-10">
                &copy; 2026 SMK Singaparna. All rights reserved.
            </div>
        </div>

        <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-white">
            <div class="w-full max-w-md space-y-8">
                
                <div class="md:hidden flex flex-col items-center text-center space-y-2">
                    <div class="w-12 h-12 bg-sky-500 rounded-2xl flex items-center justify-center shadow-lg shadow-sky-500/20 text-white mb-2">
                        <i class="pi pi-graduation-cap text-2xl"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">PORTAL PKL</h1>
                    <p class="text-sm text-slate-500">SMK Singaparna</p>
                </div>

                <div class="hidden md:block">
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">Masuk ke Akun Anda</h3>
                    <p class="text-sm text-slate-500 mt-1">Silakan masukkan email dan password terdaftar.</p>
                </div>

                <div v-if="status" class="p-3 bg-emerald-50 border border-emerald-200 text-sm font-medium text-emerald-600 rounded-xl">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <InputLabel for="email" value="Alamat Email" class="text-slate-600 font-semibold text-xs uppercase" />
                        <div class="mt-1.5 relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="pi pi-envelope text-sm"></i>
                            </span>
                            <TextInput
                                id="email"
                                type="email"
                                class="pl-10 w-full rounded-xl border-slate-200 bg-slate-50/50 focus:border-sky-500 focus:ring-sky-500/20 transition-all text-sm"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="nama@email.com"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <InputLabel for="password" value="Password" class="text-slate-600 font-semibold text-xs uppercase" />
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-xs text-sky-600 hover:text-sky-700 font-medium transition-all"
                            >
                                Lupa Password?
                            </Link>
                        </div>
                        <div class="mt-1.5 relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="pi pi-lock text-sm"></i>
                            </span>
                            <TextInput
                                id="password"
                                type="password"
                                class="pl-10 w-full rounded-xl border-slate-200 bg-slate-50/50 focus:border-sky-500 focus:ring-sky-500/20 transition-all text-sm"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center cursor-pointer select-none">
                            <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-slate-300 text-sky-500 focus:ring-sky-500/20 w-4 h-4" />
                            <span class="ms-2 text-xs font-medium text-slate-500 hover:text-slate-700">Ingat Saya</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <PrimaryButton
                            class="w-full h-11 bg-sky-500 hover:bg-sky-600 text-white font-semibold rounded-xl shadow-md shadow-sky-500/10 hover:shadow-sky-600/20 transition-all flex items-center justify-center gap-2 text-sm justify-center"
                            :class="{ 'opacity-50 pointer-events-none': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="form.processing">Memproses...</span>
                            <span v-else class="flex items-center justify-center gap-2">
                                Masuk Sistem <i class="pi pi-arrow-right text-xs"></i>
                            </span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>