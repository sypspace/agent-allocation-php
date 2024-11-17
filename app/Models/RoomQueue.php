<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomQueue extends Model
{
    protected $fillable = [
        'room_id',
        'agent_id',
        'status'
    ];
}
