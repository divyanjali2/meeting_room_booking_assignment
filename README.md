# Meeting Room Booking System

A simple system to manage meeting room bookings using Laravel and Alpine.js.

## Features

- Create, edit, and delete bookings (user-specific)
- Filter bookings by room and date
- Sort bookings by room, date, start time, and end time
- Live updates every minute
- Form validation and error handling
- Toastr notifications for success and error messages

## Installation

1. Clone the repository:

```bash
git clone <repository_url>
cd <project_folder>
Install dependencies:

bash
Copy
Edit
composer install
npm install
npm run dev
Configure environment:

Copy .env.example to .env

Set up database credentials

Generate application key:

bash
Copy
Edit
php artisan key:generate
Run migrations:

bash
Copy
Edit
php artisan migrate
Serve the application:

bash
Copy
Edit
php artisan serve
API Endpoints
Method	Endpoint	Description
GET	/api/bookings	Get all bookings
GET	/api/bookings/mine	Get bookings for logged-in user
POST	/api/bookings	Create a new booking
PUT	/api/bookings/{id}	Update a booking
DELETE	/api/bookings/{id}	Delete a booking

Frontend
Built with Alpine.js

Responsive table and booking form

Inline editing for user’s own bookings

Auto-refresh every minute

Notes
Only authenticated users can manage bookings

Time inputs use HH:mm 24-hour format

Room options can be updated in the form dropdown

Future Enhancements
Recurring bookings

Room capacity management

Admin dashboard

Real-time notifications with WebSockets
