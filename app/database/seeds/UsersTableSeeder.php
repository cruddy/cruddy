<?php

class UsersTableSeeder extends Seeder {

	public function run()
	{
        DB::table('users_groups')->truncate();
        DB::table('groups')->truncate();
        DB::table('users')->truncate();

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
                'throttles.update' => 1,
                'throttles.create' => 1,
            ),
        ));

        $user = Sentry::createUser(array(

            'email' => 'admin@localhost',
            'password' => 'admin',

            'first_name' => 'Admin',

            'activated' => true,

            'permissions' => array(
                'superuser' => 1,
            ),
        ));

        $user->groups()->attach($group);
	}

}
