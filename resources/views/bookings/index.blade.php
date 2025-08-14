@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6" x-data="bookings">
        <h1 class="text-2xl font-bold mb-4">Meeting Room Booking</h1>

        {{-- Filters --}}
        <div class="flex gap-3 mb-4">
            <select x-model="filters.room" class="border px-3 py-2 rounded" style="width: 15%">
                <option value="">All Rooms</option>
                <option>Room A</option>
                <option>Room B</option>
            </select>
            <input type="date" x-model="filters.date" class="border px-3 py-2 rounded" />
            <button @click="clearFilters" class="border px-3 py-2 rounded">Clear</button>
            <button @click="toggleMyBookings" class="border px-3 py-2 rounded" x-text="mineOnly ? 'All Bookings' : 'My Bookings'"></button>
        </div>

    {{-- Create form --}}
    <form @submit.prevent="saveBooking" class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6" x-show="!editingId">
        @csrf
        <select x-model="form.room" class="border px-3 py-2 rounded" required>
            <option value="Room A">Room A</option>
            <option value="Room B">Room B</option>
        </select>
        <input type="date" x-model="form.date" :min="today" class="border px-3 py-2 rounded" required>
        <input type="time" x-model="form.start_time" class="border px-3 py-2 rounded" required>
        <input type="time" x-model="form.end_time" class="border px-3 py-2 rounded" required>
        <button type="submit" class="bg-black text-white px-4 py-2 rounded">Add</button>
    </form>

    @include('bookings.edit')
        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <template x-for="col in columns" :key="col.key">
                        <th class="p-2" :class="{ 'cursor-pointer': col.sortable }"
                            @click="col.sortable && sort(col.key)">
                            <span x-text="col.label"></span>
                            <template x-if="col.sortable && currentSort.key === col.key">
                                <span x-text="currentSort.dir === 'asc' ? '↑' : '↓'"></span>
                            </template>
                        </th>
                    </template>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                    <tr style="{{ $booking->date->format('Y-m-d') === date('Y-m-d') ? 'background:#fff7d6;' : '' }}" class="text-center">
                        <td class="p-2">{{ $booking->room }}</td>
                        <td class="p-2">{{ \Carbon\Carbon::parse($booking->date)->format('Y-m-d') }}</td>
                        <td class="p-2">{{ $booking->start_time }}</td>
                        <td class="p-2">{{ $booking->end_time }}</td>
                        <td class="p-2">{{ $booking->user->name ?? '' }}</td>
                        <td class="p-2">{{ $booking->status }}</td>
                        <td class="p-2">
                            @if(auth()->id() === $booking->user_id)
                                <div class="flex justify-center space-x-2">
                                    @if($booking->status !== 'Completed')
                                        <button onclick="editBooking({{ $booking->id }})" class="p-1 text-blue-600 hover:text-blue-800" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                    @endif
                                    <button onclick="deleteBooking({{ $booking->id }})" class="p-1 text-red-600 hover:text-red-800" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookings', () => ({
                data: [],
                mineOnly: false,
                editingId: null,
                errors: {},
                currentSort: { key: 'date', dir: 'asc' },
                filters: {
                    room: '',
                    date: ''
                },
                form: {
                    room: 'Room A',
                    date: '',
                    start_time: '',
                    end_time: ''
                },
                columns: [
                    { key: 'room', label: 'Room', sortable: true },
                    { key: 'date', label: 'Date', sortable: true },
                    { key: 'start_time', label: 'Start', sortable: true },
                    { key: 'end_time', label: 'End', sortable: true },
                    { key: 'user', label: 'Booked By', sortable: false },
                    { key: 'status', label: 'Status', sortable: false },
                    { key: 'actions', label: 'Actions', sortable: false }
                ],
                today: new Date().toISOString().slice(0,10),
                userId: @json(auth()->check() ? auth()->id() : null),

                init() {
                    this.fetchData();
                    // Auto refresh every minute
                    setInterval(() => this.fetchData(), 60000);
                },

                get filteredBookings() {
                    let rows = this.data.filter(b =>
                        (!this.filters.room || b.room === this.filters.room) &&
                        (!this.filters.date || b.date === this.filters.date)
                    );

                    // Sort
                    return rows.sort((a, b) => {
                        const k = this.currentSort.key;
                        let A = a[k], B = b[k];
                        if (k === 'date') { A = a.date; B = b.date; }
                        if (k === 'start_time' || k === 'end_time') {
                            A = a.date + ' ' + a[k];
                            B = b.date + ' ' + b[k];
                        }
                        return (A < B ? -1 : A > B ? 1 : 0) *
                            (this.currentSort.dir === 'asc' ? 1 : -1);
                    });
                },

                async fetchData() {
                    const url = this.mineOnly ? '/api/bookings/mine' : '/api/bookings';
                    const response = await fetch(url);
                    this.data = await response.json();
                },

                async saveBooking(e) {
                    const method = this.editingId ? 'PUT' : 'POST';
                    const url = this.editingId ?
                            `/api/bookings/${this.editingId}` :
                            '/api/bookings';

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        // Format times to ensure HH:mm format
                        const formatTime = (time) => {
                            if (!time) return '';
                            const [hours, minutes] = time.split(':');
                            return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
                        };

                        const response = await fetch(url, {
                            method,
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                room: this.form.room,
                                date: this.form.date,
                                start_time: formatTime(this.form.start_time),
                                end_time: formatTime(this.form.end_time)
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            if (response.status === 422) {
                                this.errors = data.errors || {};
                                toastr.error(data.message || 'Please check the form for errors');
                                return;
                            }
                            throw new Error(data.message || 'An error occurred');
                        }

                        this.editingId = null;
                        this.errors = {};
                        this.resetForm();
                        await this.fetchData();
                        toastr.success('Booking saved successfully');
                    } catch (error) {
                        toastr.error(error.message || 'An error occurred while saving the booking');
                    }
                },

                async deleteBooking(booking) {
                    if (!confirm('Delete this booking?')) return;

                    try {
                        const response = await fetch(`/api/bookings/${booking.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            await this.fetchData();
                            toastr.success('Booking deleted successfully');
                        }
                    } catch (error) {
                        toastr.error('Error deleting booking');
                    }
                },

                editBooking(booking) {
                    const formatTimeForInput = (time) => {
                        if (!time) return '';
                        const [hours, minutes] = time.split(':');
                        return `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
                    };

                    this.form = {
                        room: booking.room,
                        date: booking.date,
                        start_time: formatTimeForInput(booking.start_time),
                        end_time: formatTimeForInput(booking.end_time)
                    };
                    this.editingId = booking.id;
                },

                cancelEdit() {
                    this.editingId = null;
                    this.resetForm();
                },

                resetForm() {
                    this.form = {
                        room: 'Room A',
                        date: '',
                        start_time: '',
                        end_time: ''
                    };
                    this.errors = {};
                },

                clearFilters() {
                    this.filters.room = '';
                    this.filters.date = '';
                },

                toggleMyBookings() {
                    this.mineOnly = !this.mineOnly;
                    this.fetchData();
                },

                sort(key) {
                    this.currentSort.dir = this.currentSort.key === key &&
                                        this.currentSort.dir === 'asc' ? 'desc' : 'asc';
                    this.currentSort.key = key;
                },

                isMyBooking(booking) {
                    return this.userId === booking.user_id;
                }
            }));
        });
    </script>
@endsection
