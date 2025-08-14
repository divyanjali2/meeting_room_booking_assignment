{{-- Edit Modal --}}
<div x-show="editingId" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4" @click.away="cancelEdit">
        <h2 class="text-xl font-bold mb-4">Edit Booking</h2>
        <form @submit.prevent="saveBooking" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Room</label>
                <select x-model="form.room" class="mt-1 block w-full border px-3 py-2 rounded" required>
                    <option value="Room A">Room A</option>
                    <option value="Room B">Room B</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" x-model="form.date" class="mt-1 block w-full border px-3 py-2 rounded" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Start Time</label>
                <input type="time" x-model="form.start_time" class="mt-1 block w-full border px-3 py-2 rounded" :class="{'border-red-500': errors.start_time}" required>
                <p class="text-red-500 text-sm mt-1" x-show="errors.start_time" x-text="errors.start_time"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">End Time</label>
                <input type="time" x-model="form.end_time" class="mt-1 block w-full border px-3 py-2 rounded" :class="{'border-red-500': errors.end_time}" required>
                <p class="text-red-500 text-sm mt-1" x-show="errors.end_time" x-text="errors.end_time"></p>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="cancelEdit" class="px-4 py-2 border rounded">Cancel</button>
                <button type="submit" class="bg-black text-white px-4 py-2 rounded">Update</button>
            </div>
        </form>
    </div>
</div>
