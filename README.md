Cruddy
======

Backend CRUD without a line of code

More info @ [Laravel Forums](http://forums.laravel.io/viewtopic.php?id=15689)

### Installation

This shipping includes an app that I use for rapid development. You can set it up and see what Cruddy is capable of.

At first you need to clone this repo. Then, configure database credentials as you would do in usual Laravel app. Migrate and seed database:

```
php artisan migrate --package cartalyst/sentry && php artisan migrate --seed
```

You can now enter the backend by entering `<hostname>/backend/users`. It will require username and password. The defaults:

admin@mail.com / admin

Enjoy!
