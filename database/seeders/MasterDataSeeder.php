<?php

namespace Database\Seeders;


use App\Models\Assetcategory;
use App\Models\Devisions;
use App\Models\Locations;
use App\Models\Workcategory;
use App\Models\Scheduletype;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            ['name' => 'IT', 'code' => 'IT'],
            ['name' => 'Marketing', 'code' => 'MKT'],
            ['name' => 'Finance', 'code' => 'FIN'],
            ['name' => 'Operasional', 'code' => 'OPS'],
            ['name' => 'Manajemen', 'code' => 'MGT'],
        ];

        foreach ($divisions as $index => $division) {
            Devisions::updateOrCreate(
                ['code' => $division['code']],
                [
                    'name' => $division['name'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        $locations = [
            ['name' => 'Kantor Utama', 'code' => 'KTR'],
            ['name' => 'Ruang IT', 'code' => 'ITROOM'],
            ['name' => 'Gudang', 'code' => 'GDG'],
            ['name' => 'Front Office', 'code' => 'FO'],
        ];

        foreach ($locations as $index => $location) {
            Locations::updateOrCreate(
                ['code' => $location['code']],
                [
                    'name' => $location['name'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        $assetCategories = [
            ['name' => 'Laptop', 'code' => 'LPT'],
            ['name' => 'Komputer', 'code' => 'PC'],
            ['name' => 'Printer', 'code' => 'PRN'],
            ['name' => 'Jaringan', 'code' => 'NET'],
            ['name' => 'Perangkat CCTV', 'code' => 'CCTV'],
            ['name' => 'Software', 'code' => 'SFT'],
        ];

        foreach ($assetCategories as $index => $category) {
            Assetcategory::updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        $workCategories = [
            ['name' => 'Maintenance Hardware', 'code' => 'HW'],
            ['name' => 'Maintenance Software', 'code' => 'SW'],
            ['name' => 'Jaringan', 'code' => 'NET'],
            ['name' => 'CCTV', 'code' => 'CCTV'],
            ['name' => 'Support User', 'code' => 'SUP'],
            ['name' => 'Pengembangan Sistem', 'code' => 'DEV'],
        ];

        foreach ($workCategories as $index => $category) {
            Workcategory::updateOrCreate(
                ['code' => $category['code']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }

        $scheduleTypes = [
            ['name' => 'Harian', 'code' => 'DAILY', 'color' => '#2563eb'],
            ['name' => 'Mingguan', 'code' => 'WEEKLY', 'color' => '#16a34a'],
            ['name' => 'Bulanan', 'code' => 'MONTHLY', 'color' => '#f59e0b'],
            ['name' => 'Insidental', 'code' => 'INCIDENTAL', 'color' => '#dc2626'],
        ];

        foreach ($scheduleTypes as $index => $type) {
            Scheduletype::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'color' => $type['color'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
