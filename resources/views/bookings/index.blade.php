@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Meeting Room Booking</h1>

  {{-- Filters (optional feature) --}}
  <div class="flex gap-3 mb-4">
    <select id="filterRoom" class="border px-3 py-2 rounded">
      <option value="">All Rooms</option>
      <option>Room A</option>
      <option>Room B</option>
    </select>
    <input type="date" id="filterDate" class="border px-3 py-2 rounded" />
    <button id="clearFilters" class="border px-3 py-2 rounded">Clear</button>
    <button id="showMine" class="border px-3 py-2 rounded">My Bookings</button>
    <button id="showAll" class="border px-3 py-2 rounded hidden">All Bookings</button>
  </div>

  {{-- Create / Edit form --}}
  <form id="bookingForm" class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
    @csrf
    <select name="room" class="border px-3 py-2 rounded" required>
      <option value="Room A">Room A</option>
      <option value="Room B">Room B</option>
    </select>
    <input type="date" name="date" class="border px-3 py-2 rounded" required>
    <input type="time" name="start_time" class="border px-3 py-2 rounded" required>
    <input type="time" name="end_time" class="border px-3 py-2 rounded" required>
    <button class="bg-black text-white px-4 py-2 rounded" id="submitBtn">Add</button>
    <button type="button" class="border px-4 py-2 rounded hidden" id="cancelEdit">Cancel</button>
  </form>

  {{-- Bookings table --}}
  <table class="w-full border" id="bookingsTable">
    <thead>
      <tr class="bg-gray-100">
        <th class="p-2 cursor-pointer" data-sort="room">Room</th>
        <th class="p-2 cursor-pointer" data-sort="date">Date</th>
        <th class="p-2 cursor-pointer" data-sort="start_time">Start</th>
        <th class="p-2 cursor-pointer" data-sort="end_time">End</th>
        <th class="p-2">Booked By</th>
        <th class="p-2">Status</th>
        <th class="p-2">Actions</th>
      </tr>
    </thead>
    <tbody id="tbody"></tbody>
  </table>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let data = [];
let mineOnly = false;
let editingId = null;
let currentSort = { key: 'date', dir: 'asc' };

const tbody = document.getElementById('tbody');
const form = document.getElementById('bookingForm');
const cancelEditBtn = document.getElementById('cancelEdit');
const submitBtn = document.getElementById('submitBtn');

function fetchData() {
  const url = mineOnly ? '/api/bookings/mine' : '/api/bookings';
  return fetch(url).then(r => r.json()).then(json => {
    data = json;
    render();
  });
}
function render() {
  // Filter
  const fRoom = document.getElementById('filterRoom').value;
  const fDate = document.getElementById('filterDate').value;

  let rows = data.filter(b => (!fRoom || b.room === fRoom) && (!fDate || b.date === fDate));

  // Sort (JS requirement)
  rows.sort((a,b) => {
    const k = currentSort.key;
    let A = a[k], B = b[k];
    // Ensure date+time sorts properly
    if (k === 'date') { A = a.date; B = b.date; }
    if (k === 'start_time' || k === 'end_time') { A = a.date+' '+a[k]; B = b.date+' '+b[k]; }
    return (A < B ? -1 : A > B ? 1 : 0) * (currentSort.dir === 'asc' ? 1 : -1);
  });

  // Build rows
  const today = new Date().toISOString().slice(0,10);
  tbody.innerHTML = rows.map(b => {
    const isToday = (b.date === today);
    const trClass = isToday ? 'style="background:#fff7d6;"' : ''; // highlight today's bookings
    const myBooking = @json(auth()->check() ? auth()->id() : null) === b.user_id;
    const actions = myBooking
      ? `<button data-id="${b.id}" class="edit px-2 py-1 border rounded mr-2">Edit</button>
         <button data-id="${b.id}" class="del px-2 py-1 border rounded">Delete</button>`
      : '';
    return `<tr ${trClass}>
      <td class="p-2">${b.room}</td>
      <td class="p-2">${b.date}</td>
      <td class="p-2">${b.start_time}</td>
      <td class="p-2">${b.end_time}</td>
      <td class="p-2">${b.user?.name ?? ''}</td>
      <td class="p-2">${b.status}</td>
      <td class="p-2">${actions}</td>
    </tr>`;
  }).join('');
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(form);
  const payload = Object.fromEntries(fd.entries());

  const method = editingId ? 'PUT' : 'POST';
  const url = editingId ? `/api/bookings/${editingId}` : '/api/bookings';

  const res = await fetch(url, {
    method, headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  if (!res.ok) {
    const msg = (await res.json()).message ?? 'Validation error';
    alert(msg); // show conflict/validation
    return;
  }

  // instant update
  editingId = null;
  submitBtn.textContent = 'Add';
  cancelEditBtn.classList.add('hidden');
  form.reset();
  await fetchData();
});

// Delegated actions
tbody.addEventListener('click', async (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const id = btn.getAttribute('data-id');

  if (btn.classList.contains('edit')) {
    const b = data.find(x => x.id == id);
    if (!b) return;
    form.room.value = b.room;
    form.date.value = b.date;
    form.start_time.value = b.start_time;
    form.end_time.value = b.end_time;
    editingId = id;
    submitBtn.textContent = 'Update';
    cancelEditBtn.classList.remove('hidden');
  }

  if (btn.classList.contains('del')) {
    if (!confirm('Delete this booking?')) return;
    const res = await fetch(`/api/bookings/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrf }
    });
    if (res.ok) await fetchData();
  }
});

cancelEditBtn.addEventListener('click', () => {
  editingId = null; form.reset(); submitBtn.textContent = 'Add';
  cancelEditBtn.classList.add('hidden');
});

// Sorting (click headers)
document.querySelectorAll('#bookingsTable thead th[data-sort]').forEach(th => {
  th.addEventListener('click', () => {
    const k = th.getAttribute('data-sort');
    currentSort.dir = currentSort.key === k && currentSort.dir === 'asc' ? 'desc' : 'asc';
    currentSort.key = k;
    render();
  });
});

// Filters
document.getElementById('filterRoom').addEventListener('change', render);
document.getElementById('filterDate').addEventListener('change', render);
document.getElementById('clearFilters').addEventListener('click', () => {
  document.getElementById('filterRoom').value = '';
  document.getElementById('filterDate').value = '';
  render();
});

// My bookings
document.getElementById('showMine').addEventListener('click', () => {
  mineOnly = true; fetchData();
  document.getElementById('showMine').classList.add('hidden');
  document.getElementById('showAll').classList.remove('hidden');
});
document.getElementById('showAll').addEventListener('click', () => {
  mineOnly = false; fetchData();
  document.getElementById('showAll').classList.add('hidden');
  document.getElementById('showMine').classList.remove('hidden');
});

// Initial load and auto-refresh status every minute
fetchData();
setInterval(render, 60000);
</script>
@endsection
