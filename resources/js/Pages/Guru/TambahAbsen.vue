<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm transition-opacity">
    <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-xl">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-slate-800">Tambah Presensi</h2>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">
          <i class="pi pi-times text-lg"></i>
        </button>
      </div>
      
      <form @submit.prevent="submit">
        
        <div class="mb-4 relative">
          <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Siswa (Bimbingan)</label>
          
          <div class="relative">
            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Ketik nama siswa..." 
              class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-sky-500 focus:bg-white focus:ring-4 focus:ring-sky-500/10 transition-all"
              @focus="showDropdown = true"
            >
            
            <div v-if="showDropdown && filteredSiswas.length > 0" class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
              <div 
                v-for="s in filteredSiswas" :key="s.id" 
                @click="selectSiswa(s)"
                class="px-4 py-2.5 hover:bg-sky-50 hover:text-sky-700 cursor-pointer text-sm border-b border-slate-50 last:border-0 transition-colors"
                :class="{'bg-sky-50 text-sky-700 font-semibold': form.siswa_id === s.id}"
              >
                {{ s.name }}
              </div>
            </div>
            
            <div v-else-if="showDropdown && searchQuery && filteredSiswas.length === 0" class="absolute z-10 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-lg p-4 text-center text-sm text-slate-500">
              Siswa tidak ditemukan.
            </div>
          </div>
          <div v-if="form.errors.siswa_id" class="text-red-500 text-xs mt-1">{{ form.errors.siswa_id }}</div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-semibold text-slate-700 mb-2">Tanggal</label>
          <input v-model="form.tanggal" type="date" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-sky-500 focus:bg-white transition-all">
          <div v-if="form.errors.tanggal" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal }}</div>
        </div>

        <div class="mb-6">
          <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
          <select v-model="form.status" class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:border-sky-500 focus:bg-white transition-all">
            <option value="Hadir">Hadir</option>
            <option value="Izin">Izin</option>
            <option value="Sakit">Sakit</option>
            <option value="Alfa">Alfa</option>
          </select>
          <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">{{ form.errors.status }}</div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="$emit('close')" class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">Batal</button>
          <button type="submit" :disabled="form.processing" class="px-5 py-2.5 text-sm font-semibold text-white bg-sky-500 hover:bg-sky-600 rounded-xl transition-colors disabled:opacity-50 flex items-center gap-2">
            <i v-if="form.processing" class="pi pi-spinner pi-spin"></i>
            Simpan
          </button>
        </div>
      </form>
    </div>
    
    <div v-if="showDropdown" @click="showDropdown = false" class="fixed inset-0 z-0"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ 
  show: Boolean, 
  siswas: { type: Array, default: () => [] } 
});

const emit = defineEmits(['close']);

// Dapatkan tanggal hari ini untuk default value
const getToday = () => new Date().toISOString().split('T')[0];

const form = useForm({
  siswa_id: '',
  tanggal: getToday(),
  status: 'Hadir',
});

// Search Logic
const searchQuery = ref('');
const showDropdown = ref(false);

const filteredSiswas = computed(() => {
  if (!searchQuery.value) return props.siswas;
  return props.siswas.filter(s => s.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const selectSiswa = (siswa) => {
  form.siswa_id = siswa.id;
  searchQuery.value = siswa.name;
  showDropdown.value = false;
};

// Reset form saat modal dibuka
watch(() => props.show, (isOpen) => {
  if (isOpen) {
    form.reset();
    form.tanggal = getToday(); // Reset tanggal ke hari ini
    searchQuery.value = '';
    showDropdown.value = false;
  }
});

const submit = () => {
  form.post(route('guru.presensi.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      emit('close');
    }
  });
};
</script>