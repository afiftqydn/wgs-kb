<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Referrer;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Logika untuk memastikan foreign key (referrer, region, user) ada
        // Jika tidak ada data di tabel terkait, factory ini akan membuatnya secara otomatis.
        $referrer = Referrer::first() ?? Referrer::factory()->create();
        $region = Region::first() ?? Region::factory()->create();
        $user = User::first() ?? User::factory()->create();

        return [
            // Kolom dari tabel 'customers' Anda:
            'nik'                => $this->faker->unique()->numerify('################'), // 16 digit angka unik
            'name'               => $this->faker->name(),
            'phone'              => $this->faker->phoneNumber(),
            'email'              => $this->faker->unique()->safeEmail(),
            'address'            => $this->faker->address(),
            
            // Mengisi Foreign Key dengan data yang sudah dipastikan ada
            'region_id'          => $region->id,
            'created_by'         => $user->id, // Penting, karena seeder tidak punya session Auth
            'referrer_id'        => $referrer->id,
            
            // Mengisi kolom referral, diambil dari data referrer di atas agar konsisten
            'referral_code_used' => $referrer->generated_referral_code, // Asumsi ada kolom ini di model Referrer
        ];
    }
}