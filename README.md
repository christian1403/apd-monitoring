# APD Monitor

A Laravel application built with Inertia.js, Vue 3, Tailwind CSS, and Laravel Fortify for authentication.

## Requirements

- PHP 8.3+
- Composer
- Node.js & pnpm (or npm)
- MySQL

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd apd-monitor
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
pnpm install
# or
npm install
```

### 4. Environment setup

Copy the example environment file and generate an application key:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Configure the database
To use MySQL or PostgreSQL instead, update the following variables in your `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apd_monitor
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 6. Run database migrations

```bash
php artisan migrate
```

To also seed the database with sample data:

```bash
php artisan migrate --seed
```

### 7. Build frontend assets

```bash
pnpm run build
# or
npm run build
```

## Running the Development Server

Use the Composer `dev` script to start all services (web server, queue worker, log viewer, and Vite) concurrently:

```bash
composer run dev
```

Or start each service individually:

```bash
php artisan serve          # Laravel development server
php artisan queue:listen   # Queue worker
php artisan pail           # Log viewer
pnpm run dev               # Vite dev server (HMR)
```

The application will be available at [http://localhost:8000](http://localhost:8000).

## One-command Setup

The project includes a `setup` script that runs all installation steps automatically:

```bash
composer run setup
```

This will install Composer dependencies, copy `.env.example`, generate an app key, run migrations, install Node dependencies, and build assets.

<!-- ## Running Tests

```bash
php artisan test
# or
./vendor/bin/pest
``` -->

<!-- ## Code Quality

```bash
# PHP linting (Laravel Pint)
composer run lint

# JavaScript/TypeScript linting (ESLint)
pnpm run lint

# TypeScript type checking
pnpm run types:check

# Code formatting (Prettier)
pnpm run format
``` -->

## Tech Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Backend    | Laravel 13         |
| Frontend   | Vue 3, Inertia.js, TypeScript        |
| Styling    | Tailwind CSS v4, Shadcn-Vue            |
| Build tool | Vite                                |
| Database   | MySQL (default)                    |
<!-- | Testing    | Pest                                | -->
