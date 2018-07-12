<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 12 Jul 2018 22:39:27 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class JobApplicationAnswer
 * 
 * @property int $id
 * @property int $job_poster_questions_id
 * @property int $job_application_id
 * @property string $answer
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\JobApplication $job_application
 * @property \App\Models\JobPosterQuestion $job_poster_question
 *
 * @package App\Models
 */
class JobApplicationAnswer extends Eloquent
{
	protected $casts = [
		'job_poster_questions_id' => 'int',
		'job_application_id' => 'int'
	];

	protected $fillable = [
		'job_poster_questions_id',
		'job_application_id',
		'answer'
	];

	public function job_application()
	{
		return $this->belongsTo(\App\Models\JobApplication::class);
	}

	public function job_poster_question()
	{
		return $this->belongsTo(\App\Models\JobPosterQuestion::class, 'job_poster_questions_id');
	}
}
