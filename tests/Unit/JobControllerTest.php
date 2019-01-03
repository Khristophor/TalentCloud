<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Lang;

use App\Models\Criteria;
use App\Models\Lookup\Department;
use App\Models\JobPoster;
use App\Models\JobPosterKeyTask;
use App\Models\JobPosterQuestion;
use App\Models\Lookup\LanguageRequirement;
use App\Models\Manager;
use App\Models\Lookup\Province;
use App\Models\Lookup\SecurityClearance;

class JobControllerTest extends TestCase
{
    /**
     * Run parent setup and provide reusable factories.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->faker = \Faker\Factory::create();
        $this->faker_fr = \Faker\Factory::create('fr_FR');

        $this->manager = factory(Manager::class)->create();
        $this->jobPoster = factory(JobPoster::class)->create([
            'manager_id' => $this->manager->id
        ]);

        $this->jobPoster->criteria()->saveMany(factory(Criteria::class, 2)->make([
            'job_poster_id' => $this->jobPoster->id
        ]));
        $this->jobPoster->job_poster_key_tasks()->saveMany(factory(JobPosterKeyTask::class, 2)->make([
            'job_poster_id' => $this->jobPoster->id
        ]));
        $this->jobPoster->job_poster_questions()->saveMany(factory(JobPosterQuestion::class, 2)->make([
            'job_poster_id' => $this->jobPoster->id
        ]));

        $this->otherManager = factory(Manager::class)->create();
        $this->otherJobPoster = factory(JobPoster::class)->create([
            'manager_id' => $this->otherManager->id
        ]);
    }

    /**
     * Ensure a manager can view their index page.
     *
     * @return void
     */
    public function testManagerIndexView()
    {
        $response = $this->actingAs($this->manager->user)
            ->get('manager/jobs');
        $response->assertStatus(200);

        $response->assertSee('<h3>' . $this->jobPoster->title . '</h3>');
        $response->assertDontSee('<h3>' . $this->otherJobPoster->title . '</h3>');
    }

    /**
     * Ensure a manager can view the create Job Poster form.
     *
     * @return void
     */
    public function testManagerCreateView()
    {
        $response = $this->actingAs($this->manager->user)
            ->get('manager/jobs/create');
        $response->assertStatus(200);

        $response->assertSee('<h2 class="heading--01">' . Lang::get('manager/job_create')['title'] . '</h2>');
        $response->assertViewIs('manager.job_create');
    }

    /**
     * Ensure a manager can create a Job Poster.
     *
     * @return void
     */
    public function testManagerCreate()
    {
        $newJob = [
            'term_qty' => $this->faker->numberBetween(1, 4),
            'salary_min' => $this->faker->numberBetween(60000, 80000),
            'salary_max' => $this->faker->numberBetween(80000, 100000),
            'noc' => $this->faker->numberBetween(1, 9999),
            'classification' => $this->faker->regexify('[A-Z]{2}-0[1-5]'),
            'remote_work_allowed' => $this->faker->boolean(50),
            'manager_id' => $this->manager->id,
            'published' => $this->faker->boolean(50),
            'open_date' => $this->faker->date('Y-m-d', strtotime('+1 day')),
            'open_time' => $this->faker->time(),
            'close_date' => $this->faker->date('Y-m-d', strtotime('+2 weeks')),
            'close_time' => $this->faker->time(),
            'start_date_time' => $this->faker->date('Y-m-d', strtotime('+2 weeks')) . ' ' . $this->faker->time(),
            'security_clearance' => SecurityClearance::inRandomOrder()->first()->id,
            'language_requirement' => LanguageRequirement::inRandomOrder()->first()->id,
            'department' => Department::inRandomOrder()->first()->id,
            'province' => Province::inRandomOrder()->first()->id,
            'city' => $this->faker->city,
            'title' => [
                'en' => $this->faker->word,
                'fr' => $this->faker_fr->word
            ],
            'impact' => [
                'en' => $this->faker->paragraphs(
                    2,
                    true
                ),
                'fr' => $this->faker_fr->paragraphs(
                    2,
                    true
                )
            ],
            'branch' => [
                'en' => $this->faker->word,
                'fr' => $this->faker_fr->word
            ],
            'division' => [
                'en' => $this->faker->word,
                'fr' => $this->faker_fr->word
            ],
            'education' => [
                'en' => $this->faker->sentence(),
                'fr' => $this->faker_fr->sentence()
            ],
            'submit' => '',
        ];

        $dbValues = array_slice($newJob, 0, 8);

        $response = $this->followingRedirects()
            ->actingAs($this->manager->user)
            ->post('manager/jobs/', $newJob);
        $response->assertStatus(200);
        $response->assertViewIs('manager.job_index');
        $this->assertDatabaseHas('job_posters', $dbValues);
    }

    /**
     * Ensure a manager can edit an unpublished Job Poster they created.
     *
     * @return void
     */
    public function testManagerEditView()
    {
        $response = $this->actingAs($this->manager->user)
            ->get('manager/jobs/' . $this->jobPoster->id . '/edit');

        $response->assertStatus(200);
        $response->assertViewIs('manager.job_create');
        // Check for a handful of properties
        $response->assertSee($this->jobPoster->city);
        $response->assertSee($this->jobPoster->education);
        $response->assertSee($this->jobPoster->title);
        $response->assertSee($this->jobPoster->impact);
        $response->assertSee($this->jobPoster->branch);
        $response->assertSee($this->jobPoster->division);
        $response->assertSee($this->jobPoster->education);
    }
}
