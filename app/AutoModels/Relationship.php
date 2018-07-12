<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 12 Jul 2018 22:39:27 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Relationship
 * 
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $micro_references
 * @property \Illuminate\Database\Eloquent\Collection $relationship_translations
 *
 * @package App\Models
 */
class Relationship extends Eloquent
{
	protected $fillable = [
		'name'
	];

	public function micro_references()
	{
		return $this->hasMany(\App\Models\MicroReference::class);
	}

	public function relationship_translations()
	{
		return $this->hasMany(\App\Models\RelationshipTranslation::class);
	}
}
