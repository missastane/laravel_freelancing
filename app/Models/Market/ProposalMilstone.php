<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalMilstone extends Model
{
    use HasFactory;

    protected $fillable = ['proposal_id', 'title', 'description', 'amount', 'duration_time', 'due_date'];

    protected function casts()
    {
        return ['due_date' => 'datetime'];
    }
    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }
   
    public function setDueDateAttribute()
    {
        $dueDate = now('Asia/Tehran')->addDays($this->duration_time)->getTimestamp();
        return $dueDate;
    }
}
