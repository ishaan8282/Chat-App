# Real-Time Chat Application (`chat-app`)

A modern web-based real-time chat application built with **Laravel**, **Tailwind CSS**, and **Alpine.js**.

---

## 📌 Project Overview

This repository is designed to be a full-featured real-time chat platform. It enables registered users to converse with each other in one-on-one and/or group conversations with instantaneous message updates via WebSockets.

---

## 🚀 Current Status: What Has Been Achieved

As of now, the foundational architecture and authentication scaffolding of the project are in place:

1. **Framework & Scaffolding**:
   - Laravel application skeleton initialized with modern PHP standards.
   - Vite-based asset bundling pipeline configured.
2. **Authentication System (Laravel Breeze)**:
   - Full user authentication lifecycle configured (Registration, Login, Password Reset, Email Verification, and Password Confirmation).
   - User Profile management (updating name/email, updating password, and account deletion).
   - Authentication middleware protecting the dashboard and sensitive routes.
3. **Frontend UI Stack**:
   - **Tailwind CSS** integrated for styling.
   - **Alpine.js** integrated for reactive client-side micro-interactions.
   - Responsive Blade layout components (`x-app-layout`, `x-guest-layout`, navigation bar, modals, forms).
4. **Environment & Database Configuration**:
   - Database connection configured for **MySQL** (`DB_CONNECTION=mysql`, `DB_DATABASE=chat_app`).
   - Initial database migrations created for core tables (`users`, `sessions`, `cache`, `jobs`).
   - Default user factory and database seeder created (`database/seeders/DatabaseSeeder.php`).
5. **Testing Suite**:
   - PHPUnit configured with test suites covering authentication and profile features (`tests/Feature/Auth`, `tests/Feature/ProfileTest.php`).

---

## 🎯 Next Steps: What We Must Do

To turn this foundation into a fully functioning real-time chat application, follow this phased roadmap:

```
[Phase 1: Database & Models] ➔ [Phase 2: Real-time Broadcasting] ➔ [Phase 3: Backend Logic] ➔ [Phase 4: Chat UI] ➔ [Phase 5: Real-time & Polish]
```

### 1. Database Architecture & Models
- [ ] **Create Conversation / Chat Room Model & Migration**:
  - `conversations` (id, type: `direct` or `group`, title/name, timestamps).
- [ ] **Create Participant Model & Migration**:
  - `conversation_user` (pivot table: `conversation_id`, `user_id`, `role`, `last_read_at`).
- [ ] **Create Message Model & Migration**:
  - `messages` (id, `conversation_id`, `user_id`, `body`, `type` [text/attachment], `read_at`, timestamps).
- [ ] **Define Eloquent Relationships**:
  - `User` has many `conversations` (belongsToMany).
  - `Conversation` has many `messages` and belongsToMany `users`.
  - `Message` belongsTo `User` (sender) and belongsTo `Conversation`.

### 2. Real-Time Broadcasting Setup
- [ ] **Install Broadcasting & WebSocket Server**:
  - Run `php artisan install:broadcasting` to configure event broadcasting and create `routes/channels.php`.
  - Install **Laravel Reverb** (first-party high-performance WebSocket server) or configure **Pusher**:
    ```bash
    php artisan install:broadcasting --reverb
    ```
- [ ] **Install Client-Side WebSockets**:
  - Install `laravel-echo` and `pusher-js`:
    ```bash
    npm install --save-dev laravel-echo pusher-js
    ```
  - Configure Echo in `resources/js/echo.js` or `resources/js/app.js`.

### 3. Backend Controllers & Events
- [ ] **Create Chat Event**:
  - `app/Events/MessageSent.php` implementing `ShouldBroadcastNow` (or `ShouldBroadcast`).
  - Broadcast on a private channel, e.g., `chat.{conversation_id}` or `user.{user_id}`.
- [ ] **Authorize Channels in `routes/channels.php`**:
  - Ensure only users that belong to a conversation can listen to its channel:
    ```php
    Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
        return $user->conversations()->where('conversations.id', $conversationId)->exists();
    });
    ```
- [ ] **Create Controllers**:
  - `ConversationController`: List user conversations, initiate new chat with another user.
  - `MessageController`: Retrieve paginated messages for a conversation, store and broadcast new messages.

### 4. Frontend Chat User Interface
- [ ] **Build the Chat Layout (`resources/views/chat/index.blade.php`)**:
  - **Left Sidebar**: Search bar, list of active conversations, unread count badges, last message preview, and a button to start a new chat.
  - **Chat Area**: Header with recipient/group name & online status, scrollable message history feed, and message input bar.
  - **Message Input**: Textarea / input field, emoji picker, attachment button, and Send button.
- [ ] **Client State Handling with Alpine.js**:
  - Listen for `MessageSent` events using Echo and dynamically append new messages to the feed.
  - Auto-scroll to the latest message on arrival.
  - Optimistic UI updates when the sender clicks "Send".

### 5. Advanced Features & Polish
- [ ] **Presence & Typing Indicators**:
  - Use Whisper events (`Echo.private(...).whisper('typing', { ... })`) to show "User is typing...".
  - Presence channels to track online/offline status.
- [ ] **Read Receipts & Unread Counts**:
  - Update `last_read_at` when a conversation is opened.
- [ ] **File / Image Attachments**:
  - Support sending images and files stored via Laravel Storage.
- [ ] **Notifications**:
  - In-app desktop notifications or push notifications for background tabs.

---

## 🛠 Tech Stack

- **Backend**: [PHP 8.3+](https://www.php.net/), [Laravel 12/13](https://laravel.com)
- **Database**: [MySQL](https://www.mysql.com/) / MariaDB
- **Frontend**: [Blade Templates](https://laravel.com/docs/blade), [Alpine.js](https://alpinejs.dev/), [Tailwind CSS](https://tailwindcss.com/)
- **Build Tool**: [Vite](https://vitejs.dev/)
- **Broadcasting & WebSockets**: Laravel Reverb / Pusher, Laravel Echo

---

## ⚙️ Getting Started & Local Setup

If you or another developer are setting up this project on a new machine, follow these steps:

### Prerequisites
Make sure your system has the following installed:
- PHP >= 8.3 with extensions (`pdo_mysql`, `mbstring`, `xml`, `curl`)
- Composer (v2+)
- Node.js (v18+) & NPM
- MySQL Server

### Step-by-Step Installation

1. **Clone the Repository & Navigate to Directory**:
   ```bash
   cd /var/www/html/chat-app
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Install JavaScript Dependencies**:
   ```bash
   npm install
   ```

4. **Set Up the Environment File**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database Settings**:
   Open `.env` and verify your MySQL connection credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=chat_app
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

   Create the database in MySQL if it doesn't already exist:
   ```sql
   CREATE DATABASE chat_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

7. **Compile Frontend Assets**:
   ```bash
   npm run build
   # or for active development:
   npm run dev
   ```

8. **Start the Laravel Development Server**:
   ```bash
   php artisan serve
   ```
   Open [http://localhost:8000](http://localhost:8000) in your browser.

---

## 📂 Project Structure Overview

```
chat-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # ProfileController, Auth controllers (chat controllers to be added)
│   │   └── Requests/        # Form validation requests
│   └── Models/              # Eloquent models (User.php, future: Conversation.php, Message.php)
├── config/                  # Configuration files (database, auth, app, broadcasting)
├── database/
│   ├── factories/           # Model factories for testing
│   ├── migrations/          # Database schema migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── css/app.css          # Tailwind CSS styles
│   ├── js/app.js            # JavaScript / Alpine.js bootstrap
│   └── views/               # Blade views (layouts, auth, components, dashboard)
├── routes/
│   ├── web.php              # Web routes
│   ├── auth.php             # Breeze authentication routes
│   └── channels.php         # WebSocket private channel authorization (to be created)
├── tests/                   # Feature and Unit tests
└── vite.config.js           # Vite build configuration
```

---

## 💻 Useful Commands

| Task | Command |
|---|---|
| Run PHP dev server | `php artisan serve` |
| Run Vite asset watcher | `npm run dev` |
| Run Reverb WebSocket server | `php artisan reverb:start` |
| Run Queue worker | `php artisan queue:work` |
| Run Tests | `php artisan test` |
| Run Migrations | `php artisan migrate` |
| Rollback & Re-migrate | `php artisan migrate:fresh --seed` |
| Clear Application Cache | `php artisan optimize:clear` |

---

## 👥 Contributing & Conventions

1. Follow PSR-12 coding style for PHP. Run `vendor/bin/pint` to auto-format.
2. Ensure new feature endpoints have corresponding test coverage in `tests/Feature`.
3. Keep business logic inside action classes, services, or Eloquent models rather than fat controllers.
