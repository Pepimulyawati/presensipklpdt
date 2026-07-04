<template>
  <DashboardLayout>
    <div class="max-w-2xl mx-auto space-y-6">
      
      <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Presensi Digital PKL</h2>
        <p class="text-gray-500 text-sm text-center mb-6">Silakan ambil foto dan pastikan lokasi GPS aktif.</p>
        
        <div v-if="presensiHariIni && presensiHariIni.jam_pulang" class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-xl mb-6 flex items-start gap-3">
          <i class="pi pi-check-circle text-2xl mt-0.5"></i>
          <div>
            <h3 class="font-bold">Presensi Selesai!</h3>
            <p class="text-sm">Anda telah menyelesaikan presensi Masuk ({{ presensiHariIni.jam_masuk }}) dan Pulang ({{ presensiHariIni.jam_pulang }}) hari ini. Selamat beristirahat!</p>
          </div>
        </div>

        <div v-else-if="presensiHariIni" class="bg-blue-100 border border-blue-200 text-blue-800 p-4 rounded-xl mb-6 flex items-start gap-3">
          <i class="pi pi-info-circle text-2xl mt-0.5"></i>
          <div>
            <h3 class="font-bold">Sudah Presensi Masuk</h3>
            <p class="text-sm">Anda sudah melakukan presensi masuk pada pukul <strong>{{ presensiHariIni.jam_masuk }}</strong>. Silakan lakukan presensi pulang nanti setelah jam kerja selesai.</p>
          </div>
        </div>
        
        <div v-if="!presensiHariIni || !presensiHariIni.jam_pulang">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Status Kehadiran</label>
              <select v-model="formModel.status" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-slate-50 p-2.5">
                <option value="Hadir">Hadir</option>
                <option value="Sakit">Sakit</option>
                <option value="Izin">Izin</option>
              </select>
            </div>

            <div class="space-y-4">
              <div class="relative w-full aspect-video bg-slate-900 rounded-lg overflow-hidden flex items-center justify-center border border-slate-700">
                <video v-if="!formModel.foto" ref="videoRef" autoplay playsinline class="w-full h-full object-cover"></video>
                <img v-else :src="formModel.foto" class="w-full h-full object-cover" />
                
                <div v-if="loadingLocation" class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center text-white space-y-2">
                  <i class="pi pi-spin pi-spinner text-3xl"></i>
                  <span>Mengunci Koordinat GPS...</span>
                </div>
              </div>

              <div class="p-3 bg-slate-50 border rounded-lg flex items-center justify-between text-sm text-gray-600">
                <div class="flex items-center space-x-2">
                  <i class="pi pi-map-marker text-red-500 text-lg"></i>
                  <span v-if="formModel.latitude">
                    Lat: {{ formModel.latitude.toFixed(6) }}, Lng: {{ formModel.longitude.toFixed(6) }}
                  </span>
                  <span v-else class="text-amber-600 font-medium">Lokasi belum terdeteksi</span>
                </div>
                <button @click="getLocation" class="text-blue-600 hover:underline flex items-center gap-1">
                  <i class="pi pi-refresh"></i> Refresh
                </button>
              </div>

              <div class="flex justify-center space-x-4 mt-2">
                <button v-if="!formModel.foto" @click="takeSnapshot" class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition flex items-center gap-2">
                  <i class="pi pi-camera"></i> Ambil Foto
                </button>
                <button v-else @click="resetCamera" class="px-5 py-2.5 bg-gray-500 text-white rounded-lg font-medium hover:bg-gray-600 transition flex items-center gap-2">
                  <i class="pi pi-refresh"></i> Foto Ulang
                </button>
              </div>
            </div>
            
            <hr class="my-6 border-gray-200" />
        </div> <div class="grid grid-cols-2 gap-4">
          <button @click="submitPresensi('masuk')" :disabled="isMasukDisabled" class="p-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition disabled:opacity-40 disabled:cursor-not-allowed text-center shadow-lg shadow-emerald-100">
            <i class="pi pi-sign-in block text-xl mb-1"></i> Presensi Masuk
          </button>
          
          <button @click="submitPresensi('pulang')" :disabled="isPulangDisabled" class="p-4 bg-orange-600 text-white rounded-xl font-semibold hover:bg-orange-700 transition disabled:opacity-40 disabled:cursor-not-allowed text-center shadow-lg shadow-orange-100">
            <i class="pi pi-sign-out block text-xl mb-1"></i> Presensi Pulang
          </button>
        </div>

      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { router } from '@inertiajs/vue3';

// Props dari PresensiController
const props = defineProps({
  presensiHariIni: Object
});

const videoRef = ref(null);
const streamTrack = ref(null);
const loadingLocation = ref(false);

// Model penampung nilai form
const formModel = ref({
  status: 'Hadir',
  foto: null,
  latitude: null,
  longitude: null
});

// LOGIKA VALIDASI TOMBOL (Wajib foto & lokasi apa pun statusnya)
const isMasukDisabled = computed(() => {
  // Matikan jika sudah absen masuk ATAU (foto/lokasi kosong)
  return !!props.presensiHariIni || !formModel.value.foto || !formModel.value.latitude;
});

const isPulangDisabled = computed(() => {
  // Matikan jika belum absen masuk ATAU sudah absen pulang ATAU (foto/lokasi kosong)
  return !props.presensiHariIni || !!props.presensiHariIni.jam_pulang || !formModel.value.foto || !formModel.value.latitude;
});

// Fungsi Kamera
const initCamera = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
    if (videoRef.value) {
      videoRef.value.srcObject = stream;
      streamTrack.value = stream.getTracks()[0];
    }
  } catch (error) {
    alert('Gagal mengakses kamera. Pastikan izin diberikan!');
  }
};

const takeSnapshot = () => {
  if (!videoRef.value) return;
  const canvas = document.createElement('canvas');
  canvas.width = videoRef.value.videoWidth;
  canvas.height = videoRef.value.videoHeight;
  canvas.getContext('2d').drawImage(videoRef.value, 0, 0, canvas.width, canvas.height);
  
  formModel.value.foto = canvas.toDataURL('image/jpeg');
  if (streamTrack.value) streamTrack.value.stop();
};

const resetCamera = () => {
  formModel.value.foto = null;
  initCamera();
};

// Fungsi Lokasi
const getLocation = () => {
  if (!navigator.geolocation) return alert('Browser tidak mendukung GPS.');
  
  loadingLocation.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      formModel.value.latitude = pos.coords.latitude;
      formModel.value.longitude = pos.coords.longitude;
      loadingLocation.value = false;
    },
    (err) => {
      loadingLocation.value = false;
      alert('Gagal mendapatkan lokasi. Pastikan GPS aktif!');
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
};

// Fungsi Submit
const submitPresensi = (tipePresensi) => {
  router.post(route('siswa.presensi.store'), {
    tipe: tipePresensi,
    status: formModel.value.status,
    latitude: formModel.value.latitude,
    longitude: formModel.value.longitude,
    foto: formModel.value.foto 
  }, {
    onStart: () => loadingLocation.value = true,
    onFinish: () => loadingLocation.value = false,
    onError: (errors) => alert('Gagal: ' + Object.values(errors).join(', '))
  });
};

onMounted(() => {
  initCamera();
  getLocation();
});

onBeforeUnmount(() => {
  if (streamTrack.value) streamTrack.value.stop();
});
</script>