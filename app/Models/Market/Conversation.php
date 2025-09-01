<?php

namespace App\Models\Market;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['employer_id', 'employee_id', 'conversation_context', 'conversation_context_id'];

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
        return $this->freelancer_id === $user->id || $this->employer_id === $user->id;
    }
}
