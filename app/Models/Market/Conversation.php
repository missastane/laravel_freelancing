<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Conversation",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="employer", type="string", example="ali001"),
 *     @OA\Property(property="freelancer", type="string", example="ali006"),
 *     @OA\Property(property="status", type="string", example="مکالمه آزاد"),
 *     @OA\Property(property="messages", type="array",
 *        @OA\Items(ref="#/components/schemas/Message"),
 *      )
 * )
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['employer_id', 'employee_id', 'status', 'conversation_context', 'conversation_context_id'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function freelancer()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function employer()
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function hasUser(User $user)
    {
        return $this->employee_id === $user->id || $this->employer_id === $user->id;
    }

    public function getStatusValueAttribute()
    {
        switch ($this->status) {
            case 1:
                $result = 'مکالمه آزاد';
                break;
            case 2:
                $result = 'مکالمه بسته شده';
                break;
            case 3:
                $result = 'مکالمه آرشیو شده';
                break;
            default:
                $result = 'آزاد';
                break;
        }
        return $result;
    }
}
