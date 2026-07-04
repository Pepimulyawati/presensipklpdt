<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="bg-white p-5 rounded-2xl border border-sky-100 shadow-2xs">
        <h1 class="text-xl font-bold text-slate-800"><i class="pi pi-car text-sky-500 mr-2"></i> Jadwal Pemberangkatan DUDI</h1>
        <p class="text-sm text-slate-500 mt-1">Atur jadwal pemberangkatan dan ploting petugas untuk setiap mitra DUDI.</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-sky-100 flex items-center gap-3">
          <div class="p-3 bg-sky-50 text-sky-500 rounded-xl"><i class="pi pi-building text-xl"></i></div>
          <div><p class="text-[10px] text-slate-500 font-bold">TOTAL DUDI</p><p class="text-xl font-black text-slate-800">{{ stats.total }}</p></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-100 flex items-center gap-3">
          <div class="p-3 bg-emerald-50 text-emerald-500 rounded-xl"><i class="pi pi-check-circle text-xl"></i></div>
          <div><p class="text-[10px] text-slate-500 font-bold">SELESAI</p><p class="text-xl font-black text-emerald-600">{{ stats.selesai }}</p></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-amber-100 flex items-center gap-3">
          <div class="p-3 bg-amber-50 text-amber-500 rounded-xl"><i class="pi pi-sync text-xl"></i></div>
          <div><p class="text-[10px] text-slate-500 font-bold">PROGRESS</p><p class="text-xl font-black text-amber-600">{{ stats.progress }}</p></div>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-rose-100 flex items-center gap-3">
          <div class="p-3 bg-rose-50 text-rose-500 rounded-xl"><i class="pi pi-clock text-xl"></i></div>
          <div><p class="text-[10px] text-slate-500 font-bold">BELUM DIJADWAL</p><p class="text-xl font-black text-rose-600">{{ stats.belum }}</p></div>
        </div>
      </div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-sky-100 flex flex-col sm:flex-row gap-3 justify-between items-center bg-slate-50/50">
          <InputText v-model="search" @input="doSearch" placeholder="Cari Nama DUDI atau Alamat..." class="p-inputtext-sm w-full max-w-xs rounded-xl" />

          <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <a :href="route('admin.pemberangkatan.download-template')" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all shadow-2xs">
              <i class="pi pi-download text-slate-400 text-[11px]"></i> Unduh Template
            </a>

            <label class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-emerald-600 border border-emerald-700 rounded-xl hover:bg-emerald-700 cursor-pointer transition-all shadow-2xs">
              <i class="pi pi-file-excel text-[11px]"></i> Import Excel
              <input type="file" ref="fileInput" class="hidden" accept=".xlsx, .xls" @change="handleExcelUpload" />
            </label>
          </div>
        </div>

        <div v-if="$page.props.errors.import" class="mx-4 mt-3 p-3 bg-rose-50 border border-rose-100 text-rose-700 rounded-xl text-xs font-semibold flex items-center gap-2">
          <i class="pi pi-exclamation-triangle"></i>
          <span>{{ $page.props.errors.import }}</span>
        </div>
        <div v-if="$page.props.flash?.success" class="mx-4 mt-3 p-3 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-xs font-semibold flex items-center gap-2">
          <i class="pi pi-check-circle"></i>
          <span>{{ $page.props.flash.success }}</span>
        </div>

        <DataTable :value="dudiData.data" responsiveLayout="scroll" class="p-datatable-sm w-full">
          <Column header="No" class="w-12 text-center text-slate-400 font-mono">
            <template #body="{ index }">{{ (dudiData.current_page - 1) * dudiData.per_page + index + 1 }}</template>
          </Column>
          
          <Column header="Nama Instansi & Alamat">
            <template #body="{ data }">
              <div class="whitespace-normal break-words max-w-[280px]">
                <div class="font-bold text-slate-900">{{ data.nama_dudi }}</div>
                <div class="text-xs text-slate-500 mt-0.5 leading-tight">{{ data.alamat }}</div>
              </div>
            </template>
          </Column>
          
          <Column header="Jadwal & Petugas Pemberangkatan">
            <template #body="{ data }">
              <div v-if="data.pemberangkatan" class="whitespace-normal">
                <div class="text-sm font-bold text-sky-700 mb-0.5">{{ data.pemberangkatan.tanggal_pemberangkatan }}</div>
                <div class="text-xs text-slate-600"><i class="pi pi-user text-[10px] mr-1 text-slate-400"></i> {{ data.pemberangkatan.guru?.name }}</div>
              </div>
              <span v-else class="text-xs italic text-rose-400 bg-rose-50 px-2 py-1 rounded-md border border-rose-100">Belum ada jadwal</span>
            </template>
          </Column>

          <Column header="Status" class="text-center w-36">
            <template #body="{ data }">
              <select 
                :value="data.pemberangkatan?.status ?? 'Pending'" 
                @change="updateStatus(data, $event.target.value)"
                :class="[
                  'px-2 py-1 text-[11px] rounded-lg font-semibold border uppercase tracking-wider bg-white cursor-pointer focus:outline-none focus:ring-1 focus:ring-sky-400 w-full max-w-[110px]',
                  (data.pemberangkatan?.status ?? 'Pending') === 'Selesai' ? 'text-emerald-600 border-emerald-200 bg-emerald-50/30' :
                  (data.pemberangkatan?.status ?? 'Progress') === 'Progress' ? 'text-amber-600 border-amber-200 bg-amber-50/30' :
                  (data.pemberangkatan?.status ?? 'Tolak') === 'Tolak' ? 'text-rose-600 border-rose-200 bg-rose-50/30' : 'text-slate-600 border-slate-200 bg-slate-50'
                ]">
                <option value="Pending">Pending</option>
                <option value="Progress">Progress</option>
                <option value="Selesai">Selesai</option>
                <option value="Tolak">Tolak</option>
              </select>
            </template>
          </Column>
          
          <Column header="Aksi" class="text-center w-28">
            <template #body="{ data }">
              <div class="flex justify-center gap-1">
                <Button icon="pi pi-eye" severity="secondary" text class="w-8 h-8 p-0 rounded-lg" @click="openDetail(data)" v-tooltip.top="'Lihat Detail'" />
                <Button :icon="data.pemberangkatan ? 'pi pi-pencil' : 'pi pi-calendar-plus'" :severity="data.pemberangkatan ? 'info' : 'success'" text class="w-8 h-8 p-0 rounded-lg" @click="modalRef.open(data)" v-tooltip.top="'Atur Jadwal'" />
              </div>
            </template>
          </Column>
        </DataTable>

        <div class="p-4 flex flex-wrap gap-1 justify-end bg-slate-50/50 border-t border-sky-100">
          <Link v-for="(link, i) in dudiData.links" :key="i" :href="link.url ?? '#'" preserve-scroll preserve-state v-html="link.label" 
            :class="['px-3 py-1.5 text-xs rounded-lg border transition-all', link.active ? 'bg-sky-500 text-white font-bold border-sky-500 shadow-md' : 'bg-white text-slate-600 hover:bg-sky-50', !link.url && 'opacity-40 pointer-events-none']" />
        </div>
      </div>
    </div>

    <FormModal ref="modalRef" :gurus="gurus" />

    <Dialog v-model:visible="detailVisible" header="Detail Informasi Mitra DUDI" modal class="w-full max-w-lg">
      <div v-if="selectedDudi" class="space-y-4">
        <div class="p-4 bg-sky-50/50 rounded-xl border border-sky-100">
          <h3 class="font-black text-lg text-sky-900">{{ selectedDudi.nama_dudi }}</h3>
          <p class="text-sm text-slate-600 mt-1 whitespace-normal leading-relaxed">{{ selectedDudi.alamat }}</p>
          <div class="flex gap-4 mt-3 text-xs font-mono text-slate-500">
            <span><i class="pi pi-phone mr-1"></i> {{ selectedDudi.kontak }}</span>
            <span class="uppercase"><i class="pi pi-map-marker mr-1"></i> Zona: {{ selectedDudi.zona }}</span>
          </div>
        </div>
        
        <div>
          <h4 class="text-xs font-bold text-slate-500 mb-2 border-b pb-1 uppercase">Daftar Instruktur Lapangan</h4>
          <div v-if="selectedDudi.instrukturs?.length" class="space-y-2">
            <div v-for="ins in selectedDudi.instrukturs" :key="ins.id" class="p-3 bg-slate-50 border border-slate-100 rounded-lg flex justify-between items-center">
              <div>
                <p class="font-bold text-sm text-slate-700">{{ ins.nama_instruktur }}</p>
                <p class="text-xs text-slate-500">{{ ins.jabatan }}</p>
              </div>
              <span class="text-xs font-mono text-slate-500"><i class="pi pi-whatsapp text-emerald-500"></i> {{ ins.kontak_instruktur }}</span>
            </div>
          </div>
          <div v-else class="text-sm italic text-slate-400">Belum ada instruktur terdaftar.</div>
        </div>
      </div>
    </Dialog>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import FormModal from './FormModal.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';

import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';

const fileInput = ref(null);

const handleExcelUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const form = useForm({ file: file });
  form.post(route('admin.pemberangkatan.import'), {
    preserveScroll: true,
    onSuccess: () => { if (fileInput.value) fileInput.value.value = ''; },
    onError: () => { if (fileInput.value) fileInput.value.value = ''; }
  });
};

const updateStatus = (dudi, newStatus) => {
  router.post(route('admin.pemberangkatan.store'), {
    dudi_id: dudi.id,
    status: newStatus,
    guru_id: dudi.pemberangkatan?.guru_id ?? null,
    tanggal_pemberangkatan: dudi.pemberangkatan?.tanggal_pemberangkatan ?? null
  }, { 
    preserveScroll: true,
    preserveState: true 
  });
};

const props = defineProps({ dudiData: Object, gurus: Array, stats: Object, filters: Object });

const search = ref(props.filters?.search || '');
const doSearch = () => {
  router.get(route('admin.pemberangkatan.index'), { search: search.value }, { preserveState: true, preserveScroll: true, replace: true });
};

const modalRef = ref(null);
const detailVisible = ref(false);
const selectedDudi = ref(null);

const openDetail = (data) => {
  selectedDudi.value = data;
  detailVisible.value = true;
};

// Real-Time Reload Berdasarkan Perubahan Pemberangkatan
let versionInterval = null;
let currentVersion = null;
onMounted(async () => {
  try { currentVersion = (await axios.get('/pemberangkatan/version')).data.version; } catch (e) {}
  versionInterval = setInterval(async () => {
    try {
      const res = await axios.get('/pemberangkatan/version');
      if (currentVersion && res.data.version !== currentVersion) {
        currentVersion = res.data.version;
        router.reload({ only: ['dudiData', 'stats'], preserveState: true, preserveScroll: true });
      }
    } catch (e) {}
  }, 5000);
});
onUnmounted(() => clearInterval(versionInterval));
</script>