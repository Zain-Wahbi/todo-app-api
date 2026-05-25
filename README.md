# 📋 Todo App API

A RESTful API for task management built with Laravel 11 and Sanctum.

![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)
![Tests](https://img.shields.io/badge/Tests-31%20passed-28a745?style=flat)

---

## ✨ Features

- 🔐 Token-based authentication via Laravel Sanctum
- ✅ Full Task management (CRUD)
- 🗂️ Category management
- 📊 Dashboard statistics with caching
- 👑 Admin panel for user & task management
- 🔍 Filtering & search
- 🛡️ Rate limiting
- 📝 Logging
- 🧪 31 automated tests

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Authentication | Laravel Sanctum |
| Database | MySQL |
| Cache | File Cache |
| Testing | PHPUnit |

---

## 🚀 Installation

### 1. Clone the repository
```bash
git clone https://github.com/your-username/todo-app.git
cd todo-app
```

### 2. Install dependencies
```bash
composer install
```

### 3. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure database
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_app
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run migrations & seeders
```bash
php artisan migrate:fresh --seed
```

### 6. Start the server
```bash
php artisan serve
```

---

## 👤 Default Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password123 |
| User | john@example.com | password123 |
| User (inactive) | jane@example.com | password123 |

---

## 📡 API Endpoints

### Base URL  
http://localhost:8000/api/v1

### 🔐 Auth
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/auth/register` | No | Register new user |
| POST | `/auth/login` | No | Login |
| POST | `/auth/logout` | Yes | Logout |
| GET | `/auth/me` | Yes | Get current user |

### ✅ Tasks
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/tasks` | Yes | Get all tasks |
| POST | `/tasks` | Yes | Create task |
| GET | `/tasks/{id}` | Yes | Get task |
| PUT | `/tasks/{id}` | Yes | Update task |
| DELETE | `/tasks/{id}` | Yes | Delete task |
| PATCH | `/tasks/{id}/complete` | Yes | Mark as complete |

### 🔍 Task Filters
GET /api/v1/tasks?status=pending
GET /api/v1/tasks?priority=high
GET /api/v1/tasks?category_id=1
GET /api/v1/tasks?search=meeting
GET /api/v1/tasks?overdue=true

### 🗂️ Categories
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/categories` | Yes | Get all categories |
| POST | `/categories` | Yes | Create category |
| GET | `/categories/{id}` | Yes | Get category |
| PUT | `/categories/{id}` | Yes | Update category |
| DELETE | `/categories/{id}` | Yes | Delete category |

### 📊 Dashboard
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/dashboard` | Yes | Get statistics |

### 👑 Admin
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/admin/dashboard` | Admin | Admin statistics |
| GET | `/admin/users` | Admin | All users |
| GET | `/admin/users/{id}` | Admin | User details |
| PATCH | `/admin/users/{id}/toggle-active` | Admin | Toggle user status |
| PATCH | `/admin/users/{id}/change-role` | Admin | Change user role |
| DELETE | `/admin/users/{id}` | Admin | Delete user |
| GET | `/admin/tasks` | Admin | All tasks |
| DELETE | `/admin/tasks/{id}` | Admin | Delete any task |

---

## 📊 API Response Format

### Success
```json
{
    "success": true,
    "message": "Task created successfully",
    "data": {
        "task": {}
    }
}
```

### Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "title": ["Title is required"]
    }
}
```

---

## 🧪 Running Tests

```bash
php artisan test
```

Expected output:
Tests: 31 passed (73 assertions)

---

## 📁 Project Structure

- **app/**
  - **Enums/**
    - TaskPriority.php
    - TaskStatus.php
    - UserRole.php
  - **Http/**
    - Controllers/Api/V1/
      - Admin/
        - AdminDashboardController.php
        - AdminTaskController.php
        - AdminUserController.php
      - AuthController.php
      - CategoryController.php
      - DashboardController.php
      - TaskController.php
    - Middleware/
      - AdminMiddleware.php
    - Requests/
      - Auth/
      - Category/
      - Task/
    - Resources/
      - CategoryResource.php
      - TaskResource.php
      - UserResource.php
  - **Models/**
    - Category.php
    - Task.php
    - User.php
  - **Traits/**
    - ApiResponseTrait.php
---

## 🔒 Security Features

- Token-based auth via Sanctum
- Rate limiting (5 req/min for auth, 60 req/min for API)
- Admin middleware protection
- Soft deletes for data safety
- Request validation on all endpoints

---

## 📄 License

MIT License

## 🌐 Live Demo

Base URL:
https://todo-app-api-production-6c73.up.railway.app/api/v1