<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Lumos Invitation Studio

A SaaS digital wedding invitation platform built with Laravel. Couples can create, customize, and share beautiful digital wedding invitations with their guests, manage RSVPs, and track guest engagement.

## Features

### For Couples
- **Create Invitations** -- Personalized digital wedding invitations with names, date, venue, and a unique shareable link
- **9 Theme Templates** -- Premium Gold, Minimal Light, Terracotta Bloom, Plum Parchment, Floral Garden, Beach Tropical, Rustic Boho, Royal Classic, Indian Royal
- **3 Languages** -- English, Sinhala, Tamil with full translation support
- **Background Music** -- 5 curated tracks to accompany the invitation
- **Guest Management** -- Add guests with WhatsApp numbers, categories, seat assignments, and delivery tracking
- **RSVP Tracking** -- Real-time accept/decline with guest notes and seat reservations
- **Photo Gallery** -- Upload couple photos and a "Love Story" section
- **Guest Shared Gallery** -- Guests can share their own photos
- **Wedding Events** -- Manage events with Google Maps links and ICS calendar downloads
- **Wedding Checklist** -- Track planning tasks with progress
- **3D Animated Envelope Gate** -- WebGL-powered wax seal envelope animation using Three.js

### For Guests
- **Animated Invitation Experience** -- Stunning 3D envelope opening animation
- **WhatsApp Verification** -- Simple identity verification via WhatsApp number
- **RSVP Submission** -- Accept or decline with optional note
- **Photo Sharing** -- Share photos from the wedding
- **Calendar Integration** -- Add events to Google Calendar or download ICS files

### Admin Panel
- **Couple Management** -- Toggle activation, view payment status
- **Payment Processing** -- Review bank slip uploads, approve/reject payments
- **Upgrade Requests** -- Manage package upgrades
- **Refund Management** -- Two-phase refund process with eligibility checks

## Tech Stack

| Component | Technology |
|---|---|
| **Backend** | Laravel 13, PHP 8.3 |
| **Frontend** | Alpine.js, Tailwind CSS, Vite |
| **Database** | MySQL (SQLite for local dev) |
| **3D Graphics** | Three.js (WebGL) |
| **Image Hosting** | Cloudinary |
| **Auth** | Laravel Breeze with role-based access (admin/couple) |
| **Email** | Gmail SMTP |
| **Deployment** | Docker / Vercel (serverless) |

## Requirements

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL or SQLite
- Cloudinary account (for image hosting)
- reCAPTCHA v2 keys (for registration)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd invite
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure `.env`**
   - Set your database credentials
   - Add Cloudinary credentials (`CLOUDINARY_URL`)
   - Configure mail settings
   - Add reCAPTCHA keys

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   composer dev
   ```
   This runs the artisan server, queue listener, Vite dev server, and log viewer concurrently.

### Quick Setup

```bash
composer setup
```
This single command runs install, key generation, migrations, npm install, and Vite build.

## Docker

```bash
docker build -t lumos-invitation .
docker run -p 8080:80 lumos-invitation
```

## Database Schema

| Table | Description |
|---|---|
| `users` | User accounts with roles (admin/couple), package plans, payment status |
| `weddings` | Wedding details, themes, language, music, slugs |
| `guests` | Guest list with RSVP, tracking tokens, seat assignments |
| `events` | Wedding events (Poruwa, Reception, etc.) |
| `galleries` | Couple photo gallery |
| `guest_galleries` | Guest-shared photos |
| `tasks` | Wedding planning checklist |

## Pricing Plans

| Plan | Price (LKR) | Seats |
|---|---|---|
| **Basic** | Rs. 2,500 | 150 |
| **Standard** | Rs. 5,000 | 300 |
| **Premium** | Rs. 10,000 | Unlimited + Guest Gallery |

## Contributing

Thank you for considering contributing to the Lumos Invitation Studio! Please follow standard Laravel contribution guidelines.

## License

This project is licensed under the MIT License. The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
