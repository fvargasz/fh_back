# Laravel Project Setup Guide

## Prerequisites

Before running this Laravel project, ensure you have the following software installed on your system:

- **PHP** (version 8.2 recommended, minimum 8.1)
- **Composer** (version 2.8 or higher recommended)
- **PostgreSQL** (version 17 recommended)
- **pgAdmin** (recommended for database management)
- **Node.js & npm** (for frontend asset compilation)
- **Git** (for cloning the repository)

### Verify Installation

Check if the required tools are properly installed by running:

```bash
php --version
composer --version
psql --version
node --version
npm --version
git --version
```

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd <project-directory>
```

### 2. Install PHP Dependencies

Laravel uses Composer to manage PHP dependencies. Install them by running:

```bash
composer install
```

### 3. Environment Configuration

#### Copy Environment File

Laravel uses an `.env` file for environment-specific configurations. Copy the example file:

```bash
cp .env.example .env
```

#### Configure Required Environment Variables

Open the newly created `.env` file and update the following essential variables:

**Database Configuration:**
```bash
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=postgres
DB_PASSWORD=admin
```

**Sanctum Configuration (for API authentication):**
```bash
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1
```

> **Important:** The `SANCTUM_STATEFUL_DOMAINS` variable defines which domains are allowed to make authenticated requests to your Laravel API. This is a critical security feature that prevents unauthorized cross-origin requests. 
> 
> **Customize these domains based on your setup:**
> - `localhost:3000` - if your frontend runs on port 3000
> - `127.0.0.1:8000` - matches your Laravel backend URL
> - Add your production domain when deploying (e.g., `yourdomain.com`)
> - Include any other domains where your frontend application will be hosted
>
> **Example for different setups:**
> ```bash
> # For frontend on port 5173 (Vite default)
> SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:8000,::1
> 
> # For production deployment
> SANCTUM_STATEFUL_DOMAINS=localhost,yourdomain.com,api.yourdomain.com
> ```

**Application Configuration:**
```bash
APP_NAME="Your App Name"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
```

> **Important:** Replace `your_database_name` with your actual database name. Ensure PostgreSQL is running and the database exists before proceeding.

### 4. Generate Application Key

This key is crucial for security and encryption within your Laravel application:

```bash
php artisan key:generate
```

### 5. Database Setup

#### Create Database (if not exists)

Using pgAdmin or PostgreSQL command line, create your database:

```sql
CREATE DATABASE your_database_name;
```

#### Run Database Migrations

If the project has database migrations, run them to create the necessary tables:

```bash
php artisan migrate
```

If you encounter permission issues, you may need to run:

```bash
php artisan migrate:fresh
```

### 6. Start the Development Server

Launch the Laravel development server:

```bash
php artisan serve
```

Your application will be accessible at [http://localhost:8000](http://localhost:8000)

## Additional Configuration

### Storage Permissions (Linux/Mac)

If you encounter storage permission issues:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Queue Workers (if applicable)

If the project uses queued jobs:

```bash
php artisan queue:work
```

### Cache Configuration

Clear and rebuild cache for optimal performance:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Common Issues

**Database Connection Issues:**
- Verify PostgreSQL is running
- Check database credentials in `.env` file
- Ensure the database exists

**Permission Errors:**
- Check storage and bootstrap/cache folder permissions
- Verify web server user has write access

**Asset Compilation Errors:**
- Clear npm cache: `npm cache clean --force`
- Delete `node_modules` and reinstall: `rm -rf node_modules && npm install`

**Laravel-Specific Errors:**
- Clear Laravel caches: `php artisan cache:clear`
- Clear configuration cache: `php artisan config:clear`
- Check Laravel logs in `storage/logs/laravel.log`

### Getting Help

- **Laravel Documentation:** [https://laravel.com/docs](https://laravel.com/docs)
- **Laravel Sanctum Documentation:** [https://laravel.com/docs/sanctum](https://laravel.com/docs/sanctum)
- **PostgreSQL Documentation:** [https://www.postgresql.org/docs/](https://www.postgresql.org/docs/)

## Development Workflow

Once setup is complete, your typical development workflow will be:

1. Start PostgreSQL service
2. Run `php artisan serve` (backend server)
3. Run `npm run watch` (if using frontend assets)
4. Access application at `http://localhost:8000`
