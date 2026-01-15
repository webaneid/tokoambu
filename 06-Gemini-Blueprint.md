Blueprint Migrasi: AI Product Studio Addon (Laravel Version) - Modular Edition

Blueprint ini dirancang menggunakan arsitektur Registry Pattern. Kamu bisa menambah fitur eksperimen baru hanya dengan membuat Class baru, sangat mudah untuk di-inject.

1. Arsitektur Modular (The Core)

Kita akan membagi instruksi menjadi dua jenis:

Base Style (Preset): Tampilan utama (Studio, Outdoor, Minimalis).

Modifiers (FX): Tambahan efek (Meja Kaca, Bayangan, Anti-Hardcover, dll).

2. Implementasi Backend (Laravel)

A. Kontrak Fitur (Interface)

Semua fitur baru wajib mengikuti struktur ini.

// app/Contracts/AiFeatureContract.php
namespace App\Contracts;

interface AiFeatureContract {
    public function getKey(): string;
    public function getPrompt(): string;
    public function getCategory(): string; // 'style' atau 'fx'
}


B. Service Class Modular (app/Services/AiStudioService.php)

namespace App\Services;

use App\Contracts\AiFeatureContract;

class AiStudioService {
    protected $features = [];

    public function __construct() {
        // Daftar fitur yang terdaftar (Bisa dipindah ke config/aistudio.php)
        $this->registerFeatures([
            new \App\AiFeatures\UprightStanding(),
            new \App\AiFeatures\PreserveThickness(),
            new \App\AiFeatures\GlassReflection(),
            new \App\AiFeatures\MacroCloseup(),
        ]);
    }

    public function registerFeatures(array $featureInstances) {
        foreach ($featureInstances as $instance) {
            $this->features[$instance->getKey()] = $instance;
        }
    }

    public function buildFinalPrompt($selectedKeys, $bgColor, $useSolid) {
        $promptParts = [];

        foreach ($selectedKeys as $key => $isActive) {
            if ($isActive && isset($this->features[$key])) {
                $promptParts[] = $this->features[$key]->getPrompt();
            }
        }

        $background = $useSolid 
            ? "ON A PURE FLAT SOLID {$bgColor} BACKGROUND. NO GRADIENTS."
            : "in a professional studio setting.";

        return "STRICT INSTRUCTION: Keep product identity identical. {$background}. " . 
               implode(" ", $promptParts) . 
               " Professional commercial photography look.";
    }

    // Untuk dikirim ke Frontend agar UI otomatis muncul
    public function getAvailableFeatures() {
        return collect($this->features)->map(fn($f) => [
            'key' => $f->getKey(),
            'category' => $f->getCategory()
        ]);
    }
}


C. Contoh Class Fitur (app/AiFeatures/UprightStanding.php)

namespace App\AiFeatures;

use App\Contracts\AiFeatureContract;

class UprightStanding implements AiFeatureContract {
    public function getKey(): string { return 'standing'; }
    public function getCategory(): string { return 'fx'; }
    public function getPrompt(): string {
        return "The product must be shown in an UPRIGHT STANDING position. Three-quarter view.";
    }
}


3. Cara Menambah Fitur Baru (Eksperimen)

Jika kamu ingin menambah fitur eksperimen baru, misalnya "Floating Effect" (Produk Melayang):

Langkah 1: Buat Class Baru

Buat file app/AiFeatures/FloatingEffect.php:

namespace App\AiFeatures;

class FloatingEffect implements \App\Contracts\AiFeatureContract {
    public function getKey(): string { return 'floating'; }
    public function getCategory(): string { return 'fx'; }
    public function getPrompt(): string {
        return "Make the product appear to be floating 5cm above the surface with a soft shadow below.";
    }
}


Langkah 2: Daftarkan di Service

Buka AiStudioService.php dan tambahkan new \App\AiFeatures\FloatingEffect(), ke dalam registerFeatures.

Langkah 3: Selesai

Backend sekarang secara otomatis mengenali kunci floating. Kamu tinggal kirim { "floating": true } dari frontend.

4. Skema Frontend Dinamis (Blade + Vue/React)

Agar kamu tidak perlu mengubah UI setiap kali nambah fitur, biarkan Laravel yang mengirim daftar fiturnya.

// Di Frontend (saat halaman dimuat)
const availableFeatures = await axios.get('/api/ai-features'); 

// Loop data ini untuk membuat tombol secara otomatis
availableFeatures.map(feature => {
    return <Checkbox label={feature.key} name={feature.key} />
});


5. Integrasi Color Picker

User memilih warna di input type="color", nilainya dikirim sebagai string HEX (misal: #FF5733). Laravel akan menerimanya dan memasukkannya langsung ke template prompt: "ON A PURE FLAT SOLID #FF5733 BACKGROUND". Gemini sangat akurat dalam membaca kode HEX.

6. Keamanan API

API Proxy: Frontend dilarang memanggil Gemini langsung. Semua lewat Laravel Controller.

Validation: Cek mime-type gambar (hanya boleh png/jpg).

Logging: Simpan setiap prompt dan hasil gambarnya di database untuk bahan riset eksperimen berikutnya.