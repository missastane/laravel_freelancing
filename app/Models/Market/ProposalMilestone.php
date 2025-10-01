<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ProposalMilestone",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="عنوان مرحله"),
 *     @OA\Property(property="description", type="string", example= "من می توانم در مرحله نخست پلتفرم درخواستی شما را با فریم ورک لاراول پیاده سازی و اجرا کنم."),
 *     @OA\Property(property="amount", type="decimal", example=70000000.000),
 *     @OA\Property(property="duration_time", type="integer", example=3),
 *     @OA\Property(property="due_date", type="string", format="datetime",description="due_date datetime", example="2025-02-22T14:30:00Z"),
 *    
 *     )
 */
class ProposalMilestone extends Model
{
    use HasFactory;
      protected static function newFactory()
    {
        return \Database\Factories\ProposalMilestoneFactory::new();
    }

    protected $fillable = ['proposal_id', 'title', 'description', 'amount', 'duration_time', 'due_date'];

    protected function casts()
    {
        return ['due_date' => 'datetime'];
    }
    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id');
    }
   
}
