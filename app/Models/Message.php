<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id', 'recipient_id', 'body', 'sender_deleted', 'recipient_deleted', 'read_at'];
    protected $casts = ['sender_deleted' => 'boolean', 'recipient_deleted' => 'boolean', 'read_at' => 'datetime'];

    /** Messages between $a and $b that $a has not cleared. */
    public function scopeThread(Builder $q, int $a, int $b): Builder
    {
        return $q->where(function ($w) use ($a, $b) {
            $w->where(fn ($x) => $x->where('sender_id', $a)->where('recipient_id', $b)->where('sender_deleted', false))
              ->orWhere(fn ($x) => $x->where('sender_id', $b)->where('recipient_id', $a)->where('recipient_deleted', false));
        });
    }
}
