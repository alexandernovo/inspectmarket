<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMINISTRATOR = 'ADMINISTRATOR';

    public const ROLE_TREASURER = 'TREASURER';

    public const ROLE_CLERK = 'CLERK';

    public const ROLE_INSPECTOR = 'INSPECTOR';

    public const ROLE_TENANT = 'TENANT';

    protected $table = 'users';

    public $timestamps = true;

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'username',
        'designation',
        'email',
        'address',
        'phone_num',
        'status',
        'password',
        'usertype',
        'profile',
        'background',
        'phone_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->firstname,
            $this->middlename,
            $this->lastname,
        ])->filter()->implode(' '));
    }

    public function getRoleSlugAttribute(): string
    {
        return strtolower((string) $this->usertype);
    }

    public function isRole(string $role): bool
    {
        return strtoupper((string) $this->usertype) === strtoupper($role);
    }

    public function stallApplications(): HasMany
    {
        return $this->hasMany(StallApplication::class, 'tenant_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'tenant_id');
    }

    public function inspectionRequests(): HasMany
    {
        return $this->hasMany(LivestockInspection::class, 'tenant_id');
    }

    public function assignedInspections(): HasMany
    {
        return $this->hasMany(LivestockInspection::class, 'inspector_id');
    }

    public function cashTicketCollections(): HasMany
    {
        return $this->hasMany(CashTicketCollection::class, 'collector_id');
    }

    public function cashTicketAssignments(): HasMany
    {
        return $this->hasMany(CashTicketAssignment::class, 'collector_id');
    }

    public function marketNotifications(): HasMany
    {
        return $this->hasMany(MarketNotification::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(MarketMessage::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(MarketMessage::class, 'recipient_id');
    }
}
