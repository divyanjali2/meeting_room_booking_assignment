<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = ['room', 'date', 'start_time', 'end_time', 'status'];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saving(function ($booking) {
            if (!$booking->isDirty('status')) {
                $end = Carbon::parse($booking->date->toDateString().' '.$booking->end_time, config('app.timezone'));
                $booking->status = now(config('app.timezone'))->greaterThanOrEqualTo($end) ? 'Completed' : 'Upcoming';
            }
        });
    }
}
