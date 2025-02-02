Cloud Book Writer Platform – Documentation
This document provides a comprehensive guide to the Cloud Book Writer Platform built using Laravel 11. It explains how the system is structured, how to set it up, the assumptions made during development, and how each feature meets the challenge requirements.

Table of Contents
Introduction & Context
Installation & Setup
Database Schema & Models
Key Features & Endpoints
Permissions & Role-based Access
Caching Strategy
Test Suite & Coverage
Assumptions & Ambiguities
Future Improvements
Conclusion
1. Introduction & Context
The Cloud Book Writer Platform is an application where users can:

Write and save books using an infinite nesting structure for sections and subsections.
Collaborate with other users, assigning them different roles (Author vs. Collaborator).
Store all data in the cloud, obviating the need for local files.
Challenge Requirements Recap
Unlimited Sections and Subsections
User Authentication
Permissions & Roles: Authors and Collaborators
Access Control: Only Authors create sections; both Author/Collaborator can edit.
Optional Bonus: Test cases, caching, PSR-12 compliance.
2. Installation & Setup
Clone the Repository

bash
Copy
Edit
git clone https://github.com/your-username/cloud-book-writer-laravel11.git
cd cloud-book-writer-laravel11
Install Dependencies

bash
Copy
Edit
composer install
Environment Configuration

Copy .env.example → .env.
Update DB_DATABASE, DB_USERNAME, DB_PASSWORD in the .env.
Generate App Key:
bash
Copy
Edit
php artisan key:generate
Migrate & Seed

bash
Copy
Edit
php artisan migrate --seed
Runs all migrations to build the database schema.
Seeds default roles (Author, Collaborator) if needed.
Start Development Server

bash
Copy
Edit
php artisan serve
The application is now accessible at http://127.0.0.1:8000.

Alternative: If you use Docker / Laravel Sail, follow the official Laravel Sail instructions.

3. Database Schema & Models
3.1 Database Schema
Tables:

users
Standard Laravel authentication (id, name, email, password).
roles
Contains roles like Author, Collaborator.
books
Each book has a user_id referencing the Author.
sections
Supports nested sections with parent_id referencing another row in sections.
book_id referencing the associated book.
book_user (pivot)
Tracks collaboration data: book_id, user_id, and role_id.
Diagram (example representation):

sql
Copy
Edit
+---------+         +------------+
|  users  |         |   roles    |
+----+----+         +-----+------+
     ^                    ^
     |                    |
     | (belongsTo)        | (belongsTo)
+----+------+---------+---+-------+----------+
|   book_user (pivot)    |
+-----------+------------+
| book_id   | user_id    | role_id
+-----------+------------+
       ^                  ^
       | (book)          | (user) 
       |                 |
+------+-----------------+-----------------+
|         books                           |
+------------+----------------------------+
| id         | user_id  (Author)         |
+------------+----------------------------+
             | 
             +---------+
                       | (hasMany)
                       v
                  +----+-----------------+
                  |      sections       |
                  +---------------------+
                  | book_id  | parent_id
                  | title    | content
                  +----------+----------
3.2 Models & Relationships
User: Has many books (as an Author). Belongs to many books (collaboration) via book_user.
Role: e.g., Author, Collaborator.
Book:
Belongs to a User (the author).
Has many Sections.
Belongs to many Users (collaborators).
Section:
Belongs to a Book.
Self-referencing parent_id for nested sections.
4. Key Features & Endpoints
All authenticated endpoints use auth:sanctum middleware.

4.1 Authentication Routes
Endpoint	Method	Description
/api/register	POST	Registers a new user (returns a Sanctum token).
/api/login	POST	Logs in user with email/password (returns Sanctum token).
/api/logout	POST	Revokes the current user’s token.
4.2 Books CRUD & Collaboration
Endpoint	Method	Description
/api/books	GET	Lists books the user owns or collaborates on.
/api/books	POST	Creates a new book. The authenticated user becomes the Author.
/api/books/{book}	GET	Shows details of a book (only if user is Author or Collaborator).
/api/books/{book}	PUT	Updates a book’s metadata (only if user is the Author).
/api/books/{book}	DELETE	Deletes a book (only if user is the Author).
/api/books/{book}/invite-collaborator	POST	Invites a user as a collaborator (only if user is the Author).
/api/books/{book}/remove-collaborator	POST	Removes a collaborator from a book (only if user is the Author).
4.3 Sections (Nested)
Endpoint	Method	Description
/api/books/{book}/sections	POST	Creates a section (or sub-section if parent_id is specified). Only the Author can do this.
/api/sections/{section}	GET	Shows details of a particular section if user is Author or Collaborator on that book.
/api/sections/{section}	PUT	Updates a section’s title/content if user is Author or Collaborator.
/api/sections/{section}	DELETE	Deletes a section if user is the Author.
5. Permissions & Role-based Access
We implemented Laravel Policies (BookPolicy and SectionPolicy) to handle logic:

BookPolicy

view: Author or collaborator.
create: Authenticated user can create their own book.
update, delete: Author only.
addCollaborator, removeCollaborator: Author only.
SectionPolicy

view: Author or collaborator.
create: Only Author can create sections for a book they own.
update: Author or collaborator.
delete: Author only.
Assumption: Collaborators Cannot Create Sections
The challenge explicitly says only the Author can create new sections. We strictly enforced that.

6. Caching Strategy
We applied simple caching for the GET /api/books index route as an example:

Cache Key: books_index_user_{userId}
Expiration: 60 seconds (you can tweak this in code).
Invalidation: Whenever a new book is created, updated, or deleted, we call Cache::forget("books_index_user_{$userId}").
Why Only the Index Endpoint?: This is the most frequently accessed route for listing a user’s books. Other endpoints can be cached similarly, but we started with the minimal approach.

7. Test Suite & Coverage
We provide Feature Tests for both BookController and SectionController. These tests cover:

CRUD flows for books and sections.
Authentication checks (401 if not logged in).
Authorization checks (403 if user lacks permissions).
Caching calls (verifying Cache::remember() and Cache::forget() usage in index()).
Example Tests
tests/Feature/BookControllerTest.php
test_index_returns_cached_list_of_books_for_the_authenticated_user()
test_store_creates_a_new_book_and_forgets_the_cache()
test_destroy_deletes_book_and_forgets_cache()
test_invite_collaborator_adds_user_to_collaborators()
tests/Feature/SectionControllerTest.php
test_store_creates_section_if_authorized()
test_store_returns_403_if_not_authorized()
test_update_edits_section_if_authorized(), etc.
Run tests via:

bash
Copy
Edit
php artisan test
or

bash
Copy
Edit
./vendor/bin/phpunit
8. Assumptions & Ambiguities
The challenge allows for some interpretation. Below are our explicit assumptions:

Collaborator Creation: Only the Author can create new sections. We do not allow collaborators to add new sections—strict compliance with the requirement.
Delete Permissions: We assumed only the Author can delete any section or book. The challenge was not 100% explicit, but we derived this from “Only author can create new sections…” and standard practice.
Role Storage: We store collaborator roles in a pivot table (book_user). The project uses a roles table with an id for “Author” and “Collaborator.”
Caching: We only cached the books index. The specification mentioned caching as a bonus, so we showcased a minimal approach. If real-time data changes were a requirement, we’d reduce the cache TTL or implement more sophisticated invalidation.
Infinite Nesting: Implemented using a simple adjacency list (parent_id in the sections table). Larger projects might require a Nested Set or another approach for performance.
User Interface: This solution focuses on API endpoints. No front-end UI is provided, consistent with the challenge’s statement about building a platform.
Error Handling: On unauthorized actions, we return 403 Forbidden. On not found, 404 Not Found. On validation fail, 422 Unprocessable Entity.
9. Future Improvements
Advanced Collaboration: Additional roles (e.g., co-author) with partial admin privileges.
Real-time Sync: Use Broadcasting for simultaneous editing.
Nested Set: For extremely deep or large section nesting, a more efficient data structure.
Front-end SPA: Possibly create a Vue or React front end with Laravel Sanctum for cookie-based auth.
Tag-based Caching: If the app grows, using a package like spatie/laravel-responsecache with advanced invalidation might be beneficial.
Localization: For multi-language support.
10. Conclusion
We have built a cloud-based book writing platform in Laravel 11 that:

Implements infinite nested sections,
Authenticates users via Laravel Sanctum,
Restricts section creation to Authors but allows both Authors and Collaborators to edit,
Invites and removes collaborators,
Caches the book index for faster reads,
Complies with PSR-12 style and includes Feature Tests for controllers,
Documents all assumptions so the approach is transparent.
This solution meets the challenge requirements, while also providing clarity on why certain decisions were made. If you have any questions or need further details, please feel free to reach out!

Thank You for reviewing this documentation.

Additional Resources
Laravel Official Docs
Laravel Sanctum
Spatie Laravel Response Cache (optional advanced caching)
End of Documentation
Note: You can include code examples, screenshots (like an ERD diagram), or sequence diagrams if you want to expand on any part of this structure.