<?php

namespace Database\Factories;

use App\Domain\Catalog\UnitCondition;
use App\Domain\Catalog\UnitKind;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'PC-TST-'.fake()->unique()->numerify('###');

        return [
            'code' => $code,
            'name' => 'PC Uji '.fake()->word(),
            'kind' => UnitKind::PcMonitor,
            'specs_text' => 'Ryzen 5 7600; RTX 5070 12GB; RAM 32GB',
            'contents_text' => "{$code}/PC - satu PC\n{$code}/MON - satu monitor",
            'package_price' => 500000,
            'daily_fine' => 100000,
            'condition_status' => UnitCondition::Ready,
        ];
    }

    public function peripheral(): static
    {
        return $this->state(fn (array $attributes) => [
            'kind' => UnitKind::Peripheral,
            'package_price' => 125000,
            'daily_fine' => 20000,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition_status' => UnitCondition::Maintenance,
        ]);
    }
}
