<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $cacheKey = "books_index_user_{$userId}";

        return Cache::remember($cacheKey, 60, function () use ($userId) {
            return Book::where('user_id', $userId)->get();
        });
    }

    /**
     * Store a newly created book in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'required|string',
        ]);

        $book = Book::create([
            'title'       => $request->title,
            'description' => $request->description,
            'user_id'     => $request->user()->id,
        ]);

        Cache::forget("books_index_user_{$request->user()->id}");

        return $book;
    }

    /**
     * Display the specified book.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::findOrFail($id);

        Gate::authorize('view', $book);

        return $book;
    }

    /**
     * Update the specified book in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'string',
        ]);

        $book = Book::findOrFail($id);

        Gate::authorize('update', $book);

        $book->update([
            'title'       => $request->title,
            'description' => $request->description,
        ]);

        Cache::forget("books_index_user_{$request->user()->id}");

        return $book;
    }

    /**
     * Remove the specified book from storage.
     *
     * @param  int  $id  The ID of the book to be deleted.
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        Gate::authorize('delete', $book);

        $book->delete();

        Cache::forget("books_index_user_{$book->user_id}");

        return response()->json([
            'message' => 'Book deleted successfully',
        ]);
    }

    /**
     * Invite a collaborator to a book.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance.
     * @param int $id The ID of the book to which the collaborator is being invited.
     * @return \Illuminate\Http\Response
     */
    public function inviteCollaborator(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $book = Book::findOrFail($id);

        Gate::authorize('addCollaborator', $book);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $role_id = Role::getCollaboratorRoleId();

        $book->collaborators()->syncWithoutDetaching([
            $user->id => ['role_id' => $role_id]
        ]);

        return response()->json([
            'message' => 'Collaborator invited successfully',
        ]);
    }

    /**
     * Remove a collaborator from the book.
     *
     * @param \Illuminate\Http\Request $request The request instance.
     * @param int $id The ID of the collaborator to be removed.
     * @return \Illuminate\Http\Response
     */
    public function removeCollaborator(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $book = Book::findOrFail($id);

        Gate::authorize('removeCollaborator', $book);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        $book->collaborators()->detach($user->id);

        return response()->json([
            'message' => 'Collaborator removed successfully',
        ]);
    }
}
