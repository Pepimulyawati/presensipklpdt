<template>
  <!-- DONE TAMPILAN RIWYAT ABSEN DILENGKAPI -->
  <DashboardLayout>
    <div class="space-y-6">
      
      <div class="bg-white p-6 rounded-2xl border border-sky-100 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-history text-sky-500"></i> Riwayat Kehadiran PKL
          </h1>
          <p class="text-sm text-slate-500 mt-1">Pantau seluruh catatan absensi masuk dan pulang Anda.</p>
        </div>
        
        <div class="flex items-center gap-2 w-full md:w-auto">
          <input type="month" v-model="filterMonth" @change="applyFilter" class="w-full md:w-auto border-slate-200 rounded-lg p-2.5 text-sm focus:ring-sky-500 focus:border-sky-500" />
          <button @click="applyFilter" class="bg-sky-500 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-sky-600 transition flex items-center gap-2">
            <i class="pi pi-filter"></i> Filter
          </button>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4">
        <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl text-center shadow-sm">
          <div class="text-emerald-600 text-sm font-semibold mb-1">HADIR</div>
          <div class="text-3xl font-bold text-emerald-700">{{ summary.hadir }}</div>
        </div>
        <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl text-center shadow-sm">
          <div class="text-amber-600 text-sm font-semibold mb-1">IZIN</div>
          <div class="text-3xl font-bold text-amber-700">{{ summary.izin }}</div>
        </div>
        <div class="bg-rose-50 border border-rose-100 p-4 rounded-xl text-center shadow-sm">
          <div class="text-rose-600 text-sm font-semibold mb-1">SAKIT</div>
          <div class="text-3xl font-bold text-rose-700">{{ summary.sakit }}</div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-semibold text-sm">
                <th class="p-4 w-32">Tanggal</th>
                <th class="p-4">Jam Masuk</th>
                <th class="p-4">Jam Pulang</th>
                <th class="p-4 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-sm">
              <tr v-if="riwayat.data.length === 0">
                <td colspan="4" class="p-8 text-center text-slate-400">
                  <i class="pi pi-inbox text-2xl mb-2 block"></i> Belum ada catatan presensi.
                </td>
              </tr>
              <tr v-for="item in riwayat.data" :key="item.id" class="hover:bg-sky-50/20 transition-colors">
                <td class="p-4 font-medium text-slate-900 whitespace-nowrap">{{ formatDate(item.tanggal) }}</td>
                
                <td class="p-4">
                  <div class="flex items-center gap-1.5" v-if="item.jam_masuk">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-mono font-semibold text-emerald-600">{{ item.jam_masuk }}</span>
                  </div>
                  <span v-else class="text-slate-400">-</span>
                </td>
                
                <td class="p-4">
                  <div v-if="item.jam_pulang" class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    <span class="font-mono font-semibold text-sky-600">{{ item.jam_pulang }}</span>
                  </div>
                  <span v-else class="text-slate-400 text-xs italic bg-slate-100 px-2 py-0.5 rounded-md">Belum Pulang</span>
                </td>

                <td class="p-4 text-center whitespace-nowrap">
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-bold border',
                    item.status === 'Hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : '',
                    item.status === 'Izin'  ? 'bg-amber-50 text-amber-600 border-amber-200' : '',
                    item.status === 'Sakit' ? 'bg-rose-50 text-rose-600 border-rose-200' : ''
                  ]">
                    {{ item.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="p-4 border-t border-slate-100 flex flex-wrap items-center justify-center gap-1" v-if="riwayat.links && riwayat.links.length > 3">
          <Link v-for="(link, i) in riwayat.links" :key="i" :href="link.url || '#'" 
                class="px-3 py-1 rounded-md text-sm transition-colors border"
                :class="link.active ? 'bg-sky-500 text-white border-sky-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                v-html="link.label" preserve-scroll />
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
  riwayat: Object, // Diubah menjadi Object karena menggunakan Laravel Paginate
  summary: Object,
  filters: Object
});

const filterMonth = ref(props.filters?.bulan || '');

const applyFilter = () => {
  router.get(route('siswa.riwayat'), { bulan: filterMonth.value }, { preserveState: true, replace: true });
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};
</script>