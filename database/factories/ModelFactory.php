<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Sender::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'display_name' => $faker->name,
        'local_part' => '',
        'domain' => '',
    ];
});

$factory->define(App\Inbox::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'display_name' => $faker->name,
        'local_part' => '',
        'domain' => '',
    ];
});


$factory->define(App\Email::class, function (Faker\Generator $faker) {
    return [
        'sender_id' => function () {
            return factory(App\Sender::class)->create()->id;
        },
        'inbox_id' => function () {
            return factory(App\Inbox::class)->create()->id;
        },
        'subject' => $faker->sentence(5),
        'read' => null,
    ];
});


$factory->define(App\Attachment::class, function (Faker\Generator $faker) {
    return [
        'email_id' => function () {
            return factory(App\Email::class)->create()->id;
        },
        'headers_hashed' => 'HASHXXXXXXX',
        'file_name' => 'test.pdf',
        'content_type' => 'application/pdf',
        'size_in_bytes' => 100,
    ];
});

$factory->define(App\Statistic::class, function (Faker\Generator $faker) {
    return [
        'created_at' => $faker->date(),
        'emails_received' => $faker->numberBetween(10, 50),
        'inboxes_created' => $faker->numberBetween(2, 20),
        'storage_used' => $faker->numberBetween(10000000, 50000000),
    ];
});


$factory->define(App\Automation::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->uuid(),
        'title' => $faker->sentence(3),
        'action_url' => $faker->url
    ];
});