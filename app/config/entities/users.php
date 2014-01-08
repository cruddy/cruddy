<?php return array(

    'primary_column' => 'full_name',

    'form' => array(
        'model' => 'User',

        'validator' => function ($v)
        {
            $v->rules([
                'email' => 'required|email|max:255',
                'first_name' => 'max:255',
                'last_name' => 'max:255',
            ]);

            $v->create([
                'password' => 'required',
            ]);
        },
    ),

    'columns' => array(
        'id',

        'full_name' => array(
            'type' => 'computed',

            'value' => function ($model) {
                return $model->last_name.' '.$model->first_name;
            },

            'order_clause' => DB::raw('concat(ifnull(last_name, ""), ifnull(first_name, ""))'),

            'filter' => function ($builder, $data, $boolean) {
                $builder->where($this->order_clause, 'like', "%{$data}%", $boolean);
            },
        ),

        'email',
        'activated',
        'last_login' => array('order_dir' => 'desc'),
        'updated_at' => array('order_dir' => 'desc'),
    ),

    'fields' => array(
        'last_name', 'first_name',

        'email' => array(
            'type' => 'email',
            'required' => true,
        ),

        'activated' => 'bool',

        'password' => 'password',

        'groups' => 'relation',

        'permissions_string' => 'text',

        'last_login' => 'datetime',
    ),

    'related' => array(
        'throttle' => 'one',
        'address' => 'morphOne',
    ),

);