<?php

use App\Models\Comment;
use App\Models\Lookup\CommentType;
use App\Models\JobPoster;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'job_poster_id' => function () {
            return factory(JobPoster::class)->create()->id;
        },
        'user_id' => function () {
            return factory(User::class)->state('hr_advisor')->create()->id;
        },
        'comment' => $faker->sentence(),
        'location' => 'job/generic',
        'type_id' => CommentType::inRandomOrder()->first()->id,
    ];
});
