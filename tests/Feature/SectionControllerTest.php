<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_section_if_authorized()
    {
        // Create a user, book, and section
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        // Example request data
        $payload = [
            'title'      => 'My New Section',
            'content'    => 'Some content here.',
        ];

        // Act as the user who is the book’s author
        $response = $this->actingAs($user)
                         ->postJson("/api/books/{$book->id}/sections", $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'title'   => 'My New Section',
                     'content' => 'Some content here.',
                     'book_id' => $book->id,
                 ]);

        // Check DB for the new section
        $this->assertDatabaseHas('sections', [
            'title'   => 'My New Section',
            'content' => 'Some content here.',
            'book_id' => $book->id,
        ]);
    }

    public function test_store_returns_403_if_not_authorized()
    {
        // Create a user, book, and section
        $author = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $author->id]);

        // Another user who is not a collaborator or author
        $otherUser = User::factory()->create();

        $payload = [
            'title'      => 'Unauthorized Section',
            'content'    => 'Should not be created.',
        ];

        // Attempt to store as the unauthorized user
        $response = $this->actingAs($otherUser)
                         ->postJson("/api/books/{$book->id}/sections", $payload);

        $response->assertStatus(403); // Because Gate::authorize('create',[Section::class, $book]) should fail
        $this->assertDatabaseMissing('sections', [
            'title'   => 'Unauthorized Section',
        ]);
    }

    public function test_show_returns_section_if_authorized()
    {   
        // Create a user, book, and section
        $user = User::factory()->create();
        $section = Section::factory()->create();

        $section->book->update(['user_id' => $user->id]);

        // Act as the user who is the book’s author
        $response = $this->actingAs($user)
                         ->getJson("/api/books/$section->book_id/sections/{$section->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'id'      => $section->id,
                     'title'   => $section->title,
                     'content' => $section->content,
                 ]);
    }

    public function test_show_returns_403_if_unauthorized()
    {
        // Create a user, book, and section
        $author = User::factory()->create();
        $section = Section::factory()->create();
        // Assign the section’s parent book to the author
        $section->book->update(['user_id' => $author->id]);

        // Another user who doesn’t have permission
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->getJson("/api/books/$section->book_id/sections/{$section->id}");

        $response->assertStatus(403);
    }

    public function test_update_edits_section_if_authorized()
    {
        // Create a user, book, and section
        $user = User::factory()->create();
        $section = Section::factory()->create();

        // The user is the book author
        $section->book->update(['user_id' => $user->id]);

        $payload = [
            'title'   => 'Updated Title',
            'content' => 'Updated content text.',
        ];

        $response = $this->actingAs($user)
                         ->putJson("/api/books/$section->book_id/sections/{$section->id}", $payload);

        $response->assertStatus(200)
                 ->assertJson([
                     'id'      => $section->id,
                     'title'   => 'Updated Title',
                     'content' => 'Updated content text.',
                 ]);

        // Check in DB
        $this->assertDatabaseHas('sections', [
            'id'      => $section->id,
            'title'   => 'Updated Title',
            'content' => 'Updated content text.',
        ]);
    }

    public function test_update_returns_403_if_not_authorized()
    {
        // Create a user, book, and section
        $author = User::factory()->create();
        $section = Section::factory()->create();
        $section->book->update(['user_id' => $author->id]);

        // Another user
        $otherUser = User::factory()->create();

        $payload = [
            'title'   => 'Should Not Update',
            'content' => 'No permission to update this.',
        ];

        $response = $this->actingAs($otherUser)
                         ->putJson("/api/books/$section->book_id/sections/{$section->id}", $payload);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('sections', [
            'title' => 'Should Not Update',
        ]);
    }

    public function test_destroy_deletes_section_if_authorized()
    {
        // Create a user, book, and section
        $user = User::factory()->create();
        $section = Section::factory()->create();

        // The user is the book author
        $section->book->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)
                         ->deleteJson("/api/books/$section->book_id/sections/{$section->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Section deleted successfully',
                 ]);

        $this->assertDatabaseMissing('sections', [
            'id' => $section->id,
        ]);
    }

    public function test_destroy_returns_403_if_unauthorized()
    {
        $author = User::factory()->create();
        $section = Section::factory()->create();
        $section->book->update(['user_id' => $author->id]);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
                         ->deleteJson("/api/books/$section->book_id/sections/{$section->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('sections', [
            'id' => $section->id,
        ]);
    }
}
