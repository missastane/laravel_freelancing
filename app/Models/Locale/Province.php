<?php

namespace App\Models\Locale;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Province",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="تهران"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 * )
 */
class Province extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}
