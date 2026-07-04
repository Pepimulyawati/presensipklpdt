<?php



namespace App\Http\Controllers; // <-- PERIKSA BARIS INI! Pastikan TIDAK ADA kata "\Admin"

use App\Http\Controllers\Controller; // <-- Ini agar class Controller utama bisa ditemukan


use App\Models\Siswa;
use App\Models\Guru;
use App\Models\User;
use App\Imports\SiswaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Exception;


use App\Models\Dudi;         // <-- Tambahkan baris ini
use App\Models\Instruktur;   // <-- Tambahkan baris ini

class AdminSiswaController extends Controller
{
    /**
     * READ: Daftar siswa + Filter + Pagination
     */

  
 public function index(Request $request)
    {
        // Tetap memuat relasi guru dari tabel gurus
        $query = Siswa::with(['user', 'guru']); 

        // Pencarian Global (Berlaku untuk semua kolom)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kelas', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%')
                  ->orWhere('nis', 'like', '%' . $search . '%')
                  ->orWhere('konsentrasi_keahlian', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $siswas = $query->paginate(10)->withQueryString();

        // KOREKSI UTAMA: Ambil data langsung dari tabel gurus beserta NIP asli
        $gurus = Guru::select('id', 'nama_guru', 'nip')->get();

        return Inertia::render('Admin/Siswa/Index', [
            'siswas' => $siswas,
            'gurus' => $gurus, // Mengirim ID tabel gurus yang asli
            'filters' => $request->only(['search'])
        ]);
    }

    // FUNGSI INLINE EDIT: Menggunakan validasi langsung ke tabel gurus
    public function updateGuruInline(Request $request, $id)
    {
        $request->validate([
            // Memastikan guru_id yang diinput valid dan ada di tabel gurus
            'guru_id' => 'required|exists:gurus,id' 
        ]);

        $siswa = Siswa::findOrFail($id);
        
        $siswa->update([
            'guru_id' => $request->guru_id 
        ]);

        return redirect()->back();
    }

    // HALAMAN KHUSUS PLOTTING PKL (Pindahan dari kode kemarin)
    public function plotting(Request $request)
    {
        $query = Siswa::with(['user', 'dudi', 'instruktur', 'guru.user']);

        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $siswas = $query->paginate(10)->withQueryString();
        $dudis = Dudi::orderBy('nama_dudi', 'asc')->get();
        $instrukturs = Instruktur::orderBy('nama_instruktur', 'asc')->get();

        return Inertia::render('Admin/Siswa/Plotting', [ // <-- Melayani file Plotting
            'siswas' => $siswas,
            'dudis' => $dudis,
            'instrukturs' => $instrukturs,
            'filters' => $request->only(['search'])
        ]);
    }

    public function updatePlotting(Request $request, $id)
    {
        $request->validate([
            'dudi_id' => 'nullable|exists:dudis,id',
            'instruktur_id' => 'nullable|exists:instrukturs,id',
        ]);

        $siswa = Siswa::findOrFail($id);
        $siswa->update([
            'dudi_id' => $request->dudi_id,
            'instruktur_id' => $request->instruktur_id,
        ]);

        return redirect()->back()->with('message', 'Plotting tempat PKL siswa berhasil diperbarui!');
    }
    /**
     * CREATE: Simpan manual dari form popup
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap'         => 'required|string|max:255',
            'guru_id'              => 'required|exists:gurus,id',
            'email'                => 'required|string|email|max:255|unique:users,email',
            'password'             => 'required|string|min:8',
            'nik_ktp'              => 'nullable|string|max:20',
            'nisn'                 => 'nullable|string|max:20',
            'kelas'                => 'required|string|max:50',
            'konsentrasi_keahlian' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name'     => $validated['nama_lengkap'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => 'siswa',
                ]);

                $user->siswa()->create([
                    'guru_id'              => $validated['guru_id'],
                    'nama_lengkap'         => $validated['nama_lengkap'],
                    'nik_ktp'              => $validated['nik_ktp'],
                    'nisn'                 => $validated['nisn'],
                    'kelas'                => $validated['kelas'],
                    'konsentrasi_keahlian' => $validated['konsentrasi_keahlian'],
                    'status_pkl'           => 'aktif',
                ]);
            });

            return redirect()->back()->with('message', 'Data Siswa Berhasil Ditambahkan!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    /**
     * EXCEL IMPORT
     */
   
 public function import(Request $request)
{
    set_time_limit(180); 
    $request->validate([
        'file_excel' => 'required|file|mimes:xlsx,xls|max:2048',
    ]);

    try {
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SiswaImport, $request->file('file_excel'));
        return redirect()->back()->with('message', 'Siswa berhasil diimport!');
    } catch (\Exception $e) {
        // Pecah pesan error berdasarkan baris baru (\n) agar menjadi array bersih
        $errorMessages = explode("\n", $e->getMessage());

        throw \Illuminate\Validation\ValidationException::withMessages([
            'file_excel' => $errorMessages
        ]);
    }
}

    /**
     * UPDATE
     */

    public function update(Request $request, Siswa $siswa) // Menggunakan Route Model Binding
{
    // 1. Validasi
    $validated = $request->validate([
        'nama_lengkap'         => 'required|string|max:255',
        'guru_id'              => 'required|exists:gurus,id',
        // KOREKSI UTAMA: Menggunakan Rule agar lebih clean dan aman dari typo
        'email'                => [
            'required', 
            'string', 
            'email', 
            'max:255', 
            Rule::unique('users')->ignore($siswa->user_id)
        ],
        'password'             => 'nullable|string|min:8',
        'nik_ktp'              => 'nullable|string|max:20',
        'nisn'                 => 'nullable|string|max:20',
        'kelas'                => 'required|string|max:50',
        'konsentrasi_keahlian' => 'required|string|max:255',
    ]);

    try {
        DB::transaction(function () use ($validated, $siswa, $request) {
            // 2. Update data User (akun login)
            $userPayload = [
                'name'  => $validated['nama_lengkap'],
                'email' => $validated['email'],
            ];

            // Hanya update password jika diisi
            if ($request->filled('password')) {
                $userPayload['password'] = Hash::make($request->password);
            }

            $siswa->user()->update($userPayload);

            // 3. Update data profil siswa
            // Kita bisa menggunakan $validated untuk keamanan (mass assignment)
            $siswa->update([
                'guru_id'              => $validated['guru_id'],
                'nama_lengkap'         => $validated['nama_lengkap'],
                'nik_ktp'              => $validated['nik_ktp'],
                'nisn'                 => $validated['nisn'],
                'kelas'                => $validated['kelas'],
                'konsentrasi_keahlian' => $validated['konsentrasi_keahlian'],
            ]);
        });

        return redirect()->back()->with('message', 'Data Siswa Berhasil Diperbarui!');

    } catch (\Exception $e) {
        // Jika terjadi error pada database, kembali dengan pesan error
        return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data.']);
    }
}
    /**
     * DELETE
     */
    public function destroy(Siswa $siswa): RedirectResponse
    {
        try {
            DB::transaction(function () use ($siswa) {
                $user = $siswa->user;
                $siswa->delete();
                $user?->delete();
            });

            return redirect()->back()->with('message', 'Data Siswa Berhasil Dihapus!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus data.']);
        }
    }



    /**
     * FEATURE: Proses Import Massal Penempatan PKL
     */
    public function importPlotting(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\SiswaPlottingImport, $request->file('file_excel'));
            return redirect()->back()->with('message', 'Plotting massal 1.000+ siswa berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('message', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * FEATURE: Download Template Excel Instan untuk Admin
     */
  
    /**
     * FEATURE: Download Template Excel yang SUDAH BERISI data seluruh siswa aktif
     */
    
    /**
     * FEATURE: Download Template Excel + AUTO DROPDOWN DATA VALIDATION
     */
  public function downloadTemplatePlotting()
    {
        // 1. Ambil seluruh data siswa untuk baris utama
        $dataSiswa = \App\Models\Siswa::with(['user', 'dudi', 'instruktur'])->get()->map(function($siswa) {
            return [
                'nisn'                  => $siswa->nisn,
                'nama_siswa_info_saja'  => $siswa->user?->name ?? '-',
                'nama_dudi'             => $siswa->dudi?->nama_dudi ?? '', 
                'nama_instruktur'       => $siswa->instruktur?->nama_instruktur ?? '',
            ];
        })->toArray();

        $header = [['nisn', 'nama_siswa_info_saja', 'nama_dudi', 'nama_instruktur']];
        $payload = array_merge($header, $dataSiswa);

        // 2. Ambil data aktual Master DUDI dan Instruktur yang sudah terinput di database
        $daftarDudi = \App\Models\Dudi::pluck('nama_dudi')->toArray();
        $daftarInstruktur = \App\Models\Instruktur::pluck('nama_instruktur')->toArray();

        $totalBarisData = count($payload);
        $totalDudi = count($daftarDudi);
        $totalInstruktur = count($daftarInstruktur);

        // 3. Pembuatan Excel Multi-Sheet (Sheet 1: Main Data, Sheet 2: Hidden Data Source)
        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($payload, $daftarDudi, $daftarInstruktur, $totalBarisData, $totalDudi, $totalInstruktur) implements 
                \Maatwebsite\Excel\Concerns\WithMultipleSheets
            {
                private $payload;
                private $daftarDudi;
                private $daftarInstruktur;
                private $totalBarisData;
                private $totalDudi;
                private $totalInstruktur;

                public function __construct($payload, $daftarDudi, $daftarInstruktur, $totalBarisData, $totalDudi, $totalInstruktur) 
                {
                    $this->payload = $payload;
                    $this->daftarDudi = $daftarDudi;
                    $this->daftarInstruktur = $daftarInstruktur;
                    $this->totalBarisData = $totalBarisData;
                    $this->totalDudi = $totalDudi;
                    $this->totalInstruktur = $totalInstruktur;
                }

                public function sheets(): array
                {
                    // Sheet Utama untuk Plotting Siswa
                    $mainSheet = new class($this->payload, $this->totalBarisData, $this->totalDudi, $this->totalInstruktur) implements 
                        \Maatwebsite\Excel\Concerns\FromArray, 
                        \Maatwebsite\Excel\Concerns\WithTitle,
                        \Maatwebsite\Excel\Concerns\WithEvents 
                    {
                        private $data; private $totalBaris; private $countDudi; private $countInstruktur;
                        public function __construct($data, $totalBaris, $countDudi, $countInstruktur) {
                            $this->data = $data; $this->totalBaris = $totalBaris; $this->countDudi = $countDudi; $this->countInstruktur = $countInstruktur;
                        }
                        public function array(): array { return $this->data; }
                        public function title(): string { return 'Plotting Siswa'; }

                        public function registerEvents(): array {
                            return [
                                \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                                    $sheet = $event->sheet->getDelegate();
                                    if ($this->totalBaris <= 1) return;

                                    // Validasi Dropdown Vertikal Kolom C (Nama DUDI) merujuk ke sheet 'DataReferensi'
                                    $validationC = $sheet->getCell('C2')->getDataValidation();
                                    $validationC->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                                    $validationC->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                                    $validationC->setAllowBlank(true);
                                    $validationC->setShowDropDown(true);
                                    $validationC->setErrorTitle('Input Salah');
                                    $validationC->setError('Nama DUDI harus dipilih dari list!');
                                    // Formula mengambil range vertikal dari sheet referensi
                                    $validationC->setFormula1('DataReferensi!$A$1:$A$' . max($this->countDudi, 1));

                                    // Validasi Dropdown Vertikal Kolom D (Nama Instruktur) merujuk ke sheet 'DataReferensi'
                                    $validationD = $sheet->getCell('D2')->getDataValidation();
                                    $validationD->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                                    $validationD->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                                    $validationD->setAllowBlank(true);
                                    $validationD->setShowDropDown(true);
                                    $validationD->setErrorTitle('Input Salah');
                                    $validationD->setError('Nama Instruktur harus dipilih dari list!');
                                    $validationD->setFormula1('DataReferensi!$B$1:$B$' . max($this->countInstruktur, 1));

                                    // Duplikasi dropdown vertikal ke seluruh baris data siswa
                                    for ($i = 2; $i <= $this->totalBaris; $i++) {
                                        $sheet->getCell("C{$i}")->setDataValidation(clone $validationC);
                                        $sheet->getCell("D{$i}")->setDataValidation(clone $validationD);
                                    }
                                }
                            ];
                        }
                    };

                    // Sheet Kedua sebagai Data Source Array Vertikal (Disembunyikan agar rapi)
                    $referenceSheet = new class($this->daftarDudi, $this->daftarInstruktur) implements 
                        \Maatwebsite\Excel\Concerns\FromArray, 
                        \Maatwebsite\Excel\Concerns\WithTitle,
                        \Maatwebsite\Excel\Concerns\WithEvents
                    {
                        private $dudi; private $instruktur;
                        public function __construct($dudi, $instruktur) { $this->dudi = $dudi; $this->instruktur = $instruktur; }
                        public function title(): string { return 'DataReferensi'; }
                        
                        public function array(): array {
                            $matrix = [];
                            $max = max(count($this->dudi), count($this->instruktur));
                            for ($i = 0; $i < $max; $i++) {
                                $matrix[] = [
                                    $this->dudi[$i] ?? '',
                                    $this->instruktur[$i] ?? ''
                                ];
                            }
                            return $matrix;
                        }

                        public function registerEvents(): array {
                            return [
                                \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                                    // Sembunyikan sheet agar user tidak bisa melihat/mengacak-acak list mentah data referensi
                                    $event->sheet->getDelegate()->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
                                }
                            ];
                        }
                    };

                    return [$mainSheet, $referenceSheet];
                }
            },
            'template_plotting_dropdown.xlsx'
        );
    }


    /**
     * FEATURE: Download Template Excel Kosong untuk Import Master Siswa Baru
     */
 
    /**
     * FEATURE: Download Template Excel yang SESUAI PERSIS dengan fungsi SiswaImport
     */
    public function downloadTemplateMaster()
    {
        // Menyusun susunan kolom (Header) yang dibaca oleh file SiswaImport Anda
        $headers = [
            [
                'nama_lengkap', 
                'email', 
                'password', 
                'nisn', 
                'nis', 
                'kelas', 
                'konsentrasi_keahlian', 
                'nip_guru'
            ],
            [
                'Ahmad Subagja', 
                'ahmad.subagja@sch.id', 
                'password123', 
                '0012345678', 
                '22231001', 
                'XII RPL 1', 
                'Rekayasa Perangkat Lunak', 
                '198503152010011002' // Contoh NIP Guru Pembimbing yang sudah ada di DB
            ],
            [
                'Siti Aminah', 
                'siti.aminah@sch.id', 
                '', // Password dikosongkan (otomatis di-set 'password123' oleh sistem Anda)
                '0012345679', 
                '', // NIS dikosongkan (diizinkan null sesuai request Anda)
                'XII RPL 1', 
                'Rekayasa Perangkat Lunak', 
                '198503152010011002'
            ]
        ];

        // Unduh otomatis file template resmi
        return \Maatwebsite\Excel\Facades\Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            public function __construct(array $data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_import_master_siswa.xlsx');
    }


    /**
     * FEATURE: Download Template Excel Resmi untuk Import DUDI & Instruktur
     */
    public function downloadTemplateDudi()
    {
        // Header kolom WAJIB sama persis dengan yang dibaca oleh DudiImport Anda
        $headers = [
            [
                'nama_dudi', 
                'alamat', 
                'kontak', 
                'zona', 
                'status_mou', 
                'nama_instruktur', 
                'jabatan', 
                'kontak_instruktur'
            ],
            [
                'PT. Solusi Teknologi Nusantara', 
                'Jl. Merdeka No. 123, Bandung', 
                '022-1234567', 
                'Dalam Kota', 
                'aktif', // Opsi: aktif, tidak_aktif, proses
                'Eko Prasetyo', 
                'Senior Developer', 
                '081234567890'
            ],
            [
                'PT. Solusi Teknologi Nusantara', // Nama DUDI sama = instruktur kedua di DUDI yang sama
                'Jl. Merdeka No. 123, Bandung', 
                '022-1234567', 
                'Dalam Kota', 
                'aktif', 
                'Rina Herawati', 
                'HRD Manager', 
                '081987654321'
            ],
            [
                'CV. Kreatif Media Utama', 
                'Jl. Asia Afrika No. 45, Bandung', 
                '-', 
                'Luar Kota', 
                'proses', 
                'Dedi Kurnia', 
                'Staff Desain', 
                '-'
            ]
        ];

        // Download otomatis sebagai file excel dudi
        return \Maatwebsite\Excel\Facades\Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            public function __construct(array $data) { $this->data = $data; }
            public function array(): array { return $this->data; }
        }, 'template_import_mitra_dudi.xlsx');
    }



}