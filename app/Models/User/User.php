<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Market\Conversation;
use App\Models\Market\FinalFile;
use App\Models\Market\Message;
use App\Models\Market\MessageUserStatus;
use App\Models\Market\Portfolio;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\Market\Subscription;
use App\Models\Market\UserEducation;
use App\Models\Market\UserSubscription;
use App\Models\Market\WorkExperience;
use App\Models\Payment\Payment;
use App\Models\Payment\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="username", type="string", example="ایمان"),
 *     @OA\Property(property="email", type="string", example="missastaneh@yahoo.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", description="email verify datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="mobile", type="string", example="09125478963"),
 *     @OA\Property(property="mobile_verified_at", type="string", format="date-time", description="mobile verify datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="first_name", type="string", example="ایمان"),
 *     @OA\Property(property="last_name", type="string", example="مدائنی"),
 *     @OA\Property(property="national_code", type="string", example="2732548965"),
 *     @OA\Property(property="gender", type="string", description="User gender: 'male' if 1, 'female' if 2", example="مذکر"),
 *     @OA\Property(property="birth_date", type="string", format="date-time", description="birth datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="avatar_photo", type="string", format="uri", example="\path\image.jpg"),
 *     @OA\Property(property="activation", type="string", description="Activation Value: 'active' if 1, 'inactive' if 2", example="فعال"),
 *     @OA\Property(property="activation_date", type="string", format="date-time", description="activation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="active_role", type="string", example="کارفرما"),
 *     @OA\Property(property="about_me", type="string", example="رشته اصلی من ریاضی است"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="creation datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="update datetime", example="2025-02-22T10:00:00Z"),
 *     @OA\Property(
 *          property="roles",
 *          type="array",
 *          description="Array of related roles with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="کارفرما")
 *               )
 *        ),
 *     @OA\Property(
 *          property="permissions",
 *          type="array",
 *          description="Array of related permissions with both ID and name",
 *             @OA\Items(
 *                  type="object",
 *                  @OA\Property(property="id", type="integer", example=3),
 *                  @OA\Property(property="name", type="string", example="edit-post")
 *               )
 *        ),
 * )
 */
class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, HasRoles, HasPermissions, SoftDeletes;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'national_code',
        'profile_photo_path',
        'activation',
        'user_type',
        'active_role',
        'about_me',
        'birth_date',
        'gender',
        'mobile',
        'mobile_verified_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'user_type',
        'activation',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'mobile_verified_at' => 'datetime',
            'birth_date' => 'datetime',
            'activation_date' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getUserTypeValueAttribute()
    {
        return match ($this->user_type) {
            1 => 'کاربر',
            2 => 'ادمین',
            default => 'نامشخص',
        };
    }

    public function getActivationValueAttribute()
    {
        if ($this->activation == 1) {
            return 'کاربر فعال';
        } else {
            return 'کاربر غیرفعال';
        }
    }
    public function getGenderValueAttribute()
    {
        switch ($this->gender) {
            case 1:
                $result = 'مذکر';
                break;
            case 2:
                $result = 'مؤنث';
                break;
            default:
                $result = null;
                break;
        }
        return $result;
    }

    public function userEducations()
    {
        return $this->hasMany(UserEducation::class);
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'freelancer_id');
    }
    public function portfolios()
    {
        return $this->hasMany(Portfolio::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function freelancerConversations()
    {
        return $this->hasMany(Conversation::class, 'employee_id');
    }

    public function employerConversations()
    {
        return $this->hasMany(Conversation::class, 'employer_id');
    }

    public function messageStatuses()
    {
        return $this->hasMany(MessageUserStatus::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }
    public function finalFiles()
    {
        return $this->hasMany(FinalFile::class);
    }
    public function rates()
    {
        return $this->morphMany('App\Models\Rating', 'ratable');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }
    public function activeSubscription(): ?UserSubscription
    {
        switch ($this->active_role) {
            case 'freelancer':
                $targetType = Proposal::class;
                break;
            case 'employer':
                $targetType = Project::class;
                break;
            default:
                $targetType = null;
                break;
        }

        return $this->subscriptions()
            ->where('status', 2)
            ->where('end_date', '>', now())
            ->when($targetType, function ($q) use ($targetType) {
                $q->whereHas('subscription', function ($q2) use ($targetType) {
                    $q2->where('target_type', $targetType);
                });
            })
            ->latest('started_at')
            ->first();
    }

    public function proposalsThisMonth(): int
    {
        return $this->proposals()->whereMonth('created_at', now())->count();
    }

    public function notificationsThisMonth(): int
    {
        return $this->notifications()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }
    public function notificationsOfTypeThisMonth(string $type): int
    {
        return $this->notifications()
            ->where('type', $type)
            ->whereMonth('created_at', now()->month)
            ->count();
    }



}
