<template>
  <DashboardLayout>
    <div class="space-y-4">
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="(val, key) in rekap" :key="key" class="p-4 bg-white rounded-2xl border border-sky-100 shadow-2xs flex justify-between items-center border-l-4" :class="colorMap[key]">
          <span class="text-sm font-bold text-slate-500 uppercase">{{ key }}</span>
          <span class="text-xl font-black text-slate-800">{{ val }}%</span>
        </div>
      </div>

      <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        
        <div class="flex w-full md:w-auto gap-2">
          <div class="relative w-full md:w-64">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input v-model="form.search" @input="doSearch" type="text" placeholder="Cari nama siswa..." 
              class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-sky-500 focus:bg-white transition-all" />
          </div>
          
          <input v-model="form.bulan" @change="doSearch" type="month" 
            class="px-3 py-2 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-sky-500 focus:bg-white transition-all cursor-pointer" />
        </div>

        <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto">
          
          <div class="flex gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
            <label v-for="opt in ['Hadir', 'Izin', 'Sakit', 'Alfa']" :key="opt" class="cursor-pointer select-none">
              <input type="checkbox" :value="opt" v-model="form.status" @change="doSearch" class="peer hidden" />
              <div class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all text-slate-500 hover:text-slate-700 peer-checked:bg-sky-500 peer-checked:text-white peer-checked:shadow-sm">
                {{ opt }}
              </div>
            </label>
          </div>
          
          <button @click="resetFilter" title="Reset Filter" 
            class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all ml-1">
            <i class="pi pi-filter-slash text-lg"></i>
          </button>

       
          <button @click="tambahModalOpen = true" 
    class="bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center gap-2 ml-2">
    <i class="pi pi-plus"></i> Tambah
  </button>
          
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-semibold text-sm">
                <th class="p-4">#</th> <th class="p-4">Siswa</th>
                <th class="p-4">Tanggal</th>
                <th class="p-4">Masuk / Pulang</th>
                <th class="p-4 text-center">Status</th>
                <th class="p-4 text-center">Aksi</th> </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-sm">
              <tr v-if="!presensi.data.length"><td colspan="6" class="p-8 text-center">Data kosong.</td></tr>
              <tr v-for="(item, index) in presensi.data" :key="item.id" class="hover:bg-sky-50/10">
                <td class="p-4">{{ presensi.from + index }}</td>
                <td class="p-4 font-semibold">{{ item.siswa?.user?.name }}</td>
                <td class="p-4 whitespace-nowrap">{{ item.tanggal }}</td>
                <td class="p-4 font-mono font-medium">
                  <span class="text-emerald-600">{{ item.jam_masuk || '-' }}</span> / 
                  <span class="text-amber-600">{{ item.jam_pulang || '-' }}</span>
                </td>
                <td class="p-4 text-center">
                  <span class="px-2.5 py-1 rounded-full text-xs font-bold border" :class="badgeColor(item.status)">{{ item.status }}</span>
                  <a v-if="item.latitude_masuk" :href="`https://maps.google.com/?q=${item.latitude_masuk},${item.longitude_masuk}`" target="_blank" class="block mt-1 text-[10px] text-sky-500 hover:underline">Lihat Map</a>
                </td>
                <td class="p-4 text-center space-x-2">
                  <button @click="openModal(item)" class="text-blue-500 hover:text-blue-700"><i class="pi pi-pencil"></i></button>
                  <button @click="destroy(item.id)" class="text-red-500 hover:text-red-700"><i class="pi pi-trash"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 bg-slate-50/50 flex justify-between items-center text-xs text-slate-500">
          <span>Menampilkan {{ presensi.from }} - {{ presensi.to }} dari {{ presensi.total }}</span>
          <div class="flex gap-1">
            <Link v-for="(l, i) in presensi.links" :key="i" :href="l.url ?? '#'" v-html="l.label" :class="['px-3 py-1.5 rounded-lg', l.active ? 'bg-sky-500 text-white' : 'border bg-white', !l.url && 'opacity-50']" />
          </div>
        </div>
      </div>
    </div>
    
    <PresensiModal :show="modalOpen" :data="selectedData" @close="modalOpen = false" />
  <!-- <PresensiModal :show="modalOpen" :data="selectedData" :siswas="props.siswas" @close="modalOpen = false" /> -->
   <TambahAbsen :show="tambahModalOpen" :siswas="props.siswas" @close="tambahModalOpen = false" />
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import PresensiModal from './PresensiModal.vue';
import TambahAbsen from './TambahAbsen.vue'; // Pastikan komponen di-import



// const openModal = (item = null) => {
//   selectedData.value = item;
//   modalOpen.value = true;
// };



const props = defineProps({ presensi: Object, filters: Object, rekap: Object, siswas: Array  });


// Status adalah array [] agar bisa menampung multiple select
const form = ref({ search: props.filters.search || '', bulan: props.filters.bulan || '', status: props.filters.status || [] });


const modalOpen = ref(false);
const tambahModalOpen = ref(false); // <-- State baru untuk modal Tambah
const selectedData = ref(null);

const doSearch = () => router.get(route('guru.dashboard'), form.value, { preserveState: true, replace: true });
const resetFilter = () => { form.value = { search: '', bulan: '', status: [] }; doSearch(); };

const destroy = (id) => {
  if (confirm('Yakin hapus data ini?')) router.delete(route('guru.presensi.destroy', id), { preserveScroll: true });
};

const openModal = (item) => {
  selectedData.value = item;
  modalOpen.value = true;
};

// UI Helpers
const colorMap = { Hadir: 'border-emerald-500', Sakit: 'border-blue-500', Izin: 'border-amber-500', Alfa: 'border-red-500' };
const badgeColor = (s) => s === 'Hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : s === 'Alfa' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-amber-50 text-amber-600 border-amber-100';
</script>