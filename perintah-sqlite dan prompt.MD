📌 CHEAT SHEET SQLITE LARAVEL (TERMINAL INSTAN)

Salin dan tempel perintah di bawah ini langsung ke terminal VS Code Anda. Semua perintah sudah otomatis menggunakan format tabel kotak (.mode box) yang rapi.

1. Lihat Daftar Semua Tabel
sqlite3 database/database.sqlite ".mode box" ".tables"

2. Lihat Struktur Kolom / Tipe Data (Tabel siswas)
sqlite3 database/database.sqlite ".mode box" ".header on" "PRAGMA table_info(siswas);"

Optional lebih ringkas
sqlite3 database/database.sqlite ".mode box" ".header on" "SELECT name FROM pragma_table_info('siswas');"

3. Lihat Semua Isi Data (Tabel siswas)
sqlite3 database/database.sqlite ".mode box" ".header on" "SELECT * FROM siswas;"

4. Lihat Isi Data Terbatas (Hanya 5 Baris Teratas)
sqlite3 database/database.sqlite ".mode box" ".header on" "SELECT * FROM siswas LIMIT 5;"

5. Lihat Kolom Tertentu Saja (Hanya id & nama_lengkap)
sqlite3 database/database.sqlite ".mode box" ".header on" "SELECT id, nama_lengkap FROM siswas;"

Tips Tambahan:
Ganti kata "siswas" di paling belakang dengan nama tabel lain (seperti "users", "gurus", "
dudis") sesuai dengan tabel mana yang ingin Anda cek.



SISTEM PROMPT & ATURAN PENGEMBANGAN PROYEK (MOHON DIPATUHI):

1. Stack Proyek:z
   - Backend: Laravel dengan Laravel Orion (Manfaatkan controller bawaan Orion untuk CRUD, pakai hooks, policies, dan form requests).
   - Frontend: Inertia.js dengan Vue 3 (Composition API / Script Setup).
   - UI Library: PrimeVue.
   - tailwind css untuk layoting
   - minimalkan boilerplate
   - jangan gunakan factories karena kita akan menggunakan seeder nanti


   3. Gaya Penulisan Kode:
   - Gunakan `<script setup>` untuk Vue dan auto import di
   - Gunakan Composables jika ada logika yang bisa digunakan ulang.
   - Utamakan kode yang ringkas, aman (manfaatkan Laravel Policies untuk Orion), dan cepat (gunakan Inertia Lazy Props jika diperlukan).
   - optimalkan component agar mudah untuk maintain dan skalabel 
   

2. Aturan Form & Boilerplate (PENTING):
   - Proyek ini SUDAH memiliki komponen boilerplate bernama `FormBuilderPrime` yang menerima dua props: `:form` (objek useForm dari Inertia) dan `:fields` (array konfigurasi input).
   - JANGAN PERNAH men-generate elemen HTML form mentah atau komponen PrimeVue terpisah secara manual (seperti <InputText>, <Dropdown>, dll) di dalam file halaman utama (Pages).
   - Jika diminta membuat form, Anda HANYA BOLEH men-generate array konfigurasi `fields`, objek `useForm`, dan membungkusnya dengan komponen `<FormBuilderPrime :form="form" :fields="YOUR_FIELDS_ARRAY" @submit="YOUR_SUBMIT_FUNCTION" />`.


<!-- FIXME cara mengaktifkan php lokal dan server lokal  -->
E:\php\php.exe artisan serve --port=8000
E:\php\php.exe -S localhost:8000