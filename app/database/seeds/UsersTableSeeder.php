<?php

class UsersTableSeeder extends Seeder {

	public function run()
	{
        DB::table('users_groups')->truncate();
        DB::table('groups')->truncate();
        DB::table('users')->truncate();
        DB::table('addresses')->truncate();

        $faker = Faker\Factory::create();

		$group = Sentry::createGroup(array(

            'name' => 'administrator',

            'permissions' => array(
                'backend' => 1,

                // Users permissions
                'users.view' => 1,
                'users.update' => 1,
                'users.create' => 1,

                // User groups permissions
                'groups.view' => 1,
                'groups.update' => 1,
                'groups.create' => 1,

                // Throttle permissions
                'throttles.view' => 1,
                'throttles.update' => 1,
                'throttles.create' => 1,
            ),
        ));

        $user = Sentry::createUser([

            'email' => 'admin@mail.com',
            'password' => 'admin',

            'first_name' => 'Admin',

            'activated' => true,

            'permissions' => [
                'superuser' => 1,
            ],
        ]);

        $user->groups()->attach($group);

        foreach (range(1, 20) as $index)
        {
            $user = Sentry::createUser([
               'email' => $faker->email,
               'password' => $faker->word,
               'first_name' => $faker->firstName,
               'last_name' => $faker->lastName,
               'activated' => $faker->boolean(),
            ]);

            $user->address()->create([
                'address' => $faker->address,
            ]);
        }

    }

}
