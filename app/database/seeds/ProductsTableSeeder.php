<?php

class ProductsTableSeeder extends Seeder {

    public function run()
    {
        Product::truncate();

        $categories = Category::lists('id');
        $faker = Faker\Factory::create();

        foreach (range(1, 20) as $index)
        {
            $product = Product::create([
                'title' => $faker->sentence(rand(1, 3)),
                'description' => $faker->paragraph(rand(1, 3)),
                'image' => '',
            ]);

            $totalParents = rand(1, 3);
            $totalCategories = count($categories) - 1;
            $parents = [];

            while ($totalParents-- > 0)
            {
                $parents[] = $categories[rand(0, $totalCategories)];
            }

            if (!empty($parents))
            {
                $product->categories()->sync($parents);
            }
        }
    }
}