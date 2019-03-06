<?php

use App\Automation;
use Faker\Generator as Faker;

$factory->define(Automation::class, function (Faker $faker) {
    return [
        'id' => $faker->uuid(),
        'title' => $faker->sentence(3),
        'action_url' => $faker->url
    ];
});