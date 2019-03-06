<?php

use App\Email;
use App\Sender;
use App\Inbox;
use Faker\Generator as Faker;

$factory->define(Email::class, function (Faker $faker) {
    return [
        'sender_id' => function () {
            return factory(Sender::class)->create()->id;
        },
        'inbox_id' => function () {
            return factory(Inbox::class)->create()->id;
        },
        'subject' => $faker->sentence(5),
        'read' => null,
    ];
});