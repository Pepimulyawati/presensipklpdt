<template>
  <DashboardLayout>
    <div class="space-y-6">
      
    
        <div class="bg-white p-6 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-users text-sky-500"></i> Manajemen Data Guru Pembimbing
          </h1>
          <p class="text-sm text-slate-500 mt-1">Kelola data master, akun login, dan NIP instansi pembimbing PKL.</p>
        </div>
        
        <div class="flex items-center gap-2 w-full sm:w-auto">
        
            <input type="file" ref="fileInput" @change="handleCsvUpload" accept=".xlsx, .xls" class="hidden" />

<button @click="$refs.fileInput.click()" class="bg-emerald-500 hover:bg-emerald-600 text-white text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
  <i class="pi pi-file-excel"></i> Import Excel
</button>

          <button @click="openModalAdd" class="bg-sky-500 hover:bg-sky-600 text-white text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
            <i class="pi pi-plus"></i> Tambah Guru
          </button>
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-sky-100 shadow-2xs flex items-center justify-between">
        <input v-model="search" @input="doSearch" type="text" placeholder="Cari nama atau NIP guru..." class="px-4 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-sky-50/20 w-full max-w-xs" />
        <span class="text-xs text-slate-400 font-mono">Total: {{ gurus.total }} Guru</span>
      </div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-semibold text-sm">
                <th class="p-4 w-16 text-center">No</th>
                <th class="p-4">Nama Lengkap & NIP</th>
                <th class="p-4">Email Login</th>
                <th class="p-4">Dibuat Pada</th>
                <th class="p-4 text-center w-32">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-sm">
              <tr v-if="gurus.data.length === 0">
                <td colspan="5" class="p-8 text-center text-slate-400">Data guru tidak ditemukan atau masih kosong.</td>
              </tr>
              <tr v-for="(item, index) in gurus.data" :key="item.id" class="hover:bg-sky-50/10 transition-colors">
                <td class="p-4 text-center text-slate-400 font-mono">{{ gurus.from + index }}</td>
                <td class="p-4">
                  <div class="font-bold text-slate-900">{{ item.nama_guru }}</div>
                  <div class="text-xs text-slate-400 font-mono mt-0.5">NIP. {{ item.nip }}</div>
                </td>
                <td class="p-4 font-medium text-slate-600">{{ item.user?.email }}</td>
                <td class="p-4 text-xs text-slate-400">{{ formatDate(item.created_at) }}</td>
                <td class="p-4 text-center">
                  <div class="flex justify-center gap-2">
                    <button @click="openModalEdit(item)" class="p-2 text-sky-600 hover:bg-sky-50 rounded-lg" title="Edit Guru"><i class="pi pi-pencil"></i></button>
                    <button @click="deleteGuru(item.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg" title="Hapus Guru"><i class="pi pi-trash"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 bg-slate-50/50 border-t border-sky-100 flex items-center justify-between">
          <span class="text-xs text-slate-500">Halaman {{ gurus.current_page }} dari {{ gurus.last_page }}</span>
          <div class="flex gap-1">
            <Link v-for="(link, idx) in gurus.links" :key="idx" :href="link.url ?? '#'" v-html="link.label" :class="['px-3 py-1.5 text-xs rounded-lg transition-all', link.active ? 'bg-sky-500 text-white font-bold' : 'bg-white border border-sky-100 text-slate-600 hover:bg-sky-50', !link.url ? 'opacity-40 cursor-not-allowed' : '']" />
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 z-50 animate-fade-in">
        <div class="bg-white rounded-2xl border border-sky-100 w-full max-w-md shadow-xl overflow-hidden">
          <div class="p-5 border-b border-sky-50 flex justify-between items-center bg-sky-50/30">
            <h3 class="font-bold text-slate-800">{{ isEditMode ? 'Edit Data Guru Pembimbing' : 'Tambah Guru Pembimbing Baru' }}</h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="pi pi-times"></i></button>
          </div>
          
          <form @submit.prevent="submitForm" class="p-5 space-y-4">
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap beserta Gelar</label>
              <input v-model="form.nama_guru" type="text" required class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400" placeholder="Contoh: Drs. Hermawan, M.T." />
            </div>
            
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Nomor Induk Pegawai (NIP)</label>
              <input v-model="form.nip" type="text" required class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400" placeholder="Masukkan 18 digit NIP" />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Email Login Resmi</label>
              <input v-model="form.email" type="email" required class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400" placeholder="guru@sekolah.sch.id" />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Password Akun {{ isEditMode ? '(Kosongkan jika tidak diubah)' : '' }}</label>
              <input v-model="form.password" :required="!isEditMode" type="password" class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400" placeholder="Minimal 8 karakter unik" />
            </div>

            <div class="pt-2 flex justify-end gap-2 border-t border-sky-50">
              <button type="button" @click="showModal = false" class="px-4 py-2 text-xs bg-slate-100 text-slate-600 font-semibold rounded-xl hover:bg-slate-200">Batal</button>
              <button type="submit" class="px-4 py-2 text-xs bg-sky-500 text-white font-semibold rounded-xl hover:bg-sky-600 flex items-center gap-1">
                <i class="pi pi-check text-xs"></i> Simpan Data
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
  gurus: Object,
  filters: Object
});

const search = ref(props.filters.search || '');
const showModal = ref(false);
const isEditMode = ref(false);
const selectedId = ref(null);

const form = ref({
  nama_guru: '',
  nip: '',
  email: '',
  password: ''
});

// Fitur Pencarian Real-time
const doSearch = () => {
  router.get(route('admin.guru.index'), { search: search.value }, {
    preserveState: true,
    replace: true
  });
};

// Modal Tambah Data
const openModalAdd = () => {
  isEditMode.value = false;
  selectedId.value = null;
  form.value = { nama_guru: '', nip: '', email: '', password: '' };
  showModal.value = true;
};

// Modal Edit Data
const openModalEdit = (guru) => {
  isEditMode.value = true;
  selectedId.value = guru.id;
  form.value = {
    nama_guru: guru.nama_guru,
    nip: guru.nip,
    email: guru.user?.email || '',
    password: '' // dikosongkan secara default demi keamanan
  };
  showModal.value = true;
};

// Eksekusi Simpan Data (Store atau Update)
const submitForm = () => {
  if (isEditMode.value) {
    router.put(route('admin.guru.update', selectedId.value), form.value, {
      onSuccess: () => showModal.value = false
    });
  } else {
    router.post(route('admin.guru.store'), form.value, {
      onSuccess: () => showModal.value = false
    });
  }
};

// Hapus Data Guru
const deleteGuru = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus guru ini? Akun login guru bersangkutan juga akan ikut terhapus permanen.')) {
    router.delete(route('admin.guru.destroy', id));
  }
};

// Format Tanggal
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};



const fileInput = ref(null);

const handleCsvUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  if (confirm(`Apakah Anda yakin ingin mengimport data dari file ${file.name}?`)) {
    const formData = new FormData();
    formData.append('file_csv', file);

    router.post(route('admin.guru.import'), formData, {
      forceFormData: true,
      onSuccess: () => {
        alert('Proses import selesai!');
        fileInput.value = ''; // Reset input file
      },
      onError: (err) => {
        alert('Gagal mengimport data. Periksa format file Anda kembali.');
      }
    });
  }
};
</script>