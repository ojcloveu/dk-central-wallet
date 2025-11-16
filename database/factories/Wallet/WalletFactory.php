<?php

namespace Database\Factories\Wallet;

use App\Enum\Wallet\WalletTypeEnum;
use App\Models\Wallet\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wallet\Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = array_column(WalletTypeEnum::cases(), 'value');
        return [
            'user_id' => $this->faker->numberBetween(1, 2),
            'currency_id' => 1,
            'name' => 'Wallet USD',
            'type' => $this->faker->randomElement($types),
        ];
    }
}
