<?php

namespace App\Models\Market;

use App\Models\Market\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Skill",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="persian_title", type="string", example="ورد"),
 *     @OA\Property(property="original_title", type="string", example="word"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 * )
 */
class Skill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['persian_title', 'original_title'];

    public function portfolios()
    {
        return $this->belongsToMany(Skill::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
