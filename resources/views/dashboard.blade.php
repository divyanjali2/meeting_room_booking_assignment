@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="bookingsDisplay" x-init="fetchBookings()">
        <h1 class="text-2xl font-bold mb-6">Active Bookings</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                {{-- Debug info --}}
                <div x-show="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relativ
                e mb-4">
                    <span x-text="error"></span>
                </div>

                {{-- Loading indicator --}}
                <div x-show="loading"
                    class="flex justify-center items-center py-8">
                    <svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- No bookings message --}}
                <div x-show="!loading && bookings.length === 0" class="text-center py-8 text-gray-500"> No active bookings found</div>

                {{-- Bookings grid --}}
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3" x-show="!loading && bookings.length > 0">
                    <template x-for="booking in bookings" :key="booking.id">
                        <div class="bg-gray-50 p-4 rounded-lg border transition-all duration-300 hover:shadow-md">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold" x-text="booking.room"></span>
                                <span x-show="isToday(booking.date)" class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">
                                    Today
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <div class="mb-1">
                                    <span class="font-medium">Date:</span>
                                    <span x-text="formatDate(booking.date)"></span>
                                </div>
                                <div class="mb-1">
                                    <span class="font-medium">Time:</span>
                                    <span x-text="booking.start_time + ' - ' + booking.end_time"></span>
                                </div>
                                <div>
                                    <span class="font-medium">Booked by:</span>
                                    <span x-text="booking.user?.name || 'Unknown'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingsDisplay', () => ({
                bookings: [],
                loading: true,
                error: null,

                async fetchBookings() {
                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch('/api/bookings');
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const data = await response.json();
                        // Filter out past bookings
                        const now = new Date();
                        this.bookings = data.filter(booking => {
                            const bookingDate = new Date(booking.date + ' ' + booking.end_time);
                            return bookingDate > now;
                        });
                    } catch (error) {
                        console.error('Error:', error);
                        this.error = 'Failed to load bookings. Please try again.';
                    } finally {
                        this.loading = false;
                    }

                    // Refresh every minute
                    setTimeout(() => this.fetchBookings(), 60000);
                },

                isToday(date) {
                    const today = new Date().toISOString().slice(0, 10);
                    return date === today;
                },

                formatDate(dateStr) {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }));
        });
    </script>
@endsection
