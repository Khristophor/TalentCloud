<?php

namespace App\Http\Controllers;

use App\Exceptions\StateMachineException;
use App\Models\JobPoster;
use App\Http\Resources\JobPoster as JobPosterResource;
use App\Models\Lookup\JobPosterStatus;
use App\Services\JobStatusTransitions;
use Illuminate\Http\Request;

class JobStatusController extends Controller
{
    protected function transitionJobStatus(Request $request, JobPoster $job, string $to)
    {
        $transitions = new JobStatusTransitions();

        $user = $request->user();
        $fromStatus = $job->job_poster_status;
        $from = $fromStatus->key;

        // Ensure state transition is legal.
        if (!$transitions->canTransition($user, $from, $to)) {
            throw new StateMachineException('Illegal state transition');
        }

        // Save new status on job.
        $toStatus = JobPosterStatus::where('key', $to)->first();
        $job->job_poster_status_id = $toStatus->id;
        $job->save();

        return ($request->ajax() || $request->wantsJson())
            ? new JobPosterResource($job->fresh())
            : back();
    }

    public function setJobStatus(Request $request, JobPoster $jobPoster, string $status)
    {
        // $status = $request->input('status');
        return $this->transitionJobStatus($request, $jobPoster, $status);
    }
}