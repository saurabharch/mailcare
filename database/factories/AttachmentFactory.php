<?php

use App\Attachment;
use App\Email;
use Faker\Generator as Faker;

$factory->define(Attachment::class, function (Faker $faker) {
    return [
        'email_id' => function () {
            return factory(Email::class)->create()->id;
        },
        'headers_hashed' => 'HASHXXXXXXX',
        'file_name' => 'test.pdf',
        'content_type' => 'application/pdf',
        'size_in_bytes' => 100,
    ];
});