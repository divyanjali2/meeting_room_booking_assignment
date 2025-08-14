<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BookingController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
        $bookings = Booking::orderBy('id')->simplePaginate(10);

        return view('bookings.index', compact('bookings'));
    }

    public function list(Request $request) {
        // Return all bookings (for table) with user
        $today = now()->format('Y-m-d');
        $bookings = Booking::with('user')
            ->where('date', '>=', $today)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->json($bookings);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'room'       => ['required', Rule::in(['Room A','Room B'])],
            'date'       => ['required','date'],
            'start_time' => ['required','date_format:H:i'],
            'end_time'   => ['required','date_format:H:i','after:start_time'],
        ]);

        // Conflict check: same room & date, overlapping time
        $conflict = Booking::where('room', $data['room'])
            ->where('date', $data['date'])
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time',   '>', $data['start_time']);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Conflict: time overlaps an existing booking for this room.'
            ], 422);
        }

        $booking = $request->user()->bookings()->create($data);
        return response()->json($booking->load('user'), 201);
    }

    public function update(Request $request, Booking $booking) {
        $this->authorize('update', $booking);

        $data = $request->validate([
            'room'       => ['required', Rule::in(['Room A','Room B'])],
            'date'       => ['required','date'],
            'start_time' => ['required','date_format:H:i'],
            'end_time'   => ['required','date_format:H:i','after:start_time'],
        ]);

        $conflict = Booking::where('room', $data['room'])
            ->where('date', $data['date'])
            ->where('id', '!=', $booking->id)
            ->where(function ($q) use ($data) {
                $q->where('start_time', '<', $data['end_time'])
                  ->where('end_time',   '>', $data['start_time']);
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Conflict: time overlaps an existing booking for this room.'
            ], 422);
        }

        $booking->update($data);
        return response()->json($booking->refresh()->load('user'));
    }

    public function destroy(Request $request, Booking $booking) {
        $this->authorize('delete', $booking);
        $booking->delete();
        return response()->json(['ok' => true]);
    }

    public function mine(Request $request) {
        $bookings = $request->user()->bookings()->with('user')
            ->orderBy('date')->orderBy('start_time')->get();
        return response()->json($bookings);
    }
}
