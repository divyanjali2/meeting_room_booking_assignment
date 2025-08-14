<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = ['room', 'date', 'start_time', 'end_time'];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    protected $appends = ['status'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    // Status: Upcoming / Completed (no column needed)
    protected function status(): Attribute
    {
        return Attribute::get(function () {
            $end = Carbon::parse($this->date->toDateString().' '.$this->end_time, config('app.timezone'));
            return now(config('app.timezone'))->greaterThanOrEqualTo($end) ? 'Completed' : 'Upcoming';
        });
    }
}
