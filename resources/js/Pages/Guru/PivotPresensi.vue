<template>
  <DashboardLayout>
    <div class="space-y-6">
      
      <div class="bg-white p-6 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-calendar-check text-sky-500"></i> Rekap Presensi Pivot Bulanan
          </h1>
          <p class="text-sm text-slate-500 mt-1">Tinjauan cepat absensi bulanan siswa. Angka merepresentasikan tanggal (1 - {{ jumlahHari }}).</p>
        </div>
        
        <div class="flex items-center gap-2">
          <span class="text-xs font-semibold text-slate-500">Pilih Bulan:</span>
          <input v-model="bulanFilter" @change="gantiBulan" type="month" class="px-4 py-1.5 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-white" />
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-sky-100 flex flex-wrap gap-4 text-xs font-medium text-slate-600 shadow-2xs">
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-emerald-100 text-emerald-700 font-bold text-[10px] flex items-center justify-center">H</span> Hadir</div>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-amber-100 text-amber-700 font-bold text-[10px] flex items-center justify-center">I</span> Izin</div>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-blue-100 text-blue-700 font-bold text-[10px] flex items-center justify-center">S</span> Sakit</div>
        <div class="flex items-center gap-1.5"><span class="w-4 h-4 rounded-md bg-red-100 text-red-700 font-bold text-[10px] flex items-center justify-center">-</span> Alfa / Tanpa Keterangan</div>
      </div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse table-fixed">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-bold text-xs">
                <th class="p-3 w-48 sticky left-0 bg-sky-50 z-10 shadow-xs">Nama Siswa</th>
                <th v-for="hari in jumlahHari" :key="hari" class="p-1 text-center w-8 font-mono border-l border-sky-100/50">
                  {{ hari }}
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-xs">
              <tr v-if="siswa.length === 0">
                <td :colspan="jumlahHari + 1" class="p-6 text-center text-slate-400">Belum ada siswa yang di-plot ke bimbingan Anda.</td>
              </tr>
              <tr v-for="s in siswa" :key="s.id" class="hover:bg-sky-50/10">
                <td class="p-3 font-semibold text-slate-900 sticky left-0 bg-white shadow-xs whitespace-nowrap overflow-hidden text-ellipsis">
                  {{ s.user?.name }}
                </td>
                
                <td v-for="hari in jumlahHari" :key="hari" class="p-1 text-center border-l border-sky-50 font-mono">
                  <span v-if="pivotData[s.id] && pivotData[s.id][hari]" 
                    class="w-6 h-6 flex items-center justify-center rounded-md font-bold mx-auto cursor-help text-[11px]" 
                    :class="getStatusStyle(pivotData[s.id][hari].status)"
                    :title="`Status: ${pivotData[s.id][hari].status} | Masuk: ${pivotData[s.id][hari].jam_masuk || '-'} | Pulang: ${pivotData[s.id][hari].jam_pulang || '-'}`">
                    {{ pivotData[s.id][hari].status.charAt(0).toUpperCase() }}
                  </span>
                  
                  <span v-else class="w-6 h-6 flex items-center justify-center rounded-md font-bold mx-auto text-[11px] bg-red-100 text-red-700" title="Alfa / Tanpa Keterangan">
                    -
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  siswa: Array,
  pivotData: Object,
  jumlahHari: Number,
  bulanDipilih: String
});

const bulanFilter = ref(props.bulanDipilih);

const gantiBulan = () => {
  router.get(route('guru.pivot'), { bulan: bulanFilter.value }, { preserveState: true });
};

// Auto mapping warna berdasarkan status absensi siswa
const getStatusStyle = (status) => {
  switch (status?.toLowerCase()) {
    case 'hadir': return 'bg-emerald-100 text-emerald-700';
    case 'izin': return 'bg-amber-100 text-amber-700';
    case 'sakit': return 'bg-blue-100 text-blue-700';
    case 'alfa': return 'bg-red-100 text-red-700';
    default: return 'bg-red-100 text-red-700';
  }
};
</script>