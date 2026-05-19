<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mailbox extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 0;
    public const STATUS_SENT  = 1;

    public const TARGET_ALL   = 0;
    public const TARGET_ROLES = 1;
    public const TARGET_USER  = 2;

    protected $fillable = [
        'sender_id',
        'subject',
        'body',
        'status',
        'target_type',
        'target_roles',
        'target_user_id',
    ];

    protected $casts = [
        'target_roles' => 'array',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mailbox_user')
            ->withPivot('read_at', 'is_deleted')
            ->withTimestamps();
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function resolveRecipientQuery(): Builder
    {
        return match ((int) $this->target_type) {
            self::TARGET_ROLES => User::query()->active()->whereIn('role', $this->target_roles ?? []),
            self::TARGET_USER  => User::query()->where('id', $this->target_user_id),
            default            => User::query()->active(),
        };
    }

    /**
     * Returns whether the given user has read this message.
     * Uses the already-eager-loaded `recipients` collection when available
     * to avoid extra queries per row.
     */
    public function isReadByUser(int $userId): bool
    {
        return $this->recipients
            ->where('id', $userId)
            ->first()?->pivot?->read_at !== null;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeDrafts(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeIsread(Builder $query): Builder
    {
        return $query->whereHas('recipients', function (Builder $q) {
            $q->where('user_id', auth()->id())->whereNotNull('read_at');
        });
    }
}
