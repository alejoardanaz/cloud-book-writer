# Cloud Book Writer Platform

A next-generation **cloud-based** platform built with **Laravel 11** where users can write, save, and collaborate on books. It supports **unlimited nesting** of sections, user roles (**Author** / **Collaborator**), and **basic caching** on listings for improved performance.

---

## Features

1. **User Authentication**  
   - Registers and logs in users via Laravel Sanctum tokens.

2. **Books & Sections**  
   - Unlimited **nested sections** using a `parent_id` column for recursive structure.
   - **Author** can create/edit/delete books and sections.
   - **Collaborator** can edit existing sections but cannot create or delete them.

3. **Role-Based Permissions**  
   - **Author**: Full rights over a book (create, edit, delete, manage collaborators).  
   - **Collaborator**: Edit rights only; cannot create sections.

4. **Collaboration**  
   - Authors can **invite** or **remove** collaborators on each book.

5. **Caching**  
   - `GET /api/books` is cached for 60 seconds per user ID.
   - Cache **invalidates** when data changes (store/update/destroy).

6. **Tests**  
   - PSR-12 coding style enforced.
   - Feature tests for Books and Sections with authorization checks.

---

## Requirements

- **PHP 8.1+**  
- **Composer**  
- **Database** (MySQL)  
- **Laravel 11**

---

## Installation & Setup

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/cloud-book-writer-laravel11.git
cd cloud-book-writer-laravel11
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
- Copy `.env.example` to `.env`.
- Update `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- Generate an app key:
```bash
php artisan key:generate
```

### 4. Migrate & Seed
```bash
php artisan migrate --seed
```
- Runs all migrations to build the DB schema.
- Optionally seeds default roles (Author, Collaborator).

### 5. Serve the Application
```bash
php artisan serve
```
- Application runs at `http://127.0.0.1:8000`.

---

### Database Schema

1. users Table
- id
- name
- email
- password
- remember_token
- timestamps

2. roles Table
- id
- name (e.g., Author, Collaborator)
- timestamps

3. books Table
- id
- title
- description (nullable)
- user_id (the author of the book)
- timestamps

4. sections Table
- id
- book_id
- parent_id (nullable, self-referencing to allow for infinite nesting)
- title
- content (long text field for the actual section text)
- timestamps

5. book_user (Pivot Table for Collaboration)
- id
- book_id
- user_id
- role_id (indicates if the user is an Author or a Collaborator for this specific book)
- timestamps

## Usage (API Endpoints)

All routes typically use Sanctum authentication (`auth:sanctum`).

### **Authentication**
- `POST /api/register`
- `POST /api/login`

### **Books**
- `GET /api/books` – List user’s books (cached per user).
- `POST /api/books` – Create a new book.
- `GET /api/books/{book}` – Show details of a specific book.
- `PUT /api/books/{book}` – Update book details (**Author only**).
- `DELETE /api/books/{book}` – Delete a book (**Author only**).
- `POST /api/books/{book}/invite-collaborator` – Invite collaborator (**Author only**).
- `POST /api/books/{book}/remove-collaborator` – Remove collaborator (**Author only**).

### **Sections**
- `POST /api/books/{book}/sections` – Create a section (**Author only**).
- `GET /api/sections/{section}` – Show a single section if authorized.
- `PUT /api/sections/{section}` – Update a section (**Author or Collaborator**).
- `DELETE /api/sections/{section}` – Delete a section (**Author only**).

---

## Caching Details

- **Endpoint Cached**: `GET /api/books`
- **Cache Key**: `books_index_user_{userId}`
- **Expiration**: 60 seconds (configurable in code).
- **Invalidation**: `Cache::forget` is called on store, update, destroy for books.

---

## Testing

The project include Feature Tests for both `BookController` and `SectionController`:

Run tests:
```bash
php artisan test
```
Or
```bash
./vendor/bin/phpunit
```

### **Coverage includes:**
- CRUD operations
- Permissions (**403 if unauthorized**)
- Validation rules (**422 on invalid data**)
- Cache checks (using `Cache::spy()` or `Cache::shouldReceive()` in index tests)
---

## Assumptions & Clarifications

- **Collaborators do not create sections. Only the Author can.**
- **Deleting books or sections is Author-only.**
- **Pivot Table (`book_user`) stores user roles for each book.**
- **Nested Sections**: Implemented via `parent_id`; if very large, consider a more advanced data structure (e.g., Nested Sets).
- **API-Only**: No front-end UI is provided; use tools like Postman or a custom SPA to interact.