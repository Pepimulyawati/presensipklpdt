<template>
  <DashboardLayout>
    <div class="space-y-6">
      
      <!-- Header -->
     
      <div class="bg-white p-6 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-map-marker text-sky-500"></i> Plotting Penempatan PKL Siswa
          </h1>
          <p class="text-sm text-slate-500 mt-1">Tempatkan siswa ke DUDI mitra dan tentukan instruktur lapangannya secara real-time.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
          <a :href="route('admin.siswa.plotting.template')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
            <i class="pi pi-download"></i> Template Excel
          </a>

          <input type="file" ref="fileInputPlotting" @change="handlePlottingUpload" accept=".xlsx, .xls" class="hidden" />
          
          <button @click="$refs.fileInputPlotting.click()" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
            <i class="pi pi-file-excel"></i> Import Plotting Massal
          </button>
        </div>
      </div>
      <!-- Search Bar -->
      <div class="bg-white p-4 rounded-2xl border border-sky-100 shadow-2xs flex items-center justify-between">
        <input 
          v-model="search" 
          @input="doSearch" 
          type="text" 
          placeholder="Cari nama siswa..." 
          class="px-4 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-sky-50/20 w-full max-w-xs" 
        />
      </div>

      <!-- Tabel Plotting -->
      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-semibold text-sm">
                <th class="p-4 text-center w-12">No</th>
                <th class="p-4">Nama Siswa</th>
                <th class="p-4">Guru Pembimbing (Internal)</th>
                <th class="p-4">Tempat PKL (DUDI)</th>
                <th class="p-4">Instruktur Lapangan</th>
                <th class="p-4 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-sm">
              <tr v-for="(siswa, index) in siswas.data" :key="siswa.id" class="hover:bg-sky-50/10">
                <td class="p-4 text-center text-slate-400 font-mono">{{ siswas.from + index }}</td>
                <td class="p-4">
                  <div class="font-bold text-slate-900">{{ siswa.user?.name }}</div>
                  <div class="text-xs text-slate-400 font-mono">NISN. {{ siswa.nisn }}</div>
                </td>
                <td class="p-4">
                  <span class="text-slate-700 font-medium">{{ siswa.guru?.user?.name ?? 'Belum Diplot' }}</span>
                </td>
                <!-- Kolom Status DUDI saat ini -->
                <td class="p-4">
                  <div v-if="siswa.dudi" class="text-sky-600 font-bold flex items-center gap-1">
                    <i class="pi pi-building text-xs"></i> {{ siswa.dudi.nama_dudi }}
                  </div>
                  <span v-else class="text-xs bg-rose-50 text-rose-500 px-2 py-1 rounded-md font-medium">Belum dapat tempat</span>
                </td>
                <!-- Kolom Status Instruktur saat ini -->
                <td class="p-4">
                  <div v-if="siswa.instruktur">
                    <div class="font-semibold text-slate-800">{{ siswa.instruktur.nama_instruktur }}</div>
                    <div class="text-xs text-slate-400">{{ siswa.instruktur.jabatan }}</div>
                  </div>
                  <span v-else class="text-slate-400 text-xs italic">Belum ada pembimbing</span>
                </td>
                <!-- Tombol Aksi Kelola Tempat PKL -->
                <td class="p-4 text-center">
                  <button @click="openModalPlotting(siswa)" class="bg-sky-50 hover:bg-sky-500 text-sky-600 hover:text-white text-xs px-3 py-1.5 rounded-xl font-semibold transition-all flex items-center gap-1 mx-auto">
                    <i class="pi pi-sliders-h"></i> Atur PKL
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FORM MODAL POPUP PLOTTING -->
      <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl border border-sky-100 w-full max-w-md shadow-xl overflow-hidden">
          <div class="p-5 border-b border-sky-50 flex justify-between items-center bg-sky-50/30">
            <h3 class="font-bold text-slate-800">Atur Tempat PKL Siswa</h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="pi pi-times"></i></button>
          </div>
          
          <div class="p-5 space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Siswa</label>
              <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl font-bold text-slate-700">{{ selectedSiswa?.user?.name }}</div>
            </div>

            <!-- DROPDOWN 1: PILIH PERUSAHAAN (DUDI) -->
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Dunia Industri (DUDI)</label>
              <select v-model="form.dudi_id" @change="onDudiChange" class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-white text-slate-700">
                <option :value="null">-- Kosongkan / Belum PKL --</option>
                <option v-for="dudi in dudis" :key="dudi.id" :value="dudi.id">{{ dudi.nama_dudi }} ({{ dudi.zona }})</option>
              </select>
            </div>

            <!-- DROPDOWN 2: PILIH INSTRUKTUR (OTOMATIS TERFILTER) -->
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Instruktur Lapangan</label>
              <select v-model="form.instruktur_id" :disabled="!form.dudi_id" class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-white text-slate-700 disabled:bg-slate-50 disabled:text-slate-400">
                <option :value="null">-- Pilih Instruktur Lapangan --</option>
                <option v-for="ins in filteredInstrukturs" :key="ins.id" :value="ins.id">{{ ins.nama_instruktur }} - [{{ ins.jabatan }}]</option>
              </select>
              <p v-if="form.dudi_id && filteredInstrukturs.length === 0" class="text-[11px] text-amber-500 mt-1">
                ⚠️ DUDI ini belum memiliki data instruktur lapangan. Silakan isi terlebih dahulu di menu Mitra DUDI.
              </p>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-sky-50">
              <button @click="showModal = false" class="px-4 py-2 text-xs bg-slate-100 text-slate-600 font-semibold rounded-xl">Batal</button>
              <button @click="submitPlotting" class="px-4 py-2 text-xs bg-sky-500 text-white font-semibold rounded-xl hover:bg-sky-600">Simpan Penempatan</button>
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
import { router } from '@inertiajs/vue3';

const props = defineProps({
  siswas: Object,
  dudis: Array,
  instrukturs: Array,
  filters: Object
});

const search = ref(props.filters.search || '');
const showModal = ref(false);
const selectedSiswa = ref(null);
const form = ref({ dudi_id: null, instruktur_id: null });

const doSearch = () => {
  router.get(route('admin.siswa.index'), { search: search.value }, { preserveState: true, replace: true });
};

// Logika Filter Dropdown: Instruktur hanya muncul jika dudi_id miliknya cocok
const filteredInstrukturs = computed(() => {
  if (!form.value.dudi_id) return [];
  return props.instrukturs.filter(ins => ins.dudi_id === form.value.dudi_id);
});

// Jika DUDI diubah, reset pilihan instruktur lama agar tidak konflik
const onDudiChange = () => {
  form.value.instruktur_id = null;
};

const openModalPlotting = (siswa) => {
  selectedSiswa.value = siswa;
  form.value.dudi_id = siswa.dudi_id;
  form.value.instruktur_id = siswa.instruktur_id;
  showModal.value = true;
};

const submitPlotting = () => {
  router.put(route('admin.siswa.update-plotting', selectedSiswa.value.id), form.value, {
    onSuccess: () => {
      showModal.value = false;
      alert('Berhasil mengatur tempat PKL siswa!');
    }
  });
};


const fileInputPlotting = ref(null);

const handlePlottingUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (confirm(`Apakah Anda yakin ingin melakukan plotting massal siswa menggunakan file ${file.name}?`)) {
    const formData = new FormData();
    formData.append('file_excel', file);

    router.post(route('admin.siswa.plotting.import'), formData, {
      forceFormData: true,
      onSuccess: () => {
        alert('Plotting massal 1.000 siswa selesai diproses!');
        fileInputPlotting.value = ''; // Reset input file
      },
      onError: (errors) => {
        alert('Gagal memproses berkas: ' + Object.values(errors).join('\n'));
        fileInputPlotting.value = '';
      }
    });
  }
};
</script>