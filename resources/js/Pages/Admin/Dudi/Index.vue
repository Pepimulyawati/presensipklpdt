<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="bg-white p-6 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-building text-sky-500"></i> Data Mitra DUDI & Instruktur
          </h1>
          <p class="text-sm text-slate-500 mt-1">Manajemen tempat PKL, instruktur lapangan, dan status kerjasama (MoU).</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
          <a :href="route('admin.dudi.template')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
            <i class="pi pi-download"></i> Template Excel
          </a>
          <input type="file" ref="fileInput" @change="handleUpload" accept=".xlsx, .xls" class="hidden" />
          <p-button label="Import DUDI" icon="pi pi-file-excel" severity="success" class="p-button-sm rounded-xl" @click="fileInput.click()" />
          <p-button label="Tambah Mitra" icon="pi pi-plus" severity="info" class="p-button-sm rounded-xl" @click="modalRef.open()" />
        </div>
      </div>

      <div class="bg-white p-4 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full max-w-xs">
          <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
          <input v-model="search" @input="doSearch" type="text" placeholder="Cari nama DUDI atau zona..." class="pl-10 pr-4 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-sky-50/20 w-full" />
        </div>
        <span class="text-xs text-slate-400 font-mono bg-slate-50 px-3 py-1 rounded-full border border-slate-100">
          Total: {{ dudis.total || 0 }} Mitra DUDI
        </span>
      </div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <p-datatable :value="dudis.data" responsiveLayout="scroll" class="p-datatable-sm w-full">
          <p-column header="No" class="text-center w-12 font-mono text-slate-400">
            <template #body="slotProps">
              {{ (dudis.current_page - 1) * dudis.per_page + slotProps.index + 1 }}
            </template>
          </p-column>
          <p-column field="nama_dudi" header="Nama DUDI / Instansi" class="font-bold text-slate-900" />
          <p-column header="Alamat & Kontak">
            <template #body="slotProps">
              <div class="text-xs text-slate-600 max-w-xs truncate">{{ slotProps.data.alamat }}</div>
              <div class="text-xs text-slate-400 font-mono mt-0.5"><i class="pi pi-phone text-[10px]"></i> {{ slotProps.data.kontak }}</div>
            </template>
          </p-column>
          <p-column header="Instruktur Lapangan">
            <template #body="slotProps">
              <div v-if="slotProps.data.instrukturs?.length">
                <div v-for="ins in slotProps.data.instrukturs" :key="ins.id" class="mb-1 last:mb-0">
                  <span class="font-semibold text-slate-800">{{ ins.nama_instruktur }}</span>
                  <span class="text-xs text-slate-400"> ({{ ins.jabatan }})</span>
                </div>
              </div>
              <span v-else class="text-xs text-slate-300 italic">Belum ada instruktur</span>
            </template>
          </p-column>
          <p-column field="zona" header="Zona" class="text-center">
            <template #body="slotProps">
              <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-xs rounded-md font-medium uppercase">{{ slotProps.data.zona }}</span>
            </template>
          </p-column>
          <p-column field="status_mou" header="Status MoU" class="text-center">
            <template #body="slotProps">
              <span :class="[
                'px-2.5 py-1 text-xs rounded-full font-semibold capitalize border',
                slotProps.data.status_mou === 'aktif' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 
                slotProps.data.status_mou === 'proses' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-rose-50 text-rose-600 border-rose-100'
              ]">{{ slotProps.data.status_mou.replace('_', ' ') }}</span>
            </template>
          </p-column>
          <p-column header="Aksi" class="text-center w-28">
            <template #body="slotProps">
              <div class="flex justify-center gap-1">
                <p-button icon="pi pi-pencil" severity="secondary" text class="rounded-lg" @click="modalRef.open(slotProps.data)" />
                <p-button icon="pi pi-trash" severity="danger" text class="rounded-lg" @click="deleteDudi(slotProps.data.id)" />
              </div>
            </template>
          </p-column>
          <template #empty>
            <div class="p-12 text-center opacity-40">
              <i class="pi pi-inbox text-4xl mb-2"></i>
              <p>Belum ada data mitra DUDI.</p>
            </div>
          </template>
        </p-datatable>

        <div class="p-4 bg-slate-50/50 border-t border-sky-100 flex flex-col sm:flex-row items-center justify-between gap-4">
          <span class="text-xs text-slate-500">Menampilkan {{ dudis.from || 0 }} sampai {{ dudis.to || 0 }} dari {{ dudis.total || 0 }} data</span>
          <div class="flex gap-1">
            <Link v-for="(link, idx) in dudis.links" :key="idx" :href="link.url ?? '#'" v-html="link.label" :class="['px-3 py-1.5 text-xs rounded-lg transition-all border', link.active ? 'bg-sky-500 text-white border-sky-500 font-bold' : 'bg-white border-sky-100 text-slate-600 hover:bg-sky-50', !link.url ? 'opacity-40 cursor-not-allowed' : '']" />
          </div>
        </div>
      </div>
    </div>

    <ModalDudi ref="modalRef" />
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import ModalDudi from '@/Components/ModalDudi.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';

// Explicit Import Komponen PrimeVue untuk Bypass Bug Resolving
import PButton from 'primevue/button';
import PDatatable from 'primevue/datatable';
import PColumn from 'primevue/column';

const props = defineProps({ dudis: Object, filters: Object });
const search = ref(props.filters?.search || '');
const modalRef = ref(null);
const fileInput = ref(null);
const currentVersion = ref(null);
let versionInterval = null;

const doSearch = () => {
  router.get(route('admin.dudi.index'), { search: search.value }, { preserveState: true, replace: true, preserveScroll: true });
};

const handleUpload = (event) => {
  const file = event.target.files[0];
  if (!file || !confirm(`Import data dari "${file.name}"?`)) return (event.target.value = '');
  
  const fd = new FormData();
  fd.append('file_excel', file);
  router.post(route('admin.dudi.import'), fd, {
    forceFormData: true,
    onSuccess: () => { alert('Data Berhasil diimport!'); event.target.value = ''; },
    onError: (err) => { alert('Gagal: ' + (err.file_excel || 'Sistem error')); event.target.value = ''; }
  });
};

const deleteDudi = (id) => {
  if (confirm('Hapus mitra DUDI? Seluruh data instruktur terkait akan terhapus permanen.')) {
    router.delete(route('admin.dudi.destroy', id));
  }
};

// REAL-TIME BACKGROUND RELOAD
onMounted(async () => {
  try { const res = await axios.get('/dudi/version'); currentVersion.value = res.data.version; } catch (e) {}
  versionInterval = setInterval(async () => {
    try {
      const res = await axios.get('/dudi/version');
      if (currentVersion.value !== null && res.data.version !== currentVersion.value) {
        currentVersion.value = res.data.version;
        router.reload({ only: ['dudis'], preserveState: true });
      }
    } catch (e) {}
  }, 5000);
});

onUnmounted(() => clearInterval(versionInterval));
</script>