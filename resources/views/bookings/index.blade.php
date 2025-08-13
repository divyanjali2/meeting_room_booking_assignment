@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6" x-data="bookings">
        <h1 class="text-2xl font-bold mb-4">Meeting Room Booking</h1>

        {{-- Filters (optional feature) --}}
        <div class="flex gap-3 mb-4">
            <select x-model="filters.room" class="border px-3 py-2 rounded">
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
        <input type="date" x-model="form.date" class="border px-3 py-2 rounded" required>
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
                <template x-for="booking in filteredBookings" :key="booking.id">
                    <tr :style="booking.date === today ? 'background:#fff7d6;' : ''">
                        <td class="p-2" x-text="booking.room"></td>
                        <td class="p-2" x-text="booking.date"></td>
                        <td class="p-2" x-text="booking.start_time"></td>
                        <td class="p-2" x-text="booking.end_time"></td>
                        <td class="p-2" x-text="booking.user?.name ?? ''"></td>
                        <td class="p-2" x-text="booking.status"></td>
                        <td class="p-2">
                            <template x-if="isMyBooking(booking)">
                                <div>
                                    <button @click="editBooking(booking)"
                                            class="px-2 py-1 border rounded mr-2">Edit</button>
                                    <button @click="deleteBooking(booking)"
                                            class="px-2 py-1 border rounded">Delete</button>
                                </div>
                            </template>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('bookings', () => ({
                data: [],
                mineOnly: false,
                editingId: null,
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
                                start_time: this.form.start_time,
                                end_time: this.form.end_time
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Validation error');
                        }

                        this.editingId = null;
                        this.resetForm();
                        await this.fetchData();
                    } catch (error) {
                        alert(error.message || 'An error occurred while saving the booking');
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
                        }
                    } catch (error) {
                        alert('Error deleting booking');
                    }
                },

                editBooking(booking) {
                    this.form = {
                        room: booking.room,
                        date: booking.date,
                        start_time: booking.start_time,
                        end_time: booking.end_time
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
