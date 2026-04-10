<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        $categories = ['Food & Beverage', 'Software', 'Hardware', 'Industri', 'Jasa', 'Kesehatan'];
        $units = ['pcs', 'kg', 'gram', 'liter', 'paket', 'lusin', 'dus', 'karton', 'unit', 'lisensi'];
        $statuses = ['active', 'inactive', 'preorder', 'out_of_stock'];
        
        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->slug,
            'category' => $this->faker->randomElement($categories),
            'description' => $this->faker->paragraphs(3, true),
            'original_price' => $this->faker->numberBetween(10000, 1000000),
            'selling_price' => function (array $attributes) {
                return $attributes['original_price'] + $this->faker->numberBetween(10000, 500000);
            },
            'stock' => $this->faker->numberBetween(0, 1000),
            'unit' => $this->faker->randomElement($units),
            'status' => $this->faker->randomElement($statuses),
            'image' => 'products/' . $this->faker->image('public/storage/products', 640, 480, null, false),
            'location' => $this->faker->city,
            'rating' => $this->faker->randomFloat(1, 1, 5),
            'is_featured' => $this->faker->boolean(20),
        ];
    }
}