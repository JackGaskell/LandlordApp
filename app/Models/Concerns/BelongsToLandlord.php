<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToLandlord
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function landlord(): BelongsTo
    {
        return $this->user();
    }

    public function scopeForLandlord(Builder $query, User|int $landlord): Builder
    {
        $landlordId = $landlord instanceof User ? $landlord->id : $landlord;

        return $query->where($this->getTable().'.user_id', $landlordId);
    }
}
