<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\JobPoster;
use App\Models\HrAdvisor;
use App\Models\User;

class ClaimJobApiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testClaimAndUnclaim(): void
    {
        // Factories.
        $user = factory(User::class)->create([
            'department_id' => 1,
            'user_role_id' => 4
        ]);
        $hrAdvisor = factory(HrAdvisor::class)->create([
            'user_id' => $user->id
        ]);
        $job = factory(JobPoster::class)->states(['review_requested'])->create([
            'department_id' => 1
        ]);

        // Claim job poster.
        $response = $this->followingRedirects()
            ->actingAs($hrAdvisor->user)
            ->json('put', "api/jobs/$job->id/claim");
        $response->assertOk();
        $expectedIds = array_merge(
            ['job_poster_id' => $job->id],
            ['hr_advisor_id' => $hrAdvisor->id]
        );
        $this->assertDatabaseHas('claimed_jobs', $expectedIds);

        // Unclaim job poster.
        $response = $this->followingRedirects()
            ->actingAs($hrAdvisor->user)
            ->json('delete', "api/jobs/$job->id/claim");
        $response->assertOk();
        $this->assertDatabaseMissing('claimed_jobs', $expectedIds);
    }

    public function testClaimUnclaimForAdvisor(): void
    {
        $user = factory(User::class)->create([
            'department_id' => 1,
            'user_role_id' => 4
        ]);
        $hrAdvisor = factory(HrAdvisor::class)->create([
            'user_id' => $user->id
        ]);
        $job = factory(JobPoster::class)->states(['review_requested'])->create([
            'department_id' => 1
        ]);

        // Claim job poster.
        $response = $this->followingRedirects()
            ->actingAs($hrAdvisor->user)
            ->json('put', "api/hr-advisors/$hrAdvisor->id/claims/$job->id");
        $response->assertOk();
        $expectedIds = array_merge(
            ['job_poster_id' => $job->id],
            ['hr_advisor_id' => $hrAdvisor->id]
        );
        $this->assertDatabaseHas('claimed_jobs', $expectedIds);

        // Unclaim job poster.
        $response = $this->followingRedirects()
            ->actingAs($hrAdvisor->user)
            ->json('delete', "api/hr-advisors/$hrAdvisor->id/claims/$job->id");
        $response->assertOk();
        $this->assertDatabaseMissing('claimed_jobs', $expectedIds);
    }

    public function testAdvisorInWrongDeptCannotClaim(): void
    {
        $user = factory(User::class)->create([
            'department_id' => 1,
            'user_role_id' => 4
        ]);
        $hrAdvisor = factory(HrAdvisor::class)->create([
            'user_id' => $user->id
        ]);
        $job = factory(JobPoster::class)->states(['review_requested'])->create([
            'department_id' => 2
        ]);

        // Claim job poster.
        $response = $this->followingRedirects()
            ->actingAs($hrAdvisor->user)
            ->json('put', "api/jobs/$job->id/claim");
        $response->assertStatus(403);

        $advisorSpecificResponse = $this->followingRedirects()
            ->actingAs($hrAdvisor->user)
            ->json('put', "api/hr-advisors/$hrAdvisor->id/claims/$job->id");
        $advisorSpecificResponse->assertStatus(403);
    }

    public function testClaimUnclaimForAdvisorFailsForOtherUsers(): void
    {
        $user = factory(User::class)->create([
            'department_id' => 1,
            'user_role_id' => 4
        ]);
        $hrAdvisor = factory(HrAdvisor::class)->create([
            'user_id' => $user->id
        ]);
        $job = factory(JobPoster::class)->states(['review_requested'])->create([
            'department_id' => 1,
        ]);

        $otherUser = factory(HrAdvisor::class)->create();

        // Claim job poster, logged in as different user.
        $response = $this->followingRedirects()
            ->actingAs($otherUser->user)
            ->json('put', "api/hr-advisors/$hrAdvisor->id/claims/$job->id");
        $response->assertStatus(403);

        // Add a job claim that we can unclaim
        $response = $this->followingRedirects()
            ->actingAs($hrAdvisor->user)
            ->json('put', "api/hr-advisors/$hrAdvisor->id/claims/$job->id");
        $response->assertOk();

        // Unclaim job poster, logged in as different user.
        $response = $this->followingRedirects()
            ->actingAs($otherUser->user)
            ->json('delete', "api/hr-advisors/$hrAdvisor->id/claims/$job->id");
        $response->assertStatus(403);
    }
}
