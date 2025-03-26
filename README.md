# Laravel Client Portal

A modern, secure client portal built with Laravel 12, Vue.js, Inertia.js, and TailwindCSS with shadcn/vue components.

## Features

- **User Authentication**
    - Registration with email verification
    - Secure login
    - Password reset
    - Remember me functionality

- **Profile Management**
    - Personal information updates
    - Avatar upload and management
    - Contact details management
    - Address information

- **Role-Based Access Control**
    - Pre-defined roles (Super Admin, Admin, Volunteer)
    - Custom permission assignments
    - Role management interface
    - User permission management

- **Admin Dashboard**
    - User management with CRUD operations
    - Role management interface
    - System statistics and metrics
    - User approval workflow

- **Responsive Design**
    - Mobile-friendly interface
    - Adaptive layouts for all screen sizes
    - Dark mode support

## Tech Stack

- **Backend:**
    - Laravel 12.x
    - PHP 8.2+
    - MySQL/PostgreSQL
    - Spatie Laravel-Permission

- **Frontend:**
    - Vue.js 3 (Composition API)
    - Inertia.js
    - TailwindCSS
    - shadcn/vue components

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL or PostgreSQL database

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/laravel-client-portal.git
   cd laravel-client-portal
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install NPM dependencies:
   ```bash
   npm install
   ```

4. Create a copy of the .env file:
   ```bash
   cp .env.example .env
   ```

5. Generate an application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your database in the .env file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=laravel_client_portal
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Run database migrations and seed initial data:
   ```bash
   php artisan migrate --seed
   ```

8. Create a symbolic link for storage:
   ```bash
   php artisan storage:link
   ```

9. Start the development server:
   ```bash
   php artisan serve
   ```

10. In a separate terminal, compile assets:
    ```bash
    npm run dev
    ```

11. Access the application at `http://localhost:8000`

## Default Credentials

After seeding the database, you can log in with the following default credentials:

- **Super Admin:**
    - Email: superadmin@example.com
    - Password: password

- **Admin:**
    - Email: admin@example.com
    - Password: password

- **Volunteer:**
    - Email: volunteer@example.com
    - Password: password

## Development

### Running the Development Server

```bash
# Start Laravel server
php artisan serve

# Compile assets with hot reload
npm run dev
```

### Building for Production

```bash
npm run build
```

## Commands
### Assign a role to a user
```bash
php artisan user:assign-role john@example.com admin
```

### Remove a role from a user
```bash
php artisan user:assign-role john@example.com admin --remove
```

### List all roles
```bash
php artisan roles:list
```

### List roles with their permissions
```bash
php artisan roles:list --with-permissions
```

### View a specific user's roles
```bash
php artisan user:roles john@example.com
```

### List all users with their roles
```bash
php artisan user:roles --all
```

## Security

- All forms use CSRF protection
- Authentication with secure password hashing
- Email verification required
- Role-based access control for all routes
- Input validation and sanitization
- Protection against XSS attacks
- Proper error handling and logging

## License

The Laravel Client Portal is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
