<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center w-full h-full p-4" @click.self="$emit('close')">
    <div class="bg-white p-6 rounded-2xl w-full max-w-sm shadow-xl">
      <h3 class="font-bold text-lg text-slate-800 mb-4">Koreksi Presensi</h3>
      
      <form @submit.prevent="submit" class="space-y-4 text-sm">
        <div>
          <label class="block text-slate-600 mb-1">Status</label>
          <select v-model="form.status" class="w-full border border-gray-200 p-2 rounded-xl focus:border-sky-500 outline-none">
            <option v-for="s in ['Hadir', 'Izin', 'Sakit', 'Alfa']" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-slate-600 mb-1">Jam Masuk</label>
            <input type="time" step="1" v-model="form.jam_masuk" class="w-full border border-gray-200 p-2 rounded-xl focus:border-sky-500 outline-none" />
          </div>
          <div>
            <label class="block text-slate-600 mb-1">Jam Pulang</label>
            <input type="time" step="1" v-model="form.jam_pulang" class="w-full border border-gray-200 p-2 rounded-xl focus:border-sky-500 outline-none" />
          </div>
        </div>

        <div class="flex gap-2 justify-end pt-2">
          <button type="button" @click="$emit('close')" class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl font-medium hover:bg-slate-200">Batal</button>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-xl font-medium">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ show: Boolean, data: Object });
const emit = defineEmits(['close']);

const form = useForm({ status: '', jam_masuk: '', jam_pulang: '' });

// Auto-isi form saat modal dibuka
watch(() => props.data, (newVal) => {
  if (newVal) {
    form.status = newVal.status;
    form.jam_masuk = newVal.jam_masuk || '';
    form.jam_pulang = newVal.jam_pulang || '';
  }
}, { immediate: true });

const submit = () => {
  // Tambahkan safety check opsional (mencegah error jika data undefined)
  if (!props.data?.id) return; 

  form.put(route('guru.presensi.update', props.data.id), {
    onSuccess: () => emit('close'),
    preserveScroll: true
  });
};
</script>