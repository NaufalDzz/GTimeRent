<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Katalog dan pengaturan dari docs/data-demo.json (harga hipotetis untuk demo akademik).
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $data = File::json(base_path('docs/data-demo.json'));

        Setting::query()->updateOrCreate(['id' => 1], [
            'flat_shipping_fee' => $data['settings']['flat_shipping_fee'],
            'service_area_text' => Setting::DEFAULT_AREA_TEXT,
        ]);

        foreach ($data['units'] as $row) {
            $unit = Unit::query()->updateOrCreate(['code' => $row['code']], [
                'name' => $row['name'],
                'kind' => $row['kind'],
                'specs_text' => $row['specs_text'],
                'contents_text' => implode("\n", $row['contents']),
                'package_price' => $row['package_price'],
                'daily_fine' => $row['daily_fine'],
                'condition_status' => $row['condition_status'],
            ]);

            $categoryIds = collect($row['categories'])
                ->map(fn (string $name): int => Category::query()->firstOrCreate(['name' => $name])->id);

            $unit->categories()->sync($categoryIds);
        }
    }
}
