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
use App\Models\Market\Skill;
use App\Models\Market\UserEducation;
use App\Models\Market\UserSubscription;
use App\Models\Market\WorkExperience;
use App\Models\Payment\FeaturePerchased;
use App\Models\Payment\Payment;
use App\Models\Payment\Wallet;
use App\Models\Ticket\TicketDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Casts\Attribute;


class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable, HasRoles, HasPermissions, SoftDeletes;
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

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
        'username_change_count',
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
        'username_change_count'
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
            1 => __('entity.user'),
            2 => __('entity.admin'),
            default => __('entity.undefined'),
        };
    }

    public function isAdmin(): bool
    {
        return (int) $this->user_type === 2;
    }

    public function getActivationValueAttribute()
    {
        if ($this->activation == 1) {
            return __('entity.active_user');
        } else {
            return __('entity.inactive_user');
        }
    }
    public function getGenderValueAttribute()
    {
        switch ($this->gender) {
            case 1:
                $result = __('entity.male');
                break;
            case 2:
                $result = __('entity.female');
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

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }

    public function departments()
    {
        return $this->belongsToMany(
            TicketDepartment::class,
            'admin_ticket_department',
            'admin_id',
            'department_id'
        )->withTimestamps();
        ;
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

    public function featurePerchased()
    {
        return $this->hasMany(FeaturePerchased::class, 'user_id');
    }

    public function authSessions(): HasMany
    {
        return $this->hasMany(AuthSession::class);
    }

    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 2) // وضعیت فعال
            ->where('end_date', '>', now()) // هنوز منقضی نشده
            ->latest('start_date')
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


    /**
     * Get the user's full name.
     * 
     * @return Attribute
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim("{$this->first_name} {$this->last_name}") ?: 'N/A',
        );
    }

}
