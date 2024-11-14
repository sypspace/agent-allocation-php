<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ResolveNotif extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer',
        'resolved_by',
        'service'
    ];

    protected $keyType = 'string';

    public $incrementing = false;
}
