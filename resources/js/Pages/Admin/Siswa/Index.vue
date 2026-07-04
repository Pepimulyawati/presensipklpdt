<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="bg-white p-6 rounded-2xl border border-sky-100 shadow-2xs flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
          <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
            <i class="pi pi-user text-sky-500"></i> Manajemen Data Siswa PKL
          </h1>
          <p class="text-sm text-slate-500 mt-1">Kelola biodata siswa, plotting pembimbing, dan akun absensi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
          <Button @click="openModal()" icon="pi pi-plus" label="Tambah Siswa" class="p-button-sm bg-sky-500 border-none rounded-xl font-semibold" />
          <a :href="route('admin.siswa.master.template')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm px-4 py-2 rounded-xl font-semibold flex items-center gap-2 transition-all">
            <i class="pi pi-download"></i> Template Excel
          </a>
          <input type="file" ref="fileInputMaster" @change="handleMasterUpload" accept=".xlsx, .xls" class="hidden" />
          <Button @click="$refs.fileInputMaster.click()" icon="pi pi-file-excel" label="Import Siswa" class="p-button-sm p-button-success rounded-xl font-semibold bg-emerald-500 border-none" />
        </div>
      </div>

     
      <div class="bg-white p-4 rounded-2xl border border-sky-100 shadow-2xs flex items-center justify-between gap-4">
  <div class="relative w-full max-w-xs flex items-center">
    <!-- <i class="pi pi-search absolute left-3 text-slate-400 pointer-events-none z-10" /> -->
    <InputText 
      v-model="search" 
      @input="doSearch" 
      placeholder="Cari data siswa (Semua Kolom)..." 
      class="p-inputtext-sm w-full pl-9 rounded-xl border-sky-100 bg-sky-50/20" 
    />
  </div>
  <!--  -->
  <span class="text-xs text-slate-400 font-mono whitespace-nowrap">Total: {{ siswas.total }} Siswa</span>
</div>

      <div class="bg-white rounded-2xl border border-sky-100 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-sky-50/70 border-b border-sky-100 text-slate-600 font-semibold text-sm">
                <th class="p-4 text-center w-12">No</th>
                <th class="p-4">Identitas Siswa</th> 
                <th class="p-4">Kelas & Jurusan</th>
                <th class="p-4" style="min-width: 220px;">Pembimbing Guru (Inline Edit)</th>
                <th class="p-4 text-center">Status PKL</th>
                <th class="p-4 text-center w-28">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-sky-50 text-slate-700 text-sm">
              <tr v-for="(siswa, index) in siswas.data" :key="siswa.id" class="hover:bg-sky-50/10">
                <td class="p-4 text-center text-slate-400 font-mono">{{ siswas.from + index }}</td>
                <td class="p-4">
                  <div class="font-bold text-slate-900">{{ siswa.user?.name || siswa.nama_lengkap }}</div>
                  <div class="flex gap-2 mt-0.5 text-xs font-mono">
                    <span class="text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">NIS. {{ siswa.nis ?? '-' }}</span>
                    <span class="text-sky-600 bg-sky-50 px-1.5 py-0.5 rounded">NISN. {{ siswa.nisn }}</span>
                  </div>
                </td>
                <td class="p-4">
                  <div class="font-medium text-slate-700">{{ siswa.kelas }}</div>
                  <div class="text-xs text-slate-400">{{ siswa.konsentrasi_keahlian }}</div>
                </td>
                <td class="p-4">
                  <Dropdown 
                    v-model="siswa.guru_id" 
                    :options="gurus" 
                    optionLabel="nama_guru" 
                    optionValue="id" 
                    filter 
                    placeholder="Pilih Pembimbing" 
                    @change="updateGuruInline(siswa.id, siswa.guru_id)"
                    class="w-full p-inputtext-sm rounded-lg border-sky-100 text-xs"
                  >
                    <template #option="slotProps">
                      <span class="text-xs">{{ slotProps.option.nama_guru || slotProps.option.user?.name }} (NIP. {{ slotProps.option.nip }})</span>
                    </template>
                  </Dropdown>
                </td>
                <td class="p-4 text-center">
                  <span :class="[
                    'px-2.5 py-1 text-xs rounded-full font-semibold capitalize',
                    siswa.status_pkl === 'aktif' ? 'bg-amber-50 text-amber-600 border border-amber-100' : 
                    siswa.status_pkl === 'selesai' ? 'bg-emerald-50 text-emerald-600 border border-amber-100' : 'bg-slate-50 text-slate-500 border border-slate-100'
                  ]">{{ siswa.status_pkl }}</span>
                </td>
                <td class="p-4 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button @click="openModal(siswa)" class="p-2 bg-sky-50 text-sky-600 hover:bg-sky-500 hover:text-white rounded-xl transition-all duration-200"><i class="pi pi-pencil text-xs"></i></button>
                    <button @click="destroySiswa(siswa.id)" class="p-2 bg-rose-50 text-rose-600 hover:bg-rose-500 hover:text-white rounded-xl transition-all duration-200"><i class="pi pi-trash text-xs"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-4 bg-slate-50/50 border-t border-sky-100 flex items-center justify-between">
          <span class="text-xs text-slate-500">Halaman {{ siswas.current_page }} dari {{ siswas.last_page }}</span>
          <div class="flex gap-1">
            <Link v-for="(link, idx) in siswas.links" :key="idx" :href="link.url ?? '#'" v-html="link.label" :class="['px-3 py-1.5 text-xs rounded-lg transition-all', link.active ? 'bg-sky-500 text-white font-bold' : 'bg-white border border-sky-100 text-slate-600 hover:bg-sky-50', !link.url ? 'opacity-40 cursor-not-allowed' : '']" />
          </div>
        </div>
      </div>

      <Dialog v-model:visible="showModal" :header="form.id ? 'Edit Data Siswa' : 'Tambah Siswa Baru'" :modal="true" :breakpoints="{'960px': '75vw', '640px': '90vw'}" style="width: 450px" @hide="form.reset()">
        <form @submit.prevent="submit" class="space-y-3 mt-2">
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Siswa</label>
            <InputText v-model="form.nama_lengkap" required class="w-full p-inputtext-sm rounded-xl" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">NIS (Boleh Kosong)</label>
              <InputText v-model="form.nis" placeholder="Boleh dikosongkan" class="w-full p-inputtext-sm rounded-xl" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">NISN (Wajib)</label>
              <InputText v-model="form.nisn" required placeholder="10 digit NISN" class="w-full p-inputtext-sm rounded-xl" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">NIK KTP (Optional)</label>
            <InputText v-model="form.nik_ktp" placeholder="16 digit NIK" class="w-full p-inputtext-sm rounded-xl" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Guru Pembimbing</label>
           <Dropdown 
    v-model="siswa.guru_id" 
    :options="gurus" 
    optionLabel="nama_guru" 
    optionValue="id" 
    filter 
    placeholder="Pilih Pembimbing" 
    @change="updateGuruInline(siswa.id, siswa.guru_id)"
    class="w-full p-inputtext-sm rounded-lg border-sky-100 text-xs"
  >
    <template #option="slotProps">
      <span class="text-xs">{{ slotProps.option.nama_guru }} (NIP. {{ slotProps.option.nip }})</span>
    </template>
  </Dropdown>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Kelas</label>
              <InputText v-model="form.kelas" required placeholder="XII RPL 1" class="w-full p-inputtext-sm rounded-xl" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-600 mb-1">Jurusan</label>
              <InputText v-model="form.konsentrasi_keahlian" required placeholder="RPL" class="w-full p-inputtext-sm rounded-xl" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Email Akun</label>
            <InputText v-model="form.email" type="email" required class="w-full p-inputtext-sm rounded-xl" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Password {{ form.id ? '(Kosongkan jika tidak diubah)' : '' }}</label>
            <InputText v-model="form.password" type="password" :required="!form.id" class="w-full p-inputtext-sm rounded-xl" />
          </div>
          <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
            <Button type="button" label="Batal" class="p-button-text p-button-sm text-slate-600" @click="showModal = false" />
            <Button type="submit" label="Simpan Data" :disabled="form.processing" class="p-button-sm bg-sky-500 border-none" />
          </div>
        </form>
      </Dialog>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link, useForm } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';

const props = defineProps({ siswas: Object, gurus: Array, filters: Object });
const search = ref(props.filters.search || '');
const showModal = ref(false);
const fileInputMaster = ref(null);

// Form Arsitektur Terpendek via useForm Inertia
const form = useForm({
  id: null, nama_lengkap: '', guru_id: '', kelas: '', nisn: '', nis: '', nik_ktp: '', konsentrasi_keahlian: '', email: '', password: ''
});

// Real-time Global Search untuk Semua Kolom
const doSearch = () => {
  router.get(route('admin.siswa.index'), { search: search.value }, { preserveState: true, replace: true });
};

// Fungsi Open dinamis (Tambah & Edit)
const openModal = (data = null) => {
  if (data) {
    Object.assign(form, data);
    form.id = data.id;
    form.email = data.user?.email || '';
    form.password = '';
  } else {
    form.reset();
    form.id = null;
  }
  showModal.value = true;
};

// Submit Dinamis (POST jika baru, PUT jika edit)
const submit = () => {
  const url = form.id ? route('admin.siswa.update', form.id) : route('admin.siswa.store');
  const action = form.id ? 'put' : 'post';

  form[action](url, {
    onSuccess: () => {
      showModal.value = false;
      alert('Data berhasil disimpan!');
    },
    onError: (err) => alert(Object.values(err).join('\n'))
  });
};

// Update Guru Pembimbing secara Inline & Real-time

const updateGuruInline = (siswaId, guruId) => {
  router.put(route('admin.siswa.update-guru', siswaId), { guru_id: guruId }, {
    preserveScroll: true,
    onSuccess: () => {
      // Notifikasi opsional/real-time ramah cPanel tanpa re-load
    },
    onError: () => alert('Gagal memperbarui guru pembimbing.')
  });
};

const destroySiswa = (id) => {
  if (confirm('Hapus siswa secara permanen? Akun dan absensi akan ikut terhapus.')) {
    router.delete(route('admin.siswa.destroy', id));
  }
};

const handleMasterUpload = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('file_excel', file);
  router.post(route('admin.siswa.import'), formData, {
    forceFormData: true,
    onSuccess: () => { fileInputMaster.value.value = ''; alert('Import berhasil!'); },
    onError: (err) => { fileInputMaster.value.value = ''; alert(err.file_excel || 'Gagal import.'); }
  });
};

defineExpose({ openModal });
</script>