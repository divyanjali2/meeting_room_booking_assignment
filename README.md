# 🏢 Meeting Room Booking

This module is a **Laravel + Alpine.js**-based interface for managing **meeting room bookings**. Users can **create, edit, delete**, filter by room and date, and view only their own bookings.

---

## ✨ Features

### 📅 Booking Management
- ➕ Create new bookings with **room, date, start time, and end time**.
- ✏️ Edit existing bookings (**only your own bookings**).
- 🗑️ Delete bookings (**only your own bookings**).

### 🔍 Filters & Sorting
- 🏷️ Filter bookings by **room** and **date**.
- 👀 Toggle between viewing **all bookings** or **only your bookings**.
- ↕️ Sort bookings by **room, date, start time, or end time**.

### ⚡ Live Updates
- ⏱️ Bookings table refreshes **automatically every minute**.
- 📝 Edit modal appears inline with **pre-filled form**.

### ✅ Validation
- ⏰ Ensures correct **time formatting (`HH:mm`)**.
- ❌ Shows error messages if input is invalid.

---

## 🛠️ Technologies Used

- 🖥️ **Laravel** (backend & API)
- 💻 **Alpine.js** (frontend interactivity)
- 🎨 **Tailwind CSS** (styling)
- 🔔 **Toastr** (notifications)

---

## 🚀 Installation

1. Clone the repository.
2. Run:
   ```bash
   composer install
   npm install && npm run dev
Set up your .env file with database credentials:

env
Copy
Edit
APP_NAME="Meeting Room Booking"
DB_DATABASE=meeting_booking
Run migrations:

bash
Copy
Edit
php artisan migrate
Serve the app:

bash
Copy
Edit
php artisan serve
🖱️ Usage
Navigate to the Meeting Room Booking page.

Use the filters to narrow down bookings.

➕ Add new bookings using the form.

✏️ Edit or 🗑️ delete bookings you created.

⏱️ Bookings update automatically every minute.

📌 Notes
🔐 Only authenticated users can book, edit, or delete bookings.

🏢 Current implementation supports "Room A" and "Room B"; additional rooms can be added in the select options.

⏰ Time fields are validated to prevent incorrect formats.

📂 File Structure
resources/views/bookings.blade.php → Main bookings page

resources/views/bookings/edit.blade.php → Booking edit form/modal

routes/web.php → Routes for the bookings page

routes/api.php → API endpoints for bookings (CRUD)

app/Models/Booking.php → Booking model

app/Http/Controllers/BookingController.php → Handles booking API logic
