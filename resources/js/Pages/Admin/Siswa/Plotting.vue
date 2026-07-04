<template>
  <DashboardLayout>
    <div class="space-y-6">
      
      <!-- HEADER DASHBOARD -->
      <div class="bg-white p-6 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-map-marker text-sky-500"></i> Plotting Penempatan PKL Siswa
          </h1>
          <p class="text-sm text-slate-500 mt-1">Hubungkan siswa dengan DUDI mitra dan tentukan instruktur lapangannya.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
          <a :href="route('admin.siswa.plotting.template')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
            <i class="pi pi-download"></i> Template Excel
          </a>

          <input type="file" ref="fileInputPlotting" @change="handlePlottingUpload" accept=".xlsx, .xls" class="hidden" />
          
          <button @click="triggerFileSelect" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
            <i class="pi pi-file-excel"></i> Import Plotting Massal
          </button>
        </div>
      </div>

      <!-- SEARCH & TOTAL -->
      <div class="bg-white p-4 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full max-w-xs">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input 
            v-model="search" 
            @input="doSearch" 
            type="text" 
            placeholder="Cari nama siswa atau NISN..." 
            class="pl-10 pr-4 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-sky-50/20 w-full" 
          />
        </div>
        <span class="text-xs text-slate-400 font-mono bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
          Total: {{ siswas.total }} Siswa
        </span>
      </div>

      <!-- TABLE PLOTTING -->
      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-semibold text-sm">
                <th class="p-4 text-center w-12">No</th>
                <th class="p-4">Nama Siswa</th>
                <th class="p-4">Pembimbing Internal (Guru)</th>
                <th class="p-4">Tempat PKL (DUDI)</th>
                <th class="p-4">Instruktur Lapangan</th>
                <th class="p-4 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-sm">
              <tr v-if="!siswas.data || siswas.data.length === 0">
                <td colspan="6" class="p-12 text-center text-slate-400">
                  <i class="pi pi-info-circle text-2xl mb-2"></i>
                  <p>Data siswa tidak ditemukan.</p>
                </td>
              </tr>
              <tr v-for="(siswa, index) in siswas.data" :key="siswa.id" class="hover:bg-sky-50/30 transition-colors">
                <td class="p-4 text-center text-slate-400 font-mono">
                   {{ (siswas.current_page - 1) * siswas.per_page + index + 1 }}
                </td>
                <td class="p-4">
                  <div class="font-bold text-slate-900">{{ siswa.user?.name }}</div>
                  <div class="text-xs text-slate-400 font-mono">NISN. {{ siswa.nisn }}</div>
                </td>
                <td class="p-4">
                  <div v-if="siswa.guru" class="flex items-center gap-2">
                    <i class="pi pi-user text-[10px] text-slate-400"></i>
                    <span class="text-slate-700 font-medium">{{ siswa.guru?.user?.name }}</span>
                  </div>
                  <span v-else class="text-xs text-slate-300 italic">Belum diplot</span>
                </td>
                <td class="p-4">
                  <div v-if="siswa.dudi" class="text-sky-600 font-bold flex items-center gap-1.5">
                    <i class="pi pi-building text-xs"></i> {{ siswa.dudi.nama_dudi }}
                  </div>
                  <span v-else class="px-2 py-0.5 bg-rose-50 text-rose-500 text-[10px] rounded-md font-bold uppercase tracking-tighter">Belum Ada Tempat</span>
                </td>
                <td class="p-4">
                  <div v-if="siswa.instruktur">
                    <div class="font-semibold text-slate-800">{{ siswa.instruktur.nama_instruktur }}</div>
                    <div class="text-[10px] text-slate-400">{{ siswa.instruktur.jabatan }}</div>
                  </div>
                  <span v-else class="text-slate-400 text-xs italic opacity-50">Kosong</span>
                </td>
                <td class="p-4 text-center">
                  <button @click="openModalPlotting(siswa)" class="bg-sky-50 hover:bg-sky-500 text-sky-600 hover:text-white text-xs px-3 py-1.5 rounded-xl font-semibold transition-all flex items-center gap-1 mx-auto border border-sky-100 shadow-sm">
                    <i class="pi pi-sliders-h"></i> Atur PKL
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- PAGINATION -->
        <div class="p-4 bg-slate-50/50 border-t border-sky-100 flex flex-col sm:flex-row items-center justify-between gap-4">
          <span class="text-xs text-slate-500">
            Halaman {{ siswas.current_page }} dari {{ siswas.last_page }}
          </span>
          <div class="flex gap-1">
            <Link 
              v-for="(link, idx) in siswas.links" 
              :key="idx" 
              :href="link.url ?? '#'" 
              v-html="link.label" 
              :class="[
                'px-3 py-1.5 text-xs rounded-lg transition-all border', 
                link.active ? 'bg-sky-500 text-white border-sky-500 font-bold' : 'bg-white border-sky-100 text-slate-600 hover:bg-sky-50', 
                !link.url ? 'opacity-40 cursor-not-allowed' : ''
              ]" 
            />
          </div>
        </div>
      </div>

      <!-- MODAL PLOTTING -->
      <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl border border-sky-100 w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
          <div class="p-5 border-b border-sky-50 flex justify-between items-center bg-sky-50/30">
            <h3 class="font-bold text-slate-800 flex items-center gap-2">
              <i class="pi pi-map-marker text-sky-500"></i> Atur Penempatan Siswa
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 transition-colors">
              <i class="pi pi-times"></i>
            </button>
          </div>
          
          <div class="p-5 space-y-5">
            <!-- Info Siswa -->
            <div class="p-3 bg-sky-50/50 border border-sky-100 rounded-xl">
              <label class="block text-[10px] font-bold text-sky-600 uppercase tracking-widest mb-1">Nama Siswa</label>
              <div class="font-bold text-slate-800">{{ selectedSiswa?.user?.name }}</div>
              <div class="text-xs text-slate-500 font-mono">NISN. {{ selectedSiswa?.nisn }}</div>
            </div>

            <!-- Pilih DUDI -->
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-2">Tempat PKL (Dunia Industri)</label>
              <select v-model="form.dudi_id" @change="onDudiChange" class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-white shadow-xs">
                <option :value="null">-- Kosongkan / Cabut Penempatan --</option>
                <option v-for="dudi in dudis" :key="dudi.id" :value="dudi.id">
                  {{ dudi.nama_dudi }} ({{ dudi.zona }})
                </option>
              </select>
            </div>

            <!-- Pilih Instruktur -->
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-2">Instruktur Lapangan</label>
              <select 
                v-model="form.instruktur_id" 
                :disabled="!form.dudi_id" 
                class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-white disabled:bg-slate-50 disabled:text-slate-400 shadow-xs"
              >
                <option :value="null">-- Pilih Instruktur Lapangan --</option>
                <option v-for="ins in filteredInstrukturs" :key="ins.id" :value="ins.id">
                  {{ ins.nama_instruktur }} [{{ ins.jabatan }}]
                </option>
              </select>
              
              <!-- Warning if no instrukturs -->
              <div v-if="form.dudi_id && filteredInstrukturs.length === 0" class="mt-2 p-2 bg-amber-50 rounded-lg flex gap-2 border border-amber-100">
                <i class="pi pi-exclamation-triangle text-amber-500 text-sm mt-0.5"></i>
                <p class="text-[10px] text-amber-700 leading-tight">
                  Mitra DUDI ini belum memiliki data instruktur. Mohon tambahkan instruktur di menu <span class="font-bold">Data Mitra</span> terlebih dahulu.
                </p>
              </div>
            </div>

            <!-- Footer Modal -->
            <div class="pt-4 flex justify-end gap-2 border-t border-sky-50">
              <button @click="showModal = false" class="px-4 py-2 text-xs bg-slate-100 text-slate-600 font-semibold rounded-xl hover:bg-slate-200 transition-colors">
                Batal
              </button>
              <button 
                @click="submitPlotting" 
                :disabled="form.processing"
                class="px-4 py-2 text-xs bg-sky-500 text-white font-semibold rounded-xl hover:bg-sky-600 transition-all flex items-center gap-2 shadow-md shadow-sky-100"
              >
                <i v-if="form.processing" class="pi pi-spin pi-spinner text-[10px]"></i>
                Simpan Penempatan
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref, computed } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  siswas: Object,
  dudis: Array,
  instrukturs: Array,
  filters: Object
});

// State Pencarian
const search = ref(props.filters?.search || '');

// State Modal
const showModal = ref(false);
const selectedSiswa = ref(null);
const fileInputPlotting = ref(null);

// Form Management via useForm
const form = useForm({
  dudi_id: null,
  instruktur_id: null
});

// Handler Pencarian
const doSearch = () => {
  router.get(route('admin.siswa.plotting'), { search: search.value }, { preserveState: true, replace: true, preserveScroll: true });
};

// Filter Instruktur berdasarkan DUDI yang dipilih
const filteredInstrukturs = computed(() => {
  if (!form.dudi_id) return [];
  return props.instrukturs.filter(ins => ins.dudi_id === form.dudi_id);
});

// Reset instruktur saat DUDI berubah
const onDudiChange = () => {
  form.instruktur_id = null;
};

// Buka Modal
const openModalPlotting = (siswa) => {
  selectedSiswa.value = siswa;
  form.dudi_id = siswa.dudi_id;
  form.instruktur_id = siswa.instruktur_id;
  showModal.value = true;
};

// Submit Simpan Plotting Manual
const submitPlotting = () => {
  form.put(route('admin.siswa.update-plotting', selectedSiswa.value.id), {
    onSuccess: () => {
      showModal.value = false;
      // Notifikasi sukses biasanya dikirim via Flash Props
    },
    onError: (errors) => {
      alert('Terjadi kesalahan: ' + Object.values(errors).join(', '));
    }
  });
};

// Trigger Upload
const triggerFileSelect = () => {
  fileInputPlotting.value.click();
};

// Handle Import Massal
const handlePlottingUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (confirm(`Proses plotting massal menggunakan file "${file.name}"?`)) {
    const formData = new FormData();
    formData.append('file_excel', file);

    router.post(route('admin.siswa.plotting.import'), formData, {
      forceFormData: true,
      onSuccess: () => {
        alert('Plotting massal berhasil diproses!');
        event.target.value = ''; // Reset input
      },
      onError: (errors) => {
        alert('Gagal memproses berkas: ' + (errors.file_excel || 'Terjadi kesalahan format file.'));
        event.target.value = '';
      }
    });
  } else {
    event.target.value = '';
  }
};
</script>

<style scoped>
.animate-in {
  animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>