<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookPolicy
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
    public function view(User $user, Book $book): Response
    {
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
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Book $book): Response
    {
        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'Only the author of this book can update it');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Book $book): Response
    {
        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'Only the author of this book can delete it');
    }

    /**
     * Determine whether the user can add a collaborator to the book.
     */
    public function addCollaborator(User $user, Book $book): Response
    {
        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'Only the author of this book can add collaborators');
    }

    /**
     * Determine whether the user can remove a collaborator to the book.
     */
    public function removeCollaborator(User $user, Book $book): Response
    {
        if ($book->user_id === $user->id) {
            return Response::allow();
        }

        return Response::denyWithStatus(403, 'Only the author of this book can remove collaborators');
    }
}
