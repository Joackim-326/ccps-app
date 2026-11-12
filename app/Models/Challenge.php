<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Challenge extends Model
{
    public const CATEGORIES = [
        'Results verification & Overall GPA generation',
        'Foreign Award Evaluation (FAE)',
        'Names Mismatch',
        'Transcript/Certificate request',
        'Other',
    ];

    public const IN_PROGRESS = 'in_progress';
    public const ATTENDED = 'attended';

    protected $fillable = ['reference', 'index_number', 'category', 'other_category', 'description', 'status', 'created_by', 'assigned_to', 'attended_at'];
    protected $casts = ['attended_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(function (Challenge $c) {
            $c->reference ??= 'CH-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
        });
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function getCategoryLabelAttribute(): string
    {
        return $this->category === 'Other' && $this->other_category ? 'Other: '.$this->other_category : $this->category;
    }

    /** Receptionists see "Pending" for what officers see as "In progress". */
    public function statusLabelFor(User $user): string
    {
        if ($this->status === self::ATTENDED) {
            return 'Attended';
        }
        return $user->isReceptionist() ? 'Pending' : 'In progress';
    }
}
