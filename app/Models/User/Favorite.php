<?php

namespace App\Models\User;

use App\Models\Market\Project;
use App\Models\Market\Proposal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Favorite",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(
 *          property="user",
 *          type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="first_name", type="string", example="راضیه"),
 *                  @OA\Property(property="last_name", type="string", example="آذری آستانه"),
 *               )
 *            ),
 *  @OA\Property(property="favoritable", type="object")
 * )
 */
class Favorite extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','favoritable_type','favoritable_id'];

    public function getFavoritableValueAttribute()
    {
        switch($this->Favoritable_type)
        {
            case 'َApp\\Models\\Market\\Proposal':
                $result = Proposal::where('id',$this->favoritable_id)
                ->select('id','description','total_amount','total_duration_time','status')
                ->with('milstones','user:id,username')->get();
                break;
            case 'َApp\\Models\\Market\\Project':
                 $result = Project::where('id',$this->favoritable_id)
                ->select('id','title','description','amount','duration_time','status')
                ->with('user:id,username')->get();
                break;
        }
        return $result;
    }
}
