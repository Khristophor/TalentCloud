<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\HrAdvisor;
use App\Models\JobPoster;
use App\Models\Lookup\JobPosterStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JobStatusControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure several legal transitions work as expected.
     *
     * @return void
     */
    public function testLegalTransitions(): void
    {
        $job = factory(JobPoster::class)->states(['draft', 'byUpgradedManager'])->create();
        $this->assertEquals('draft', $job->fresh()->job_poster_status->key);

        $responseReview = $this->actingAs($job->manager->user)->post(
            route('manager.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'review_hr'])
        );
        $responseReview->assertStatus(302);
        $this->assertEquals('review_hr', $job->fresh()->job_poster_status->key);

        $hrAdvisor = factory(User::class)->states('hr_advisor')->create([
            'department_id' => $job->department_id
        ]);
        $hrAdvisor->hr_advisor()->save(factory(HrAdvisor::class)->create([
            'user_id' => $hrAdvisor->id,
        ]));
        $hrAdvisor->hr_advisor->claimed_jobs()->attach($job);

        $responseTranslation = $this->actingAs($hrAdvisor->fresh())->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'translation'])
        );
        $responseTranslation->assertStatus(302);
        $this->assertEquals('translation', $job->fresh()->job_poster_status->key);

        $admin = factory(User::class)->state('admin')->create([
            'gov_email' => 'admin@test.gov',
            'department_id' => $job->department_id,
        ]);
        $admin->hr_advisor()->save(factory(HrAdvisor::class)->create([
            'user_id' => $admin->id,
        ]));
        $responseFinalReview = $this->actingAs($admin)->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'final_review_manager'])
        );
        $responseFinalReview->assertStatus(302);
        $this->assertEquals('final_review_manager', $job->fresh()->job_poster_status->key);

        $responsePending = $this->actingAs($job->manager->user)->post(
            route('manager.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'pending_approval'])
        );
        $responsePending->assertStatus(302);
        $this->assertEquals('pending_approval', $job->fresh()->job_poster_status->key);

        $responseApproved = $this->actingAs($hrAdvisor->fresh())->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'approved'])
        );
        $responseApproved->assertStatus(302);
        $this->assertEquals('approved', $job->fresh()->job_poster_status->key);

        $responseReady = $this->actingAs($admin)->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'ready'])
        );
        $responseReady->assertStatus(302);
        $this->assertEquals('ready', $job->fresh()->job_poster_status->key);

        $responseLive = $this->actingAs($admin)->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'live'])
        );
        $responseLive->assertStatus(302);
        $this->assertEquals('live', $job->fresh()->job_poster_status->key);

        $responseAssessment = $this->actingAs($admin)->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'assessment'])
        );
        $responseAssessment->assertStatus(302);
        $this->assertEquals('assessment', $job->fresh()->job_poster_status->key);

        $responseCompleted = $this->actingAs($admin)->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'completed'])
        );
        $responseCompleted->assertStatus(302);
        $this->assertEquals('completed', $job->fresh()->job_poster_status->key);
    }

    /**
     * Illegal transitions should fail and leave the status unchanged.
     *
     * @return void
     */
    public function testIllegalTransitions(): void
    {
        $job = factory(JobPoster::class)->states(['draft', 'byUpgradedManager'])->create();
        $this->assertEquals('draft', $job->fresh()->job_poster_status->key);

        $response = $this->actingAs($job->manager->user)->post(
            route('manager.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'live'])
        );
        $response->assertStatus(400);
        $this->assertEquals('draft', $job->fresh()->job_poster_status->key);

        $job->job_poster_status_id = JobPosterStatus::where('key', 'review_hr')->first()->id;
        $job->save();

        $hrAdvisor = factory(User::class)->states('hr_advisor')->create([
            'department_id' => $job->department_id
        ]);
        $hrAdvisor->hr_advisor()->save(factory(HrAdvisor::class)->create([
            'user_id' => $hrAdvisor->id,
        ]));
        $hrAdvisor->hr_advisor->claimed_jobs()->attach($job);

        $responseHr =  $this->actingAs($hrAdvisor->fresh())->post(
            route('hr_advisor.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'draft'])
        );
        $responseHr->assertStatus(400);
        $this->assertEquals('review_hr', $job->fresh()->job_poster_status->key);
    }

    /**
     * Legal transitions should fail and leave the status unchanged if the wrong user initiates them.
     *
     * @return void
     */
    public function testTransitionByWrongUser(): void
    {
        $job = factory(JobPoster::class)->states(['draft', 'byUpgradedManager'])->create();
        $job->job_poster_status_id = JobPosterStatus::where('key', 'review_hr')->first()->id;
        $job->save();

        $this->assertEquals('review_hr', $job->fresh()->job_poster_status->key);

        $hrAdvisor = factory(User::class)->states('hr_advisor')->create([
            'department_id' => $job->department_id
        ]);
        $hrAdvisor->hr_advisor()->save(factory(HrAdvisor::class)->create([
            'user_id' => $hrAdvisor->id,
        ]));
        $hrAdvisor->hr_advisor->claimed_jobs()->attach($job);

        $response = $this->actingAs($job->manager->user)->post(
            route('manager.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'review_manager'])
        );
        $response->assertStatus(400);
        $this->assertEquals('review_hr', $job->fresh()->job_poster_status->key);
    }

    public function testTransitionHistory(): void
    {
        $job = factory(JobPoster::class)->states(['draft', 'byUpgradedManager'])->create();
        $this->assertEquals('draft', $job->fresh()->job_poster_status->key);
        $this->assertEmpty($job->fresh()->job_poster_status_histories);

        $responseReview = $this->actingAs($job->manager->user)->post(
            route('manager.jobs.setJobStatus', ['jobPoster' => $job, 'status' => 'review_hr'])
        );

        $this->assertCount(1, $job->fresh()->job_poster_status_histories);
        $this->assertDatabaseHas('job_poster_status_histories', [
            'job_poster_id' => $job->id,
            'user_id' => $job->manager->user->id,
            'from_job_poster_status_id' => JobPosterStatus::where('key', 'draft')->first()->id,
            'to_job_poster_status_id' => JobPosterStatus::where('key', 'review_hr')->first()->id,
        ]);
    }
}
