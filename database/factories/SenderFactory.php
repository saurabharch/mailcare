<?php

use App\Sender;
use Faker\Generator as Faker;

$factory->define(Sender::class, function (Faker $faker) {
    return [
        'email' => $faker->email,
        'display_name' => $faker->name,
        'local_part' => '',
        'domain' => '',
    ];
});