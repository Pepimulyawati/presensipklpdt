<template>
    <!-- DONE TAMPILAN LEMBUR GURU PEMBIMBING -->
  <DashboardLayout>
    <div class="space-y-4">
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div v-for="(val, key) in rekap" :key="key" class="p-4 bg-white rounded-2xl border border-sky-100 shadow-2xs flex justify-between items-center border-l-4 border-sky-500">
          <span class="text-sm font-bold text-slate-500 uppercase">{{ key }}</span>
          <span class="text-xl font-black text-slate-800">{{ val }} {{ key === 'Total Jam' ? 'Jam' : key === 'Total Menit' ? 'Min' : 'Kali' }}</span>
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

        <button @click="resetFilter" title="Reset Filter" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all self-end md:self-auto">
          <i class="pi pi-filter-slash text-lg"></i> Reset Filter
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-semibold text-sm">
                <th class="p-4">#</th>
                <th class="p-4">Siswa</th>
                <th class="p-4">Tanggal</th>
                <th class="p-4">Jam Kerja (Msk / Plg)</th>
                <th class="p-4 text-center">Durasi</th>
                <th class="p-4 text-center">Lokasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-sm">
              <tr v-if="!lembur.data.length"><td colspan="6" class="p-8 text-center">Tidak ada data lembur.</td></tr>
              <tr v-for="(item, index) in lembur.data" :key="item.id" class="hover:bg-sky-50/10">
                <td class="p-4">{{ lembur.from + index }}</td>
              <td class="p-4 font-semibold">{{ item.siswa?.nama_lengkap }}</td>
                <td class="p-4 whitespace-nowrap">{{ item.tanggal }}</td>
                <td class="p-4 font-mono font-medium">
                  <span class="text-emerald-600">{{ item.jam_masuk || '-' }}</span> / 
                  <span class="text-amber-600">{{ item.jam_pulang || '-' }}</span>
                </td>
                <td class="p-4 text-center font-bold text-sky-600">
                  {{ item.jumlah_menit }} Menit
                  <span class="block text-[10px] text-slate-400 font-normal">({{ roundJam(item.jumlah_menit) }} Jam)</span>
                </td>
                <td class="p-4 text-center space-y-1">
                  <a v-if="item.latitude_masuk" :href="`https://www.google.com/maps?q=${item.latitude_masuk},${item.longitude_masuk}`" target="_blank" class="inline-block px-2 py-1 text-[10px] bg-emerald-50 text-emerald-600 rounded border border-emerald-100 hover:underline mr-1">Map Masuk</a>
                  <a v-if="item.latitude_pulang" :href="`https://www.google.com/maps?q=${item.latitude_pulang},${item.longitude_pulang}`" target="_blank" class="inline-block px-2 py-1 text-[10px] bg-amber-50 text-amber-600 rounded border border-amber-100 hover:underline">Map Pulang</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 bg-slate-50/50 flex justify-between items-center text-xs text-slate-500">
          <span>Menampilkan {{ lembur.from }} - {{ lembur.to }} dari {{ lembur.total }}</span>
          <div class="flex gap-1">
            <Link v-for="(l, i) in lembur.links" :key="i" :href="l.url ?? '#'" v-html="l.label" :class="['px-3 py-1.5 rounded-lg', l.active ? 'bg-sky-500 text-white' : 'border bg-white', !l.url && 'opacity-50']" />
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({ lembur: Object, filters: Object, rekap: Object });

const form = ref({ 
  search: props.filters.search || '', 
  bulan: props.filters.bulan || '' 
});

const doSearch = () => {
  router.get(route('guru.lembur'), form.value, { preserveState: true, replace: true });
};

const resetFilter = () => { 
  form.value = { search: '', bulan: '' }; 
  doSearch(); 
};

const roundJam = (menit) => {
  return (menit / 60).toFixed(1);
};
</script>