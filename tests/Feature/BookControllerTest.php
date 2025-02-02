<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase; 

    public function test_index_returns_cached_list_of_books_for_the_authenticated_user()
    {
        $user = User::factory()->create();
        
        // Create 3 books belonging to this user
        Book::factory()->count(3)->create(['user_id' => $user->id]);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(Book::where('user_id', $user->id)->get());

        // Hit the index endpoint as the user
        $response = $this->actingAs($user)->getJson('/api/books');

        $response->assertStatus(200)
                 ->assertJsonCount(3);  // We expect exactly 3 books in JSON
    }

    public function test_store_creates_a_new_book_and_forgets_the_cache()
    {
        $user = User::factory()->create();

        // Fake the cache so we can verify "forget" is called
        Cache::spy();

        $payload = [
            'title'       => 'My New Book',
            'description' => 'A thrilling new read.',
        ];

        $response = $this->actingAs($user)
                         ->postJson('/api/books', $payload);

        $response->assertStatus(201);

        // Assert the book was created in DB
        $this->assertDatabaseHas('books', [
            'title'       => 'My New Book',
            'description' => 'A thrilling new read.',
            'user_id'     => $user->id,
        ]);

        // Assert Cache::forget("books_index_user_{$user->id}") was called
        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("books_index_user_{$user->id}");
    }

    public function test_show_returns_book_if_user_is_authorized()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        // The user who owns the book
        $response = $this->actingAs($user)
                         ->getJson("/api/books/{$book->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id'    => $book->id,
                     'title' => $book->title,
                 ]);
    }

    public function test_show_fails_if_user_is_not_authorized()
    {
        $author = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $author->id]);

        // Another user who doesn't own/collaborate on the book
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
                         ->getJson("/api/books/{$book->id}");

        $response->assertStatus(403);
    }

    public function test_update_modifies_book_and_forgets_cache()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        Cache::spy(); 

        $payload = [
            'title'       => 'Updated Title',
            'description' => 'Updated description.',
        ];

        $response = $this->actingAs($user)
                         ->putJson("/api/books/{$book->id}", $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'id'          => $book->id,
                     'title'       => 'Updated Title',
                     'description' => 'Updated description.',
                 ]);

        $this->assertDatabaseHas('books', [
            'id'          => $book->id,
            'title'       => 'Updated Title',
        ]);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("books_index_user_{$user->id}");
    }

    public function test_destroy_deletes_book_and_forgets_cache()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        Cache::spy();

        $response = $this->actingAs($user)
                         ->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        Cache::shouldHaveReceived('forget')
            ->once()
            ->with("books_index_user_{$user->id}");
    }

    public function test_invite_collaborator_adds_user_to_collaborators()
    {
        $author = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $author->id]);
        
        // Prepare a user to be invited
        $collaborator = User::factory()->create(['email' => 'collab@example.com']);
        
        $payload = ['email' => 'collab@example.com'];

        $response = $this->actingAs($author)
                         ->postJson("/api/books/{$book->id}/invite-collaborator", $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Collaborator invited successfully',
                 ]);

        // Ensure pivot record was created
        $this->assertDatabaseHas('book_users', [
            'book_id' => $book->id,
            'user_id' => $collaborator->id
        ]);
    }

    public function test_remove_collaborator_removes_user_from_collaborators()
    {
        $author = User::factory()->create();
        $collaborator = User::factory()->create(['email' => 'collab@example.com']);
        $book = Book::factory()->create(['user_id' => $author->id]);

        // Attach collaborator
        $role_id = Role::getCollaboratorRoleId();
        $book->collaborators()->attach($collaborator->id, ['role_id' => $role_id]);

        $payload = ['email' => 'collab@example.com'];

        $response = $this->actingAs($author)
                         ->postJson("/api/books/{$book->id}/remove-collaborator", $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Collaborator removed successfully',
                 ]);

        // Ensure pivot record was deleted
        $this->assertDatabaseMissing('book_users', [
            'book_id' => $book->id,
            'user_id' => $collaborator->id,
        ]);
    }
}
