<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Article;
use App\User;
use Faker\Generator as Faker;

$factory->define(Article::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(),
        'text' => $faker->sentences(5, true),
        'publish' => true,
        'user_id' => function() {
            return factory(User::class)->create()->id;
        },
    ];
});
