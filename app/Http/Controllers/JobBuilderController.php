<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Controller;

class JobBuilderController extends Controller
{
    /**
     * Show the Job Builder Intro page
     * @return \Illuminate\Http\Response
     */
    public function intro($jobId = null)
    {
        return view(
            'manager/job-builder-intro',
            ['title' => Lang::get('manager/job_builder.intro_title'), 'jobId' => $jobId]
        );
    }

    /**
     * Show the Job Builder Details page
     * @return \Illuminate\Http\Response
     */
    public function details($jobId = null)
    {
        return view(
            'manager/job-builder-details',
            ['title' => Lang::get('manager/job_builder.details_title'), 'jobId' => $jobId]
        );
    }
}