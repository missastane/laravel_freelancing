<?php

namespace App\Models\Market;

use App\Models\Locale\Province;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="UserEducation",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="university_name", type="string", example="دانشگاه تهران"),
 *     @OA\Property(property="field_of_study", type="string", example="مهندسی کامپیوتر"),
 *     @OA\Property(property="start_year", type="string", format="datetime",description="start datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="end_year", type="string", format="datetime",description="end datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="datetime",description="delete datetime", example="2025-02-22T14:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(
 *          property="freelancer",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="username", type="string", example="ایمان"),
 *               )
 *            ),
 *     @OA\Property(
 *          property="province",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="تهران"),
 *               )
 *            )
 * )
 */
class UserEducation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'province_id', 'university_name', 'field_of_study', 'start_year', 'end_year'];

    protected function casts(): array
    {
        return [
            'start_year' => 'datetime',
            'end_year' => 'datetime',
        ];
    }

    public function freelancer()
    {
        return $this->BelongsTo(User::class, 'user_id');
    }
    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
}
