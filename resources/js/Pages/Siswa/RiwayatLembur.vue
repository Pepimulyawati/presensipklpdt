<template>
  <DashboardLayout>
    <div class="space-y-6">
      
      <div class="bg-white p-6 rounded-2xl border border-indigo-100 shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-clock text-indigo-500"></i> Riwayat Lembur PKL
          </h1>
          <p class="text-sm text-slate-500 mt-1">Pantau akumulasi durasi jam kerja lembur Anda selama magang.</p>
        </div>
        
        <div class="flex items-center gap-2 w-full md:w-auto">
          <input type="month" v-model="filterMonth" @change="applyFilter" class="w-full md:w-auto border-slate-200 rounded-lg p-2.5 text-sm focus:ring-indigo-500 focus:border-indigo-500" />
          <button @click="applyFilter" class="bg-indigo-500 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-indigo-600 transition flex items-center gap-2">
            <i class="pi pi-filter"></i> Filter
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-indigo-50 border border-indigo-100 p-5 rounded-xl flex items-center gap-4 shadow-sm">
          <div class="p-3 bg-indigo-500 text-white rounded-lg">
            <i class="pi pi-calendar text-2xl"></i>
          </div>
          <div>
            <div class="text-indigo-600 text-xs font-bold uppercase tracking-wider">Total Hari Lembur</div>
            <div class="text-2xl font-black text-indigo-900 mt-0.5">{{ summary.total_hari }} Hari</div>
          </div>
        </div>
        
        <div class="bg-violet-50 border border-violet-100 p-5 rounded-xl flex items-center gap-4 shadow-sm">
          <div class="p-3 bg-violet-500 text-white rounded-lg">
            <i class="pi pi-stopwatch text-2xl"></i>
          </div>
          <div>
            <div class="text-violet-600 text-xs font-bold uppercase tracking-wider">Akumulasi Durasi</div>
            <div class="text-2xl font-black text-violet-900 mt-0.5">{{ summary.total_durasi }}</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-indigo-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-indigo-50/50 border-b border-indigo-100 text-slate-600 font-semibold text-sm">
                <th class="p-4 w-32">Tanggal</th>
                <th class="p-4">Jam Masuk</th>
                <th class="p-4">Jam Pulang</th>
                <th class="p-4 text-center">Durasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-indigo-50 text-slate-700 text-sm">
              <tr v-if="riwayat.data.length === 0">
                <td colspan="4" class="p-8 text-center text-slate-400">
                  <i class="pi pi-inbox text-2xl mb-2 block"></i> Belum ada catatan lembur terekam.
                </td>
              </tr>
              <tr v-for="item in riwayat.data" :key="item.id" class="hover:bg-indigo-50/10 transition-colors">
                <td class="p-4 font-medium text-slate-900 whitespace-nowrap">{{ formatDate(item.tanggal) }}</td>
                
                <td class="p-4">
                  <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-mono font-semibold text-emerald-600">{{ item.jam_masuk }}</span>
                  </div>
                </td>
                
                <td class="p-4">
                  <div v-if="item.jam_pulang" class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="font-mono font-semibold text-amber-600">{{ item.jam_pulang }}</span>
                  </div>
                  <span v-else class="text-slate-400 text-xs italic bg-slate-100 px-2 py-0.5 rounded-md">Belum Pulang</span>
                </td>

                <td class="p-4 text-center whitespace-nowrap">
                  <span v-if="item.jumlah_menit !== null" class="px-2.5 py-1 rounded-md text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 font-mono">
                    {{ formatMenit(item.jumlah_menit) }}
                  </span>
                  <span v-else class="text-slate-400">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="p-4 border-t border-slate-100 flex flex-wrap items-center justify-center gap-1" v-if="riwayat.links && riwayat.links.length > 3">
          <Link v-for="(link, i) in riwayat.links" :key="i" :href="link.url || '#'" 
                class="px-3 py-1 rounded-md text-sm transition-colors border"
                :class="link.active ? 'bg-indigo-500 text-white border-indigo-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
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
  riwayat: Object,
  summary: Object,
  filters: Object
});

const filterMonth = ref(props.filters?.bulan || '');

const applyFilter = () => {
  router.get(route('siswa.riwayat-lembur'), { bulan: filterMonth.value }, { preserveState: true, replace: true });
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
};

// Helper untuk mengubah jumlah_menit tiap baris menjadi format jam/menit di tabel
const formatMenit = (menitTotal) => {
  const j = floor(menitTotal / 60);
  const m = menitTotal % 60;
  return j > 0 ? `${j} Jam ${m} Menit` : `${m} Menit`;
};

// Pengganti Math.floor agar bisa dipakai langsung di template expression Vue
const floor = (num) => Math.floor(num);
</script>