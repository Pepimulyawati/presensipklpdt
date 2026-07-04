<template>
  <DashboardLayout>
    <div class="max-w-2xl mx-auto space-y-6">
      
      <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-2 text-center">Presensi Lembur PKL</h2>
        <p class="text-gray-500 text-sm text-center mb-6">Aktifkan GPS dan ambil foto untuk mencatat jam lembur Anda.</p>
        
        <div v-if="lemburHariIni?.jam_pulang" class="bg-green-100 border border-green-200 text-green-800 p-4 rounded-xl mb-6 flex items-start gap-3">
          <i class="pi pi-check-circle text-2xl mt-0.5"></i>
          <div>
            <h3 class="font-bold">Lembur Selesai!</h3>
            <p class="text-sm">Masuk: <strong>{{ lemburHariIni.jam_masuk }}</strong> | Pulang: <strong>{{ lemburHariIni.jam_pulang }}</strong></p>
            <p class="text-sm mt-1 font-semibold text-green-700">Total Waktu Lembur: {{ durasiLembur }}</p>
          </div>
        </div>

        <div v-else-if="lemburHariIni" class="bg-blue-100 border border-blue-200 text-blue-800 p-4 rounded-xl mb-6 flex items-start gap-3">
          <i class="pi pi-info-circle text-2xl mt-0.5"></i>
          <div>
            <h3 class="font-bold">Sedang Lembur</h3>
            <p class="text-sm">Anda mulai lembur pada pukul <strong>{{ lemburHariIni.jam_masuk }}</strong>. Semangat!</p>
          </div>
        </div>
        
        <div v-if="!lemburHariIni?.jam_pulang">
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
                  <span v-if="formModel.latitude">Lat: {{ formModel.latitude.toFixed(6) }}, Lng: {{ formModel.longitude.toFixed(6) }}</span>
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
            
            <div class="grid grid-cols-2 gap-4">
              <button @click="submitPresensi('masuk')" :disabled="isMasukDisabled" class="p-4 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition disabled:opacity-40 disabled:cursor-not-allowed shadow-lg shadow-emerald-100">
                <i class="pi pi-sign-in block text-xl mb-1"></i> Mulai Lembur
              </button>
              
              <button @click="submitPresensi('pulang')" :disabled="isPulangDisabled" class="p-4 bg-orange-600 text-white rounded-xl font-semibold hover:bg-orange-700 transition disabled:opacity-40 disabled:cursor-not-allowed shadow-lg shadow-orange-100">
                <i class="pi pi-sign-out block text-xl mb-1"></i> Selesai Lembur
              </button>
            </div>
        </div>

      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
// DONE: ABSENSI LEMBUR SELESAI
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  lemburHariIni: Object
});

const videoRef = ref(null);
const streamTrack = ref(null);
const loadingLocation = ref(false);

const formModel = ref({
  foto: null,
  latitude: null,
  longitude: null
});

// Durasi Lembur Formatter
const durasiLembur = computed(() => {
  if (!props.lemburHariIni?.jumlah_menit) return '-';
  const menitTotal = props.lemburHariIni.jumlah_menit;
  const jam = Math.floor(menitTotal / 60);
  const menit = menitTotal % 60;
  return `${jam} Jam ${menit} Menit`;
});

// Validasi Tombol
const isMasukDisabled = computed(() => !!props.lemburHariIni || !formModel.value.foto || !formModel.value.latitude);
const isPulangDisabled = computed(() => !props.lemburHariIni || !!props.lemburHariIni.jam_pulang || !formModel.value.foto || !formModel.value.latitude);

// Kamera & Lokasi Logic
const initCamera = async () => {
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
    if (videoRef.value) {
      videoRef.value.srcObject = stream;
      streamTrack.value = stream.getTracks()[0];
    }
  } catch (e) { alert('Gagal mengakses kamera.'); }
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

const resetCamera = () => { formModel.value.foto = null; initCamera(); };

const getLocation = () => {
  if (!navigator.geolocation) return;
  loadingLocation.value = true;
  navigator.geolocation.getCurrentPosition(
    (pos) => { formModel.value.latitude = pos.coords.latitude; formModel.value.longitude = pos.coords.longitude; loadingLocation.value = false; },
    () => { loadingLocation.value = false; alert('Gagal mendapatkan lokasi GPS.'); },
    { enableHighAccuracy: true }
  );
};

// Submit ke rute (Pastikan rute ini dibuat di web.php)
const submitPresensi = (tipePresensi) => {
  router.post(route('siswa.lembur.store'), {
    tipe: tipePresensi,
    latitude: formModel.value.latitude,
    longitude: formModel.value.longitude,
    foto: formModel.value.foto 
  }, {
    onStart: () => loadingLocation.value = true,
    onFinish: () => loadingLocation.value = false,
    onError: (err) => alert('Gagal: ' + Object.values(err).join(', '))
  });
};

onMounted(() => { initCamera(); getLocation(); });
onBeforeUnmount(() => { if (streamTrack.value) streamTrack.value.stop(); });
</script>