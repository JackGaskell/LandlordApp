<?php

namespace App\Models;

use App\Enums\PaymentProofStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentProof extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentProofFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payment_history_id',
        'status',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'tenant_note',
        'claimed_paid_at',
        'tenant_marked_paid',
        'reviewed_at',
        'landlord_note',
        'reviewed_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentProofStatus::class,
            'size_bytes' => 'integer',
            'claimed_paid_at' => 'datetime',
            'tenant_marked_paid' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function paymentHistory(): BelongsTo
    {
        return $this->belongsTo(PaymentHistory::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === PaymentProofStatus::Pending;
    }

    public function isPendingReview(): bool
    {
        return $this->isPending();
    }

    public function isApproved(): bool
    {
        return $this->status === PaymentProofStatus::Approved;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentProofStatus::Pending);
    }

    public function scopeForLandlord(Builder $query, User|int $landlord): Builder
    {
        $landlordId = $landlord instanceof User ? $landlord->id : $landlord;

        return $query->whereIn('tenant_id', function ($sub) use ($landlordId) {
            $sub->select('id')->from('tenants')->where('user_id', $landlordId);
        });
    }

    public function scopeForTenant(Builder $query, Tenant|int $tenant): Builder
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return $query->where('tenant_id', $tenantId);
    }

    public function fileExists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }
}
