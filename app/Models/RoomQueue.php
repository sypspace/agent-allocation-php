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

    public function getNextAttribute()
    {
        // Ambil antrean berikutnya berdasarkan status 'queued' dan urutan waktu
        $nextRoom = self::where('status', 'queued')
            ->where(function ($query) {
                $query->where('created_at', '>', $this->created_at)
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('created_at', '=', $this->created_at)
                            ->where('id', '>', $this->id);
                    });
            })
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->first();

        // Kembalikan `room_id` atau null jika tidak ada antrean berikutnya
        return $nextRoom ? $nextRoom : null;
    }
}
