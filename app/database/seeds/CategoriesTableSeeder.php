<?php

class CategoriesTableSeeder extends Seeder {

    public function run()
    {
        Category::truncate();

        $faker = Faker\Factory::create();

        foreach (range(1, 3) as $index)
        {
            $category = Category::create([
                'title' => $faker->sentence(rand(1, 3)),
                'images' => [],
            ]);

            $children = rand(0, 3);

            while ($children-- > 0)
            {
                Category::create([
                    'title' => $faker->sentence(rand(1, 3)),
                    'parent_id' => $category->id,
                    'images' => [],
                ]);
            }
        }
    }
}