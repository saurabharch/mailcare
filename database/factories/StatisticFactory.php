<?php

use App\Statistic;
use Faker\Generator as Faker;

$factory->define(Statistic::class, function (Faker $faker) {
    return [
        'created_at' => $faker->date(),
        'emails_received' => $faker->numberBetween(10, 50),
        'inboxes_created' => $faker->numberBetween(2, 20),
        'storage_used' => $faker->numberBetween(10000000, 50000000),
    ];
});
