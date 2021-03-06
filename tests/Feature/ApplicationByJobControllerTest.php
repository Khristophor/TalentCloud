<?php

namespace Tests\Feature;

use App\Http\Controllers\ApplicationByJobController;
use App\Models\Applicant;
use App\Models\Degree;
use App\Models\JobApplication;
use App\Models\JobPoster;
use App\Models\Manager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationByJobControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndexViewFailsForPublic() : void
    {
        $manager = factory(Manager::class)->create();
        $job = factory(JobPoster::class)->state('closed')->create(['manager_id' => $manager->id]);
        $job->job_applications()->saveMany(factory(JobApplication::class, 5))->create([
            'job_poster_id' => $job->id
        ]);
        $response = $this->get(route('manager.jobs.applications', $job->id));
        $response->assertRedirect(route('manager.login'));
    }

    public function testIndexViewFailsForOtherManager() : void
    {
        $manager = factory(Manager::class)->create();
        $job = factory(JobPoster::class)->state('closed')->create(['manager_id' => $manager->id]);
        $job->job_applications()->saveMany(factory(JobApplication::class, 5))->create([
            'job_poster_id' => $job->id
        ]);
        $otherManager = factory(Manager::class)->create();
        $response = $this->actingAs($otherManager->user)
            ->get(route('manager.jobs.applications', $job->id));
        $response->assertStatus(403);
    }

    public function testIndexView() : void
    {
        $manager = factory(Manager::class)->create();
        $job = factory(JobPoster::class)->state('closed')->create(['manager_id' => $manager->id]);
        $job->job_applications()->saveMany(factory(JobApplication::class, 5))->create([
            'job_poster_id' => $job->id
        ]);
        $response = $this->actingAs($manager->user)
            ->get(route('manager.jobs.applications', $job->id));
        $response->assertOk();
        foreach ($job->job_applications as $application) {
            $response->assertSee(e($application->applicant->user->first_name));
            $response->assertSee(e($application->applicant->user->last_name));
        }
    }

    public function testSubmit() : void
    {
        $job = factory(JobPoster::class)->state('live')->create();
        $applicant = factory(Applicant::class)->create();
        $application = factory(JobApplication::class)->state('draft')->create([
            'job_poster_id' => $job->id,
            'applicant_id' => $applicant->id
        ]);
        $formData = [
            'submission_signature' => 'John Doe',
            'submission_date' => \Carbon\Carbon::now()->toDateTimeString(),
            'submit' => 'submit'
        ];
        $response = $this->actingAs($applicant->user)
            ->post(route('job.application.submit', $job), $formData);
        $response->assertRedirect(route('job.application.complete', $job));
        $this->assertEquals('submitted', $application->fresh()->application_status->name);
    }

    public function testSubmitFailsForIncompleteApplication() : void
    {
        $job = factory(JobPoster::class)->state('live')->create();
        $applicant = factory(Applicant::class)->create();
        $application = factory(JobApplication::class)->state('draft')->create([
            'job_poster_id' => $job->id,
            'applicant_id' => $applicant->id
        ]);
        $applicant->skill_declarations()->delete();
        $formData = [
            'submission_signature' => 'John Doe',
            'submission_date' => \Carbon\Carbon::now()->toDateTimeString(),
            'submit' => 'submit'
        ];
        $response = $this->actingAs($applicant->user)
            ->post(route('job.application.submit', $job), $formData);
        $response->assertSessionHasErrors();
        $this->assertEquals('draft', $application->fresh()->application_status->name);
    }

    public function testSubmitCopiesProfileData() : void
    {
        $job = factory(JobPoster::class)->state('live')->create();
        $applicant = factory(Applicant::class)->create();
        $applicant->degrees()->delete();
        $degree = factory(Degree::class)->create([
            'degreeable_id' => $applicant->id
        ]);
        $applicant->degrees()->save($degree);
        $application = factory(JobApplication::class)->state('draft')->create([
            'job_poster_id' => $job->id,
            'applicant_id' => $applicant->id
        ]);
        $formData = [
            'submission_signature' => 'John Doe',
            'submission_date' => \Carbon\Carbon::now()->toDateTimeString(),
            'submit' => 'submit'
        ];
        $this->actingAs($applicant->user)
            ->post(route('job.application.submit', $job), $formData);

        $copy = $application->degrees->first();
        $forgetFields = [
            'id',
            'created_at', 'updated_at',
            'degreeable_type', 'degreeable_id',
        ];
        $originalData = collect($degree->attributesToArray())->except($forgetFields);
        $copyData = collect($copy->attributesToArray())->except($forgetFields);

        $this->assertNull($application->degrees->find($degree->id));
        $this->assertNotEquals($degree->id, $copy->id);
        $this->assertEquals($originalData, $copyData);
    }

    public function testSubmitDoesntCopyForInvalidApplication() : void
    {
        $job = factory(JobPoster::class)->state('live')->create();
        $applicant = factory(Applicant::class)->create();
        $application = factory(JobApplication::class)->state('draft')->create([
            'job_poster_id' => $job->id,
            'applicant_id' => $applicant->id
        ]);
        $applicant->skill_declarations()->delete();
        $formData = [
            'submission_signature' => 'John Doe',
            'submission_date' => \Carbon\Carbon::now()->toDateTimeString(),
            'submit' => 'submit'
        ];
        $this->actingAs($applicant->user)
            ->post(route('job.application.submit', $job), $formData);

        $this->assertEmpty($application->degrees);
    }
}
