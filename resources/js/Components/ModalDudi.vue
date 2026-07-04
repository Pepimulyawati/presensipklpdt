<template>
  <p-dialog v-model:visible="visible" :header="form.id ? 'Edit Data Mitra DUDI' : 'Tambah Mitra DUDI Baru'" modal class="w-full max-w-lg" @hide="form.reset()">
    <form @submit.prevent="submit" class="space-y-4 pt-2 max-h-[75vh] overflow-y-auto px-1">
      
      <div class="space-y-3">
        <p class="text-[10px] font-bold text-sky-600 uppercase tracking-widest border-b border-sky-50 pb-1">A. Informasi Perusahaan</p>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Nama Instansi / Perusahaan</label>
          <input v-model="form.nama_dudi" type="text" required class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-sky-50/10" placeholder="Contoh: PT. Maju Bersama" />
          <div v-if="form.errors.nama_dudi" class="text-rose-500 text-[10px] mt-1">{{ form.errors.nama_dudi }}</div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Alamat Lengkap</label>
          <textarea v-model="form.alamat" required rows="2" class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-sky-50/10" placeholder="Jl. Nama Jalan..."></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Kontak/No. Telp</label>
            <input v-model="form.kontak" type="text" required class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-sky-50/10" placeholder="021-xxxx" />
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Zona Wilayah</label>
            <select v-model="form.zona" class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-white">
              <option value="Dalam Kota">Dalam Kota</option>
              <option value="Luar Kota">Luar Kota</option>
            </select>
          </div>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-600 mb-1">Status MoU Kerja Sama</label>
          <select v-model="form.status_mou" class="w-full px-3 py-2 text-sm border border-sky-100 rounded-xl focus:outline-none focus:border-sky-400 bg-white">
            <option value="aktif">Aktif</option>
            <option value="proses">Dalam Proses</option>
            <option value="tidak_aktif">Tidak Aktif</option>
          </select>
        </div>
      </div>

      <div class="pt-2 space-y-3 border-t border-dashed border-sky-100">
        <div class="flex justify-between items-center border-b border-emerald-50 pb-1">
          <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">B. Instruktur Lapangan</p>
          <p-button type="button" icon="pi pi-plus" label="Tambah Instruktur" text class="p-button-xs text-xs" @click="addInstruktur" />
        </div>

        <div v-for="(ins, idx) in form.instrukturs" :key="idx" class="p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-2 relative">
          <p-button v-if="form.instrukturs.length > 1" type="button" icon="pi pi-times" severity="danger" text class="absolute top-1 right-1 !w-6 !h-6 p-0" @click="removeInstruktur(idx)" />
          
          <div>
            <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Nama Instruktur</label>
            <input v-model="ins.nama_instruktur" type="text" required class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-400" placeholder="Nama Lengkap & Gelar" />
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Jabatan</label>
              <input v-model="ins.jabatan" type="text" required class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-400" placeholder="Supervisor / HRD" />
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">No. HP</label>
              <input v-model="ins.kontak_instruktur" type="text" required class="w-full px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-emerald-400" placeholder="08xxx" />
            </div>
          </div>
        </div>
      </div>

      <div class="pt-4 flex justify-end gap-2 border-t border-sky-50">
        <p-button type="button" label="Batal" severity="secondary" text class="rounded-xl" @click="visible = false" />
        <p-button type="submit" :label="form.id ? 'Simpan Perubahan' : 'Simpan Data'" :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-check'" :disabled="form.processing" severity="info" class="rounded-xl" />
      </div>
    </form>
  </p-dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PDialog from 'primevue/dialog';
import PButton from 'primevue/button';

const visible = ref(false);

const form = useForm({
  id: null,
  nama_dudi: '',
  alamat: '',
  kontak: '',
  zona: 'Dalam Kota',
  status_mou: 'aktif',
  instrukturs: [{ nama_instruktur: '', jabatan: '', kontak_instruktur: '' }]
});

const open = (data = null) => {
  if (data) {
    Object.assign(form, data);
    form.instrukturs = data.instrukturs?.length 
      ? JSON.parse(JSON.stringify(data.instrukturs)) 
      : [{ nama_instruktur: '', jabatan: '', kontak_instruktur: '' }];
  } else {
    form.reset();
    form.id = null;
    form.instrukturs = [{ nama_instruktur: '', jabatan: '', kontak_instruktur: '' }];
  }
  visible.value = true;
};

const addInstruktur = () => {
  form.instrukturs.push({ nama_instruktur: '', jabatan: '', kontak_instruktur: '' });
};

const removeInstruktur = (index) => {
  form.instrukturs.splice(index, 1);
};

const submit = () => {
  const url = form.id ? route('admin.dudi.update', form.id) : route('admin.dudi.store');
  const method = form.id ? 'put' : 'post';

  form[method](url, {
    onSuccess: () => {
      visible.value = false;
      form.reset();
    }
  });
};

defineExpose({ open });
</script>