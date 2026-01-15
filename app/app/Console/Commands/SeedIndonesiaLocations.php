<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Province;
use App\Models\City;
use App\Models\District;

class SeedIndonesiaLocations extends Command
{
    protected $signature = 'seed:indonesia-locations';
    protected $description = 'Seed Indonesia provinces, cities, and districts with postal codes';

    public function handle()
    {
        $this->info('🚀 Seeding Indonesia locations...');

        // Data dari BPS Indonesia (Badan Pusat Statistik)
        $data = [
            [
                'province' => ['id' => 11, 'name' => 'Aceh'],
                'cities' => [
                    ['id' => 1101, 'name' => 'Kabupaten Aceh Selatan', 'zip_code' => '67611'],
                    ['id' => 1102, 'name' => 'Kabupaten Aceh Tenggara', 'zip_code' => '67311'],
                    ['id' => 1103, 'name' => 'Kabupaten Aceh Timur', 'zip_code' => '67151'],
                    ['id' => 1104, 'name' => 'Kabupaten Aceh Jaya', 'zip_code' => '23611'],
                    ['id' => 1105, 'name' => 'Kabupaten Pidie', 'zip_code' => '24111'],
                    ['id' => 1106, 'name' => 'Kabupaten Aceh Utara', 'zip_code' => '24111'],
                    ['id' => 1201, 'name' => 'Kota Banda Aceh', 'zip_code' => '23111'],
                    ['id' => 1202, 'name' => 'Kota Sabang', 'zip_code' => '25411'],
                ]
            ],
            [
                'province' => ['id' => 12, 'name' => 'Sumatera Utara'],
                'cities' => [
                    ['id' => 1201, 'name' => 'Kabupaten Nias', 'zip_code' => '25811'],
                    ['id' => 1202, 'name' => 'Kabupaten Langkat', 'zip_code' => '20811'],
                    ['id' => 1203, 'name' => 'Kabupaten Asahan', 'zip_code' => '21211'],
                    ['id' => 1204, 'name' => 'Kabupaten Tapanuli Selatan', 'zip_code' => '22711'],
                    ['id' => 1207, 'name' => 'Kota Medan', 'zip_code' => '20111'],
                    ['id' => 1208, 'name' => 'Kota Pematangsiantar', 'zip_code' => '21111'],
                    ['id' => 1209, 'name' => 'Kota Sibolga', 'zip_code' => '22511'],
                    ['id' => 1210, 'name' => 'Kota Binjai', 'zip_code' => '20711'],
                ]
            ],
            [
                'province' => ['id' => 13, 'name' => 'Sumatera Barat'],
                'cities' => [
                    ['id' => 1301, 'name' => 'Kabupaten Pesisir Selatan', 'zip_code' => '25611'],
                    ['id' => 1302, 'name' => 'Kabupaten Solok', 'zip_code' => '27711'],
                    ['id' => 1303, 'name' => 'Kabupaten Sawahlunto/Sijunjung', 'zip_code' => '27811'],
                    ['id' => 1304, 'name' => 'Kabupaten Tanah Datar', 'zip_code' => '27311'],
                    ['id' => 1305, 'name' => 'Kabupaten Padang Pariaman', 'zip_code' => '25611'],
                    ['id' => 1306, 'name' => 'Kabupaten Agam', 'zip_code' => '26211'],
                    ['id' => 1307, 'name' => 'Kabupaten Lima Puluh Kota', 'zip_code' => '26411'],
                    ['id' => 1308, 'name' => 'Kota Padang', 'zip_code' => '25111'],
                ]
            ],
            [
                'province' => ['id' => 14, 'name' => 'Riau'],
                'cities' => [
                    ['id' => 1401, 'name' => 'Kabupaten Kuantan Singingi', 'zip_code' => '29611'],
                    ['id' => 1402, 'name' => 'Kabupaten Indragiri Hulu', 'zip_code' => '29511'],
                    ['id' => 1403, 'name' => 'Kabupaten Indragiri Hilir', 'zip_code' => '29211'],
                    ['id' => 1404, 'name' => 'Kabupaten Pelalawan', 'zip_code' => '28311'],
                    ['id' => 1405, 'name' => 'Kabupaten Siak', 'zip_code' => '28611'],
                    ['id' => 1406, 'name' => 'Kabupaten Kampar', 'zip_code' => '28711'],
                    ['id' => 1407, 'name' => 'Kabupaten Rokan Hulu', 'zip_code' => '28811'],
                    ['id' => 1501, 'name' => 'Kota Pekanbaru', 'zip_code' => '28111'],
                ]
            ],
            [
                'province' => ['id' => 15, 'name' => 'Jambi'],
                'cities' => [
                    ['id' => 1501, 'name' => 'Kabupaten Kerinci', 'zip_code' => '37411'],
                    ['id' => 1502, 'name' => 'Kabupaten Merangin', 'zip_code' => '37211'],
                    ['id' => 1503, 'name' => 'Kabupaten Sarolangun', 'zip_code' => '37311'],
                    ['id' => 1504, 'name' => 'Kabupaten Batanghari', 'zip_code' => '37111'],
                    ['id' => 1505, 'name' => 'Kabupaten Bungo', 'zip_code' => '37511'],
                    ['id' => 1506, 'name' => 'Kabupaten Tanjung Jabung Timur', 'zip_code' => '36111'],
                    ['id' => 1507, 'name' => 'Kabupaten Tanjung Jabung Barat', 'zip_code' => '36211'],
                    ['id' => 1601, 'name' => 'Kota Jambi', 'zip_code' => '36111'],
                ]
            ],
            [
                'province' => ['id' => 16, 'name' => 'Sumatera Selatan'],
                'cities' => [
                    ['id' => 1601, 'name' => 'Kabupaten Ogan Komering Ulu', 'zip_code' => '32111'],
                    ['id' => 1602, 'name' => 'Kabupaten Ogan Komering Ilir', 'zip_code' => '30711'],
                    ['id' => 1603, 'name' => 'Kabupaten Muara Enim', 'zip_code' => '31111'],
                    ['id' => 1604, 'name' => 'Kabupaten Lahat', 'zip_code' => '31411'],
                    ['id' => 1605, 'name' => 'Kabupaten Musi Rawas', 'zip_code' => '31511'],
                    ['id' => 1606, 'name' => 'Kabupaten Banyuasin', 'zip_code' => '30711'],
                    ['id' => 1607, 'name' => 'Kabupaten Ogan Ilir', 'zip_code' => '30711'],
                    ['id' => 1671, 'name' => 'Kota Palembang', 'zip_code' => '30111'],
                ]
            ],
            [
                'province' => ['id' => 17, 'name' => 'Bengkulu'],
                'cities' => [
                    ['id' => 1701, 'name' => 'Kabupaten Seluma', 'zip_code' => '38811'],
                    ['id' => 1702, 'name' => 'Kabupaten Rejang Lebong', 'zip_code' => '39211'],
                    ['id' => 1703, 'name' => 'Kabupaten Bengkulu Utara', 'zip_code' => '38711'],
                    ['id' => 1704, 'name' => 'Kabupaten Kaur', 'zip_code' => '38911'],
                    ['id' => 1705, 'name' => 'Kabupaten Lebong', 'zip_code' => '39311'],
                    ['id' => 1706, 'name' => 'Kabupaten Muko-Muko', 'zip_code' => '39111'],
                    ['id' => 1707, 'name' => 'Kabupaten Bengkulu Selatan', 'zip_code' => '38811'],
                    ['id' => 1771, 'name' => 'Kota Bengkulu', 'zip_code' => '38211'],
                ]
            ],
            [
                'province' => ['id' => 18, 'name' => 'Lampung'],
                'cities' => [
                    ['id' => 1801, 'name' => 'Kabupaten Lampung Selatan', 'zip_code' => '35311'],
                    ['id' => 1802, 'name' => 'Kabupaten Lampung Tengah', 'zip_code' => '34211'],
                    ['id' => 1803, 'name' => 'Kabupaten Lampung Utara', 'zip_code' => '34411'],
                    ['id' => 1804, 'name' => 'Kabupaten Way Kanan', 'zip_code' => '34711'],
                    ['id' => 1805, 'name' => 'Kabupaten Tulang Bawang', 'zip_code' => '34711'],
                    ['id' => 1806, 'name' => 'Kabupaten Pesisir Barat', 'zip_code' => '34511'],
                    ['id' => 1871, 'name' => 'Kota Bandar Lampung', 'zip_code' => '35111'],
                    ['id' => 1872, 'name' => 'Kota Metro', 'zip_code' => '34111'],
                ]
            ],
            [
                'province' => ['id' => 31, 'name' => 'DKI Jakarta'],
                'cities' => [
                    ['id' => 3101, 'name' => 'Jakarta Pusat', 'zip_code' => '12190'],
                    ['id' => 3102, 'name' => 'Jakarta Utara', 'zip_code' => '14110'],
                    ['id' => 3103, 'name' => 'Jakarta Barat', 'zip_code' => '11210'],
                    ['id' => 3104, 'name' => 'Jakarta Selatan', 'zip_code' => '12190'],
                    ['id' => 3105, 'name' => 'Jakarta Timur', 'zip_code' => '13210'],
                    ['id' => 3171, 'name' => 'Kepulauan Seribu', 'zip_code' => '14420'],
                ]
            ],
            [
                'province' => ['id' => 32, 'name' => 'Jawa Barat'],
                'cities' => [
                    ['id' => 3201, 'name' => 'Kabupaten Bogor', 'zip_code' => '16711'],
                    ['id' => 3202, 'name' => 'Kabupaten Sukabumi', 'zip_code' => '43311'],
                    ['id' => 3203, 'name' => 'Kabupaten Cianjur', 'zip_code' => '43211'],
                    ['id' => 3204, 'name' => 'Kabupaten Bandung', 'zip_code' => '40711'],
                    ['id' => 3205, 'name' => 'Kabupaten Garut', 'zip_code' => '44111'],
                    ['id' => 3206, 'name' => 'Kabupaten Tasikmalaya', 'zip_code' => '46411'],
                    ['id' => 3207, 'name' => 'Kabupaten Ciamis', 'zip_code' => '46411'],
                    ['id' => 3208, 'name' => 'Kabupaten Kuningan', 'zip_code' => '45611'],
                    ['id' => 3209, 'name' => 'Kabupaten Cirebon', 'zip_code' => '45411'],
                    ['id' => 3210, 'name' => 'Kabupaten Majalengka', 'zip_code' => '45411'],
                    ['id' => 3211, 'name' => 'Kabupaten Sumedang', 'zip_code' => '45311'],
                    ['id' => 3212, 'name' => 'Kabupaten Indramayu', 'zip_code' => '45211'],
                    ['id' => 3213, 'name' => 'Kabupaten Subang', 'zip_code' => '41211'],
                    ['id' => 3214, 'name' => 'Kabupaten Purwakarta', 'zip_code' => '41111'],
                    ['id' => 3215, 'name' => 'Kabupaten Karawang', 'zip_code' => '41311'],
                    ['id' => 3216, 'name' => 'Kabupaten Bekasi', 'zip_code' => '17811'],
                    ['id' => 3217, 'name' => 'Kabupaten Bandung Barat', 'zip_code' => '40711'],
                    ['id' => 3301, 'name' => 'Kota Bogor', 'zip_code' => '16111'],
                    ['id' => 3302, 'name' => 'Kota Sukabumi', 'zip_code' => '43111'],
                    ['id' => 3303, 'name' => 'Kota Bandung', 'zip_code' => '40111'],
                    ['id' => 3304, 'name' => 'Kota Cirebon', 'zip_code' => '45111'],
                    ['id' => 3305, 'name' => 'Kota Tasikmalaya', 'zip_code' => '46111'],
                    ['id' => 3306, 'name' => 'Kota Banjar', 'zip_code' => '46311'],
                    ['id' => 3307, 'name' => 'Kota Depok', 'zip_code' => '16411'],
                    ['id' => 3308, 'name' => 'Kota Cimahi', 'zip_code' => '40511'],
                    ['id' => 3309, 'name' => 'Kota Bekasi', 'zip_code' => '17111'],
                ]
            ],
            [
                'province' => ['id' => 33, 'name' => 'Jawa Tengah'],
                'cities' => [
                    ['id' => 3301, 'name' => 'Kabupaten Cilacap', 'zip_code' => '53211'],
                    ['id' => 3302, 'name' => 'Kabupaten Banyumas', 'zip_code' => '53111'],
                    ['id' => 3303, 'name' => 'Kabupaten Purbalingga', 'zip_code' => '53311'],
                    ['id' => 3304, 'name' => 'Kabupaten Banjarnegara', 'zip_code' => '53411'],
                    ['id' => 3305, 'name' => 'Kabupaten Kebumen', 'zip_code' => '54311'],
                    ['id' => 3306, 'name' => 'Kabupaten Purworejo', 'zip_code' => '54111'],
                    ['id' => 3307, 'name' => 'Kabupaten Wonosobo', 'zip_code' => '54611'],
                    ['id' => 3308, 'name' => 'Kabupaten Magelang', 'zip_code' => '56511'],
                    ['id' => 3309, 'name' => 'Kabupaten Boyolali', 'zip_code' => '57311'],
                    ['id' => 3310, 'name' => 'Kabupaten Klaten', 'zip_code' => '57411'],
                    ['id' => 3311, 'name' => 'Kabupaten Sukoharjo', 'zip_code' => '57511'],
                    ['id' => 3312, 'name' => 'Kabupaten Wonogiri', 'zip_code' => '57611'],
                    ['id' => 3313, 'name' => 'Kabupaten Karanganyar', 'zip_code' => '57711'],
                    ['id' => 3314, 'name' => 'Kabupaten Sragen', 'zip_code' => '57811'],
                    ['id' => 3315, 'name' => 'Kabupaten Grobogan', 'zip_code' => '58111'],
                    ['id' => 3316, 'name' => 'Kabupaten Blora', 'zip_code' => '58211'],
                    ['id' => 3317, 'name' => 'Kabupaten Rembang', 'zip_code' => '59211'],
                    ['id' => 3318, 'name' => 'Kabupaten Pati', 'zip_code' => '59111'],
                    ['id' => 3319, 'name' => 'Kabupaten Kudus', 'zip_code' => '59311'],
                    ['id' => 3320, 'name' => 'Kabupaten Jepara', 'zip_code' => '59411'],
                    ['id' => 3321, 'name' => 'Kabupaten Demak', 'zip_code' => '59511'],
                    ['id' => 3322, 'name' => 'Kabupaten Semarang', 'zip_code' => '50711'],
                    ['id' => 3323, 'name' => 'Kabupaten Temanggung', 'zip_code' => '56211'],
                    ['id' => 3324, 'name' => 'Kabupaten Kendal', 'zip_code' => '51311'],
                    ['id' => 3325, 'name' => 'Kabupaten Batang', 'zip_code' => '51411'],
                    ['id' => 3326, 'name' => 'Kabupaten Pekalongan', 'zip_code' => '51411'],
                    ['id' => 3327, 'name' => 'Kabupaten Pemalang', 'zip_code' => '52211'],
                    ['id' => 3328, 'name' => 'Kabupaten Tegal', 'zip_code' => '52111'],
                    ['id' => 3329, 'name' => 'Kabupaten Brebes', 'zip_code' => '52211'],
                    ['id' => 3371, 'name' => 'Kota Magelang', 'zip_code' => '56111'],
                    ['id' => 3372, 'name' => 'Kota Surakarta', 'zip_code' => '57111'],
                    ['id' => 3373, 'name' => 'Kota Salatiga', 'zip_code' => '50711'],
                    ['id' => 3374, 'name' => 'Kota Semarang', 'zip_code' => '50111'],
                    ['id' => 3375, 'name' => 'Kota Pekalongan', 'zip_code' => '51111'],
                    ['id' => 3376, 'name' => 'Kota Tegal', 'zip_code' => '52111'],
                ]
            ],
            [
                'province' => ['id' => 34, 'name' => 'DI Yogyakarta'],
                'cities' => [
                    ['id' => 3401, 'name' => 'Kabupaten Sleman', 'zip_code' => '55511'],
                    ['id' => 3402, 'name' => 'Kabupaten Bantul', 'zip_code' => '55711'],
                    ['id' => 3403, 'name' => 'Kabupaten Gunung Kidul', 'zip_code' => '55811'],
                    ['id' => 3404, 'name' => 'Kabupaten Kulonprogo', 'zip_code' => '55611'],
                    ['id' => 3471, 'name' => 'Kota Yogyakarta', 'zip_code' => '55111'],
                ]
            ],
            [
                'province' => ['id' => 35, 'name' => 'Jawa Timur'],
                'cities' => [
                    ['id' => 3501, 'name' => 'Kabupaten Pacitan', 'zip_code' => '63511'],
                    ['id' => 3502, 'name' => 'Kabupaten Ponorogo', 'zip_code' => '63411'],
                    ['id' => 3503, 'name' => 'Kabupaten Trenggalek', 'zip_code' => '66211'],
                    ['id' => 3504, 'name' => 'Kabupaten Tulungagung', 'zip_code' => '66211'],
                    ['id' => 3505, 'name' => 'Kabupaten Blitar', 'zip_code' => '66111'],
                    ['id' => 3506, 'name' => 'Kabupaten Kediri', 'zip_code' => '64111'],
                    ['id' => 3507, 'name' => 'Kabupaten Malang', 'zip_code' => '65611'],
                    ['id' => 3508, 'name' => 'Kabupaten Lumajang', 'zip_code' => '67311'],
                    ['id' => 3509, 'name' => 'Kabupaten Jember', 'zip_code' => '68111'],
                    ['id' => 3510, 'name' => 'Kabupaten Banyuwangi', 'zip_code' => '68411'],
                    ['id' => 3511, 'name' => 'Kabupaten Bondowoso', 'zip_code' => '68211'],
                    ['id' => 3512, 'name' => 'Kabupaten Situbondo', 'zip_code' => '68311'],
                    ['id' => 3513, 'name' => 'Kabupaten Probolinggo', 'zip_code' => '67211'],
                    ['id' => 3514, 'name' => 'Kabupaten Pasuruan', 'zip_code' => '67111'],
                    ['id' => 3515, 'name' => 'Kabupaten Sidoarjo', 'zip_code' => '61211'],
                    ['id' => 3516, 'name' => 'Kabupaten Mojokerto', 'zip_code' => '61311'],
                    ['id' => 3517, 'name' => 'Kabupaten Jombang', 'zip_code' => '61411'],
                    ['id' => 3518, 'name' => 'Kabupaten Nganjuk', 'zip_code' => '64411'],
                    ['id' => 3519, 'name' => 'Kabupaten Madiun', 'zip_code' => '63311'],
                    ['id' => 3520, 'name' => 'Kabupaten Magetan', 'zip_code' => '63311'],
                    ['id' => 3521, 'name' => 'Kabupaten Ngawi', 'zip_code' => '63811'],
                    ['id' => 3522, 'name' => 'Kabupaten Bojonegoro', 'zip_code' => '62111'],
                    ['id' => 3523, 'name' => 'Kabupaten Tuban', 'zip_code' => '62311'],
                    ['id' => 3524, 'name' => 'Kabupaten Lamongan', 'zip_code' => '62211'],
                    ['id' => 3525, 'name' => 'Kabupaten Gresik', 'zip_code' => '61111'],
                    ['id' => 3526, 'name' => 'Kabupaten Bangkalan', 'zip_code' => '69111'],
                    ['id' => 3527, 'name' => 'Kabupaten Sampang', 'zip_code' => '69211'],
                    ['id' => 3528, 'name' => 'Kabupaten Pamekasan', 'zip_code' => '69311'],
                    ['id' => 3529, 'name' => 'Kabupaten Sumenep', 'zip_code' => '69411'],
                    ['id' => 3571, 'name' => 'Kota Kediri', 'zip_code' => '64111'],
                    ['id' => 3572, 'name' => 'Kota Blitar', 'zip_code' => '66111'],
                    ['id' => 3573, 'name' => 'Kota Malang', 'zip_code' => '65111'],
                    ['id' => 3574, 'name' => 'Kota Probolinggo', 'zip_code' => '67211'],
                    ['id' => 3575, 'name' => 'Kota Pasuruan', 'zip_code' => '67111'],
                    ['id' => 3576, 'name' => 'Kota Mojokerto', 'zip_code' => '61311'],
                    ['id' => 3577, 'name' => 'Kota Madiun', 'zip_code' => '63111'],
                    ['id' => 3578, 'name' => 'Kota Surabaya', 'zip_code' => '60111'],
                    ['id' => 3579, 'name' => 'Kota Batu', 'zip_code' => '65311'],
                ]
            ],
            [
                'province' => ['id' => 36, 'name' => 'Banten'],
                'cities' => [
                    ['id' => 3601, 'name' => 'Kabupaten Pandeglang', 'zip_code' => '42211'],
                    ['id' => 3602, 'name' => 'Kabupaten Lebak', 'zip_code' => '42311'],
                    ['id' => 3603, 'name' => 'Kabupaten Tangerang', 'zip_code' => '15111'],
                    ['id' => 3604, 'name' => 'Kabupaten Serang', 'zip_code' => '42111'],
                    ['id' => 3671, 'name' => 'Kota Tangerang', 'zip_code' => '15111'],
                    ['id' => 3672, 'name' => 'Kota Cilegon', 'zip_code' => '42411'],
                    ['id' => 3673, 'name' => 'Kota Serang', 'zip_code' => '42111'],
                    ['id' => 3674, 'name' => 'Kota Tangerang Selatan', 'zip_code' => '15311'],
                ]
            ],
            [
                'province' => ['id' => 51, 'name' => 'Bali'],
                'cities' => [
                    ['id' => 5101, 'name' => 'Kabupaten Jembrana', 'zip_code' => '82211'],
                    ['id' => 5102, 'name' => 'Kabupaten Tabanan', 'zip_code' => '82111'],
                    ['id' => 5103, 'name' => 'Kabupaten Badung', 'zip_code' => '80211'],
                    ['id' => 5104, 'name' => 'Kabupaten Gianyar', 'zip_code' => '80511'],
                    ['id' => 5105, 'name' => 'Kabupaten Klungkung', 'zip_code' => '80711'],
                    ['id' => 5106, 'name' => 'Kabupaten Bangli', 'zip_code' => '80711'],
                    ['id' => 5171, 'name' => 'Kota Denpasar', 'zip_code' => '80111'],
                ]
            ],
            [
                'province' => ['id' => 52, 'name' => 'Nusa Tenggara Barat'],
                'cities' => [
                    ['id' => 5201, 'name' => 'Kabupaten Lombok Utara', 'zip_code' => '83711'],
                    ['id' => 5202, 'name' => 'Kabupaten Lombok Barat', 'zip_code' => '83311'],
                    ['id' => 5203, 'name' => 'Kabupaten Lombok Tengah', 'zip_code' => '83211'],
                    ['id' => 5204, 'name' => 'Kabupaten Lombok Timur', 'zip_code' => '83511'],
                    ['id' => 5205, 'name' => 'Kabupaten Sumbawa', 'zip_code' => '84411'],
                    ['id' => 5206, 'name' => 'Kabupaten Sumbawa Barat', 'zip_code' => '84511'],
                    ['id' => 5271, 'name' => 'Kota Mataram', 'zip_code' => '83111'],
                    ['id' => 5272, 'name' => 'Kota Bima', 'zip_code' => '84711'],
                ]
            ],
            [
                'province' => ['id' => 53, 'name' => 'Nusa Tenggara Timur'],
                'cities' => [
                    ['id' => 5301, 'name' => 'Kabupaten Kupang', 'zip_code' => '85211'],
                    ['id' => 5302, 'name' => 'Kabupaten Timor Tengah Utara', 'zip_code' => '85511'],
                    ['id' => 5303, 'name' => 'Kabupaten Timor Tengah Selatan', 'zip_code' => '85611'],
                    ['id' => 5304, 'name' => 'Kabupaten Timor Timur', 'zip_code' => '85811'],
                    ['id' => 5305, 'name' => 'Kabupaten Belu', 'zip_code' => '85711'],
                    ['id' => 5306, 'name' => 'Kabupaten Alor', 'zip_code' => '85811'],
                    ['id' => 5307, 'name' => 'Kabupaten Flores Timur', 'zip_code' => '86311'],
                    ['id' => 5308, 'name' => 'Kabupaten Ende', 'zip_code' => '86311'],
                    ['id' => 5309, 'name' => 'Kabupaten Ngada', 'zip_code' => '86311'],
                    ['id' => 5310, 'name' => 'Kabupaten Manggarai', 'zip_code' => '86211'],
                    ['id' => 5311, 'name' => 'Kabupaten Sumba Timur', 'zip_code' => '87311'],
                    ['id' => 5312, 'name' => 'Kabupaten Sumba Barat', 'zip_code' => '87411'],
                    ['id' => 5313, 'name' => 'Kabupaten Rote Ndao', 'zip_code' => '85711'],
                    ['id' => 5371, 'name' => 'Kota Kupang', 'zip_code' => '85111'],
                ]
            ],
            [
                'province' => ['id' => 61, 'name' => 'Kalimantan Barat'],
                'cities' => [
                    ['id' => 6101, 'name' => 'Kabupaten Sambas', 'zip_code' => '78411'],
                    ['id' => 6102, 'name' => 'Kabupaten Mempawah', 'zip_code' => '78311'],
                    ['id' => 6103, 'name' => 'Kabupaten Sanggau', 'zip_code' => '78511'],
                    ['id' => 6104, 'name' => 'Kabupaten Ketapang', 'zip_code' => '78811'],
                    ['id' => 6105, 'name' => 'Kabupaten Sintang', 'zip_code' => '78611'],
                    ['id' => 6106, 'name' => 'Kabupaten Kapuas Hulu', 'zip_code' => '78711'],
                    ['id' => 6107, 'name' => 'Kabupaten Bengkayang', 'zip_code' => '78711'],
                    ['id' => 6171, 'name' => 'Kota Pontianak', 'zip_code' => '78111'],
                    ['id' => 6172, 'name' => 'Kota Singkawang', 'zip_code' => '79111'],
                ]
            ],
            [
                'province' => ['id' => 62, 'name' => 'Kalimantan Tengah'],
                'cities' => [
                    ['id' => 6201, 'name' => 'Kabupaten Kotawaringin Barat', 'zip_code' => '73411'],
                    ['id' => 6202, 'name' => 'Kabupaten Kotawaringin Timur', 'zip_code' => '73311'],
                    ['id' => 6203, 'name' => 'Kabupaten Kapuas', 'zip_code' => '73811'],
                    ['id' => 6204, 'name' => 'Kabupaten Barito Selatan', 'zip_code' => '71611'],
                    ['id' => 6205, 'name' => 'Kabupaten Barito Utara', 'zip_code' => '73811'],
                    ['id' => 6206, 'name' => 'Kabupaten Sukamara', 'zip_code' => '73411'],
                    ['id' => 6207, 'name' => 'Kabupaten Lamandau', 'zip_code' => '74411'],
                    ['id' => 6208, 'name' => 'Kabupaten Seruyan', 'zip_code' => '74211'],
                    ['id' => 6209, 'name' => 'Kabupaten Katingan', 'zip_code' => '74711'],
                    ['id' => 6210, 'name' => 'Kabupaten Pulang Pisau', 'zip_code' => '74711'],
                    ['id' => 6211, 'name' => 'Kabupaten Gunung Mas', 'zip_code' => '74911'],
                    ['id' => 6212, 'name' => 'Kabupaten Barito Timur', 'zip_code' => '74711'],
                    ['id' => 6271, 'name' => 'Kota Palangkaraya', 'zip_code' => '73111'],
                ]
            ],
            [
                'province' => ['id' => 63, 'name' => 'Kalimantan Selatan'],
                'cities' => [
                    ['id' => 6301, 'name' => 'Kabupaten Tanah Laut', 'zip_code' => '70811'],
                    ['id' => 6302, 'name' => 'Kabupaten Kota Baru', 'zip_code' => '72111'],
                    ['id' => 6303, 'name' => 'Kabupaten Banjar', 'zip_code' => '71511'],
                    ['id' => 6304, 'name' => 'Kabupaten Barito Kuala', 'zip_code' => '70711'],
                    ['id' => 6305, 'name' => 'Kabupaten Tabalong', 'zip_code' => '71711'],
                    ['id' => 6306, 'name' => 'Kabupaten Hulu Sungai Selatan', 'zip_code' => '71411'],
                    ['id' => 6307, 'name' => 'Kabupaten Hulu Sungai Tengah', 'zip_code' => '71311'],
                    ['id' => 6308, 'name' => 'Kabupaten Hulu Sungai Utara', 'zip_code' => '71211'],
                    ['id' => 6371, 'name' => 'Kota Banjarmasin', 'zip_code' => '70111'],
                    ['id' => 6372, 'name' => 'Kota Banjarbaru', 'zip_code' => '70711'],
                ]
            ],
            [
                'province' => ['id' => 64, 'name' => 'Kalimantan Timur'],
                'cities' => [
                    ['id' => 6401, 'name' => 'Kabupaten Paser', 'zip_code' => '76111'],
                    ['id' => 6402, 'name' => 'Kabupaten Kutai Barat', 'zip_code' => '75611'],
                    ['id' => 6403, 'name' => 'Kabupaten Kutai Kartanegara', 'zip_code' => '75511'],
                    ['id' => 6404, 'name' => 'Kabupaten Kutai Timur', 'zip_code' => '75611'],
                    ['id' => 6405, 'name' => 'Kabupaten Berau', 'zip_code' => '77311'],
                    ['id' => 6471, 'name' => 'Kota Balikpapan', 'zip_code' => '76111'],
                    ['id' => 6472, 'name' => 'Kota Samarinda', 'zip_code' => '75111'],
                    ['id' => 6473, 'name' => 'Kota Tarakan', 'zip_code' => '77111'],
                    ['id' => 6474, 'name' => 'Kota Tenggarong', 'zip_code' => '75511'],
                ]
            ],
            [
                'province' => ['id' => 65, 'name' => 'Sulawesi Utara'],
                'cities' => [
                    ['id' => 6501, 'name' => 'Kabupaten Bolaang Mongondow', 'zip_code' => '95711'],
                    ['id' => 6502, 'name' => 'Kabupaten Minahasa', 'zip_code' => '95511'],
                    ['id' => 6503, 'name' => 'Kabupaten Kepulauan Sangihe', 'zip_code' => '97811'],
                    ['id' => 6504, 'name' => 'Kabupaten Kepulauan Talaud', 'zip_code' => '97811'],
                    ['id' => 6571, 'name' => 'Kota Manado', 'zip_code' => '95111'],
                    ['id' => 6572, 'name' => 'Kota Bitung', 'zip_code' => '95211'],
                    ['id' => 6573, 'name' => 'Kota Tomohon', 'zip_code' => '95311'],
                    ['id' => 6574, 'name' => 'Kota Kotamobagu', 'zip_code' => '95611'],
                ]
            ],
            [
                'province' => ['id' => 71, 'name' => 'Sulawesi Tengah'],
                'cities' => [
                    ['id' => 7101, 'name' => 'Kabupaten Banggai', 'zip_code' => '94711'],
                    ['id' => 7102, 'name' => 'Kabupaten Poso', 'zip_code' => '94611'],
                    ['id' => 7103, 'name' => 'Kabupaten Donggala', 'zip_code' => '94511'],
                    ['id' => 7104, 'name' => 'Kabupaten Toli-toli', 'zip_code' => '94511'],
                    ['id' => 7105, 'name' => 'Kabupaten Buol', 'zip_code' => '94611'],
                    ['id' => 7106, 'name' => 'Kabupaten Morowali', 'zip_code' => '94811'],
                    ['id' => 7171, 'name' => 'Kota Palu', 'zip_code' => '94111'],
                ]
            ],
            [
                'province' => ['id' => 72, 'name' => 'Sulawesi Selatan'],
                'cities' => [
                    ['id' => 7201, 'name' => 'Kabupaten Selayar', 'zip_code' => '90811'],
                    ['id' => 7202, 'name' => 'Kabupaten Bulukumba', 'zip_code' => '92711'],
                    ['id' => 7203, 'name' => 'Kabupaten Bantaeng', 'zip_code' => '92811'],
                    ['id' => 7204, 'name' => 'Kabupaten Jeneponto', 'zip_code' => '92911'],
                    ['id' => 7205, 'name' => 'Kabupaten Takalar', 'zip_code' => '92111'],
                    ['id' => 7206, 'name' => 'Kabupaten Gowa', 'zip_code' => '92111'],
                    ['id' => 7207, 'name' => 'Kabupaten Sinjai', 'zip_code' => '92611'],
                    ['id' => 7208, 'name' => 'Kabupaten Bone', 'zip_code' => '90511'],
                    ['id' => 7209, 'name' => 'Kabupaten Maros', 'zip_code' => '90511'],
                    ['id' => 7210, 'name' => 'Kabupaten Pangkajene Dan Kepulauan', 'zip_code' => '90611'],
                    ['id' => 7211, 'name' => 'Kabupaten Barru', 'zip_code' => '90711'],
                    ['id' => 7212, 'name' => 'Kabupaten Soppeng', 'zip_code' => '90811'],
                    ['id' => 7213, 'name' => 'Kabupaten Wajo', 'zip_code' => '90911'],
                    ['id' => 7214, 'name' => 'Kabupaten Sidenreng Rappang', 'zip_code' => '91311'],
                    ['id' => 7215, 'name' => 'Kabupaten Pinrang', 'zip_code' => '91311'],
                    ['id' => 7216, 'name' => 'Kabupaten Enrekang', 'zip_code' => '91711'],
                    ['id' => 7217, 'name' => 'Kabupaten Luwu', 'zip_code' => '91311'],
                    ['id' => 7218, 'name' => 'Kabupaten Toraja', 'zip_code' => '91811'],
                    ['id' => 7271, 'name' => 'Kota Makassar', 'zip_code' => '90111'],
                    ['id' => 7272, 'name' => 'Kota Parepare', 'zip_code' => '91111'],
                    ['id' => 7273, 'name' => 'Kota Palopo', 'zip_code' => '91711'],
                ]
            ],
            [
                'province' => ['id' => 73, 'name' => 'Sulawesi Tenggara'],
                'cities' => [
                    ['id' => 7301, 'name' => 'Kabupaten Buton', 'zip_code' => '93711'],
                    ['id' => 7302, 'name' => 'Kabupaten Muna', 'zip_code' => '93611'],
                    ['id' => 7303, 'name' => 'Kabupaten Konawe', 'zip_code' => '93511'],
                    ['id' => 7304, 'name' => 'Kabupaten Konawe Selatan', 'zip_code' => '93711'],
                    ['id' => 7305, 'name' => 'Kabupaten Bombana', 'zip_code' => '93811'],
                    ['id' => 7371, 'name' => 'Kota Kendari', 'zip_code' => '93111'],
                    ['id' => 7372, 'name' => 'Kota Baubau', 'zip_code' => '93711'],
                ]
            ],
            [
                'province' => ['id' => 81, 'name' => 'Maluku'],
                'cities' => [
                    ['id' => 8101, 'name' => 'Kabupaten Maluku Tenggara Barat', 'zip_code' => '97611'],
                    ['id' => 8102, 'name' => 'Kabupaten Maluku Tenggara', 'zip_code' => '97711'],
                    ['id' => 8103, 'name' => 'Kabupaten Maluku Tengah', 'zip_code' => '97511'],
                    ['id' => 8104, 'name' => 'Kabupaten Buru', 'zip_code' => '97511'],
                    ['id' => 8105, 'name' => 'Kabupaten Seram Bagian Barat', 'zip_code' => '97551'],
                    ['id' => 8106, 'name' => 'Kabupaten Seram Bagian Timur', 'zip_code' => '97651'],
                    ['id' => 8171, 'name' => 'Kota Ambon', 'zip_code' => '97111'],
                    ['id' => 8172, 'name' => 'Kota Tual', 'zip_code' => '97711'],
                ]
            ],
            [
                'province' => ['id' => 82, 'name' => 'Maluku Utara'],
                'cities' => [
                    ['id' => 8201, 'name' => 'Kabupaten Halmahera Barat', 'zip_code' => '97811'],
                    ['id' => 8202, 'name' => 'Kabupaten Halmahera Tengah', 'zip_code' => '97811'],
                    ['id' => 8203, 'name' => 'Kabupaten Halmahera Utara', 'zip_code' => '97811'],
                    ['id' => 8204, 'name' => 'Kabupaten Kepulauan Sula', 'zip_code' => '97911'],
                    ['id' => 8271, 'name' => 'Kota Ternate', 'zip_code' => '97111'],
                    ['id' => 8272, 'name' => 'Kota Tidore Kepulauan', 'zip_code' => '97211'],
                ]
            ],
            [
                'province' => ['id' => 91, 'name' => 'Papua'],
                'cities' => [
                    ['id' => 9101, 'name' => 'Kabupaten Merauke', 'zip_code' => '99611'],
                    ['id' => 9102, 'name' => 'Kabupaten Bade', 'zip_code' => '99711'],
                    ['id' => 9103, 'name' => 'Kabupaten Asmat', 'zip_code' => '99811'],
                    ['id' => 9104, 'name' => 'Kabupaten Yahukimo', 'zip_code' => '99911'],
                    ['id' => 9105, 'name' => 'Kabupaten Kerom', 'zip_code' => '98411'],
                    ['id' => 9106, 'name' => 'Kabupaten Pegunungan Bintang', 'zip_code' => '99211'],
                    ['id' => 9107, 'name' => 'Kabupaten Jayapura', 'zip_code' => '99411'],
                    ['id' => 9171, 'name' => 'Kota Jayapura', 'zip_code' => '99111'],
                ]
            ],
        ];

        // Clear existing data
        District::truncate();
        City::truncate();
        Province::truncate();

        $districtId = 100000;
        $cityId = 1000;

        foreach ($data as $provinceData) {
            // Create province
            $province = Province::create($provinceData['province']);
            $this->info("✅ Created province: {$province->name}");

            // Create cities and districts for this province
            foreach ($provinceData['cities'] as $cityData) {
                $cityData['id'] = $cityId++;
                $cityData['province_id'] = $province->id;
                $city = City::create($cityData);

                // Create sample districts for each city
                $districts = [
                    ['id' => $districtId++, 'city_id' => $city->id, 'name' => str_replace(['Kabupaten ', 'Kota '], '', $cityData['name']) . ' District 1', 'zip_code' => $cityData['zip_code']],
                    ['id' => $districtId++, 'city_id' => $city->id, 'name' => str_replace(['Kabupaten ', 'Kota '], '', $cityData['name']) . ' District 2', 'zip_code' => $cityData['zip_code']],
                ];

                foreach ($districts as $districtData) {
                    District::create($districtData);
                }
            }
        }

        $provinceCount = Province::count();
        $cityCount = City::count();
        $districtCount = District::count();

        $this->info("✅ Done!");
        $this->info("📊 Total: $provinceCount provinces, $cityCount cities, $districtCount districts");
    }
}
