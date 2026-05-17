# ⚡ Taskify — Modern & Fully Reactive Task Management System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
</p>

---

**Taskify** is a professional, high-performance, and visually stunning Task Management System built using **Laravel 11**, **Vanilla CSS**, and **Axios.js**. It features a modern Single Page Application (SPA) feel, letting you manage your daily productivity seamlessly with **zero page refreshes**.

---

## ✨ Features

* ⚡ **Zero-Refresh SPA Experience:** Adding, updating, deleting, and toggling tasks/subtasks is done dynamically using Axios.js.
* 📊 **Live Stats Dashboard:** Real-time counter metrics (Total Tasks, Completed, Overdue) that update on the fly when tasks change.
* 📈 **Dynamic Subtask Progress:** Interactive progress bars on every task card showing exactly what percentage of subtasks are complete.
* 🎯 **Smart Navigation Filters:**
  * 🏠 **Dashboard:** Overall task feed with active filters.
  * ☀️ **Today:** Auto-filters tasks due today.
  * 📅 **Upcoming:** Tracks future deadlines.
  * ⭐ **Important:** High-priority task board.
  * ✅ **Completed:** Archives all accomplished work.
* 📁 **Category Manager:** Custom categorizations with dedicated management screens.
* 🎨 **Premium Glassmorphism UI:** Stunning dark/light aesthetic, tailored HSL color palettes, elegant animations, custom scrollbars, and fully responsive layouts.

---

## 🛠️ Tech Stack

* **Backend:** Laravel 11, PHP 8.3+
* **Frontend:** Blade, Vanilla JS, Axios.js, FontAwesome 6
* **Database:** SQLite (default) / MySQL
* **Styling:** Custom CSS (Custom design variables, flexible responsive grid, HSL-colors)

---

## 🚀 Installation & Local Setup

Get your local development server up and running in just a few minutes.

### Prerequisites
Make sure you have [PHP 8.2+](https://www.php.net/), [Composer](https://getcomposer.org/), and [Node.js](https://nodejs.org/) installed.

### 1. Clone the Repository
```bash
git clone https://github.com/kareemmramadan12121212-creator/Laravel_task_manager_api.git
cd Laravel_task_manager_api
```

### 2. Install PHP Dependencies
```bash
composer install
```

### 3. Install Frontend Assets
```bash
npm install
npm run build
```

### 4. Setup Environment Config
Copy the example environment file and generate the application key:
```bash
cp .env.example .env
php artisan key:generate
```

### 5. Setup Database
Create an empty SQLite database (default) or configure MySQL in your `.env`.
```bash
# For SQLite:
touch database/database.sqlite

# Run Migrations:
php artisan migrate
```

### 6. Serve the Application
```bash
php artisan serve
```
Visit the app locally at: `http://127.0.0.1:8000` or configure it via your favorite local environment (like [Laragon](https://laragon.org/) at `http://task_management_api.local`).

---

## 🌐 Exposing Externally (Ngrok Support)

To test the application on external mobile devices or share it with others, you can expose the local domain using **Ngrok**:

```bash
ngrok http task_management_api.local:80 --host-header=task_management_api.local
```

### Trusted Proxies Setup (Already Configured)
To prevent HTTPS redirect loops and keep routes securely absolute when shared via Ngrok, Taskify utilizes Laravel 11's trusted proxies inside `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
})
```

---

## 📝 License
This project is open-sourced software licensed under the [MIT license](LICENSE).
