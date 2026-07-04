<template>
  <Dialog v-model:visible="visible" :header="`Update Jadwal Pemberangkatan: ${dudiName}`" modal class="w-full max-w-sm" @hide="form.reset()">
    <form @submit.prevent="submit" class="space-y-4 pt-2">
      
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Petugas Pemberangkatan (Guru)</label>
        <select v-model="form.guru_id" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-sky-400 bg-slate-50">
          <option :value="null" disabled>-- Pilih Petugas --</option>
          <option v-for="g in gurus" :key="g.id" :value="g.id">{{ g.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Tanggal Pemberangkatan</label>
        <input v-model="form.tanggal_pemberangkatan" type="date" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-sky-400 bg-slate-50" />
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-600 mb-1">Status Pemberangkatan</label>
        <select v-model="form.status" required class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-sky-400 bg-slate-50">
          <option value="Pending">Pending</option>
          <option value="Progress">Progress</option>
          <option value="Selesai">Selesai</option>
          <option value="Tolak">Tolak</option>
        </select>
      </div>

      <div class="pt-4 flex justify-end gap-2 border-t border-slate-100">
        <Button label="Batal" severity="secondary" text @click="visible = false" />
        <Button type="submit" label="Simpan Jadwal" :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-save'" :disabled="form.processing" class="rounded-xl" />
      </div>
    </form>
  </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

defineProps({ gurus: Array });
const visible = ref(false);
const dudiName = ref('');

const form = useForm({
  dudi_id: null,
  guru_id: null,
  tanggal_pemberangkatan: '',
  status: 'Pending'
});

const open = (dudi) => {
  dudiName.value = dudi.nama_dudi;
  form.dudi_id = dudi.id;
  
  if (dudi.pemberangkatan) {
    form.guru_id = dudi.pemberangkatan.guru_id;
    form.tanggal_pemberangkatan = dudi.pemberangkatan.tanggal_pemberangkatan;
    form.status = dudi.pemberangkatan.status;
  } else {
    form.guru_id = null;
    form.tanggal_pemberangkatan = '';
    form.status = 'Pending';
  }
  
  visible.value = true;
};

const submit = () => {
  form.post(route('admin.pemberangkatan.store'), { 
    preserveScroll: true, 
    onSuccess: () => visible.value = false 
  });
};

defineExpose({ open });
</script>