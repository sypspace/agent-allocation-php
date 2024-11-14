<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'app_id',
        'name',
        'email',
        'avatar_url',
        'room_id',
        'source',
        'is_new_session',
        'is_resolved',
        'latest_service',
        'extras',
        'candidate_agent'
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function scopeIsResolved(Builder $query): void
    {
        $query->where('is_resolved', true);
    }

    public function scopeUnResolved(Builder $query): void
    {
        $query->where('is_resolved', false);
    }

    public function scopeOrderByCreatedDate(Builder $query, $order = 'asc'): void
    {
        $query->orderBy('created_at', $order);
    }
}
