<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use App\Models\Section;
use Illuminate\Auth\Access\Response;

class SectionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Section $section): Response
    {
        $book = $section->book;

        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        if ($book->collaborators()->where('user_id', $user->id)->exists()) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'You are not a collaborator of this book');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Book $book): Response
    {
        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'Only the author of this book can create sections');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Section $section): Response
    {
        $book = $section->book;

        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        if ($book->collaborators()->where('user_id', $user->id)->exists()) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'You are not a collaborator of this book');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Section $section): Response
    {
        $book = $section->book;

        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'You are not the owner of this book');
    }
}
