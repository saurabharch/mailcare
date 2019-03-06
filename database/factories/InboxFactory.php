<?php

use App\Inbox;
use Faker\Generator as Faker;

$factory->define(Inbox::class, function (Faker $faker) {
    return [
        'email' => $faker->email,
        'display_name' => $faker->name,
        'local_part' => '',
        'domain' => '',
    ];
});
