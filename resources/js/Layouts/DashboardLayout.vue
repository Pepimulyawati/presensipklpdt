<template>
  <div class="flex h-screen bg-sky-50/50 overflow-hidden">
    
    <div :class="[
        'fixed inset-y-0 left-0 z-20 w-64 bg-slate-900 text-slate-100 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col justify-between',
        isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]">
      
      <div>
        <div class="flex items-center justify-between px-6 h-16 bg-slate-950 font-bold text-lg border-b border-slate-800">
          <div class="flex items-center space-x-2">
            <i class="pi pi-id-card text-sky-400 text-xl"></i>
            <span class="text-sky-100 tracking-wide">Presensi PKL</span>
          </div>
          <button @click="isSidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white">
            <i class="pi pi-times text-xl"></i>
          </button>
        </div>

        <nav class="mt-6 px-4 space-y-1.5">

          <Link :href="route(authRouteDashboard)" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component.startsWith('Admin/Dashboard') || $page.component.startsWith('Guru/Dashboard') || $page.component.startsWith('Siswa/Dashboard') ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
            <i class="pi pi-home mr-3 text-lg"></i> Dashboard
          </Link>

         

          <template v-if="$page.props.auth.user.role === 'admin'">
  <Link 
    :href="route('admin.guru.index')" 
    :class="[
      'flex items-center p-3 rounded-xl transition-all duration-200 font-medium group mt-1', 
      $page.component === 'Admin/Guru/Index' 
        ? 'bg-sky-500 text-white' 
        : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400'
    ]"
  >
    <i class="pi pi-users mr-3 text-lg"></i> Data Master Guru
  </Link>

  <Link 
    :href="route('admin.siswa.index')" 
    :class="[
      'flex items-center p-3 rounded-xl transition-all duration-200 font-medium group mt-1', 
      $page.component === 'Admin/Siswa/Index' 
        ? 'bg-sky-500 text-white' 
        : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400'
    ]"
  >
    <i class="pi pi-user mr-3 text-lg"></i> Data Master Siswa
  </Link>

  <Link 
    :href="route('admin.siswa.plotting')" 
    :class="[
      'flex items-center p-3 rounded-xl transition-all duration-200 font-medium group mt-1', 
      $page.component === 'Admin/Siswa/Plotting' 
        ? 'bg-sky-500 text-white' 
        : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400'
    ]"
  >
    <i class="pi pi-map-marker mr-3 text-lg"></i> Plotting PKL Siswa
  </Link>

  <Link 
   :href="route('admin.pengantaran.index')"
    :class="[
      'flex items-center p-3 rounded-xl transition-all duration-200 font-medium group mt-1', 
      $page.component === 'Admin/Pengantaran/Index' 
        ? 'bg-sky-500 text-white' 
        : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400'
    ]"
  >
    <i class="pi pi-car mr-3 text-lg"></i> Dist. Pencarian
  </Link>

  <Link 
  :href="route('admin.pemberangkatan.index')"
  :class="[
    'flex items-center p-3 rounded-xl transition-all duration-200 font-medium group mt-1', 
    $page.component === 'Admin/Pemberangkatan/Index' 
      ? 'bg-sky-500 text-white' 
      : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400'
  ]"
>
  <i class="pi pi-send mr-3 text-lg"></i> Dist.Pemberangkatan
</Link>

  <Link 
    :href="route('admin.dudi.index')" 
    :class="[
      'flex items-center p-3 rounded-xl transition-all duration-200 font-medium group mt-1', 
      $page.component === 'Admin/Dudi/Index' 
        ? 'bg-sky-500 text-white' 
        : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400'
    ]"
  >
    <i class="pi pi-building mr-3 text-lg"></i> Data Mitra DUDI
  </Link>
</template>
      
          <template v-if="$page.props.auth.user.role === 'guru'">
  <Link :href="route('guru.dashboard')" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component === 'Guru/SiswaBimbingan' ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
    <i class="pi pi-book mr-3 text-lg"></i> Siswa Bimbingan
  </Link>
  <Link :href="route('guru.pivot')" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component === 'Guru/PivotPresensi' ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
    <i class="pi pi-calendar-check mr-3 text-lg"></i> Rekap Perbulan
  </Link>
  <Link :href="route('guru.lembur')" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component === 'Guru/LemburBimbingan' ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
  <i class="pi pi-clock mr-3 text-lg"></i> Lembur Bimbingan
</Link>
</template>

          <template v-if="$page.props.auth.user.role === 'siswa'">
          
          

            <Link :href="route('siswa.presensi')" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component === 'Siswa/Presensi' ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
    <i class="pi pi-camera mr-3 text-lg"></i> Presensi Sekarang
  </Link>

  <Link :href="route('siswa.lembur')" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component === 'Siswa/PresensiLembur' ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
    <i class="pi pi-clock mr-3 text-lg"></i> Presensi Lembur
  </Link>

  <Link :href="route('siswa.riwayat')" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component === 'Siswa/Riwayat' ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
    <i class="pi pi-history mr-3 text-lg"></i> Riwayat Kehadiran
  </Link>

  <Link :href="route('siswa.riwayat-lembur')" :class="['flex items-center p-3 rounded-xl transition-all duration-200 font-medium group', $page.component === 'Siswa/Siswa/RiwayatLembur' || $page.component === 'Siswa/RiwayatLembur' ? 'bg-sky-500 text-white' : 'text-slate-300 hover:bg-sky-950 hover:text-sky-400']">
    <i class="pi pi-stopwatch mr-3 text-lg"></i> Riwayat Lembur
  </Link>
          </template>

          <Link v-if="$page.props.auth.user.role === 'admin'" href="#" class="flex items-center p-3 text-slate-300 hover:bg-sky-950 hover:text-sky-400 rounded-xl transition-all duration-200 font-medium group">
            <i class="pi pi-cog mr-3 text-lg"></i> Pengaturan Aplikasi
          </Link>

        </nav>
      </div>

      <div class="p-4 border-t border-slate-800 bg-slate-950/50">
        <button @click="handleLogout" class="w-full flex items-center justify-center p-3 bg-red-500/10 hover:bg-red-500 hover:text-white text-red-400 rounded-xl transition-all duration-200 font-semibold shadow-sm gap-2">
          <i class="pi pi-sign-out text-lg"></i>
          <span>Keluar Aplikasi</span>
        </button>
      </div>

    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
      
      <header class="flex items-center justify-between h-16 bg-white border-b border-sky-100 px-6 shadow-sm shadow-sky-100/20">
        <button @click="isSidebarOpen = !isSidebarOpen" class="text-slate-500 focus:outline-none lg:hidden p-2 rounded-lg hover:bg-slate-100">
          <i class="pi pi-bars text-xl"></i>
        </button>
        
        <div class="flex items-center ml-auto space-x-3">
          <div class="text-right">
            <p class="text-slate-700 font-semibold text-sm">{{ $page.props.auth.user.name }}</p>
            <p class="text-slate-400 text-xs capitalize">{{ $page.props.auth.user.role }}</p>
          </div>
          <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center border border-sky-200 shadow-sm text-sky-600">
            <i class="pi pi-user text-lg"></i>
          </div>
        </div>
      </header>

      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/60 p-6">
        <slot />
      </main>
    </div>

    <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 z-10 bg-slate-900/40 backdrop-blur-xs lg:hidden"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';

const isSidebarOpen = ref(false);
const page = usePage();

// Menentukan rute dashboard utama secara dinamis berdasarkan role user saat ini
const authRouteDashboard = computed(() => {
  const role = page.props.auth.user.role;
  if (role === 'admin') return 'admin.dashboard';
  if (role === 'guru') return 'guru.dashboard';
  return 'siswa.dashboard';
});

// Fungsi Logout via Laravel Breeze Endpoint
const handleLogout = () => {
  if (confirm('Apakah Anda yakin ingin keluar dari aplikasi?')) {
    router.post(route('logout'));
  }
};
</script>