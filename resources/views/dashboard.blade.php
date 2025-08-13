@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-6">Active Bookings</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div id="activeBookings" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <!-- Active bookings will be populated here via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch active bookings
    fetch('/api/bookings')
        .then(response => response.json())
        .then(bookings => {
            const today = new Date().toISOString().slice(0,10);
            const activeBookings = bookings.filter(booking => booking.date >= today);

            const container = document.getElementById('activeBookings');

            if (activeBookings.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8 text-gray-500">
                        No active bookings found
                    </div>
                `;
                return;
            }

            container.innerHTML = activeBookings.map(booking => `
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-lg">${booking.room}</h3>
                        <span class="px-2 py-1 text-xs rounded ${
                            booking.date === today ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                        }">${booking.date === today ? 'Today' : 'Upcoming'}</span>
                    </div>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p><span class="font-medium">Date:</span> ${booking.date}</p>
                        <p><span class="font-medium">Time:</span> ${booking.start_time} - ${booking.end_time}</p>
                        <p><span class="font-medium">Booked By:</span> ${booking.user?.name ?? 'Unknown'}</p>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error fetching bookings:', error);
            document.getElementById('activeBookings').innerHTML = `
                <div class="col-span-full text-center py-8 text-red-500">
                    Error loading bookings
                </div>
            `;
        });
});
</script>
@endsection
