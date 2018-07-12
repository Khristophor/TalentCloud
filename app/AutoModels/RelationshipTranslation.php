<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 12 Jul 2018 22:39:27 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class RelationshipTranslation
 * 
 * @property int $id
 * @property int $relationship_id
 * @property string $locale
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Relationship $relationship
 *
 * @package App\Models
 */
class RelationshipTranslation extends Eloquent
{
	protected $casts = [
		'relationship_id' => 'int'
	];

	protected $fillable = [
		'relationship_id',
		'locale',
		'value'
	];

	public function relationship()
	{
		return $this->belongsTo(\App\Models\Relationship::class);
	}
}
