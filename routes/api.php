<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\SectionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])
    ->group(function () {
    // Books
    Route::get('/books', [BookController::class, 'index']);
    Route::post('/books', [BookController::class, 'store']);
    Route::get('/books/{id}', [BookController::class, 'show']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);

    // Sections
    Route::post('/books/{book}/sections', [SectionController::class, 'store']);
    Route::get('/books/{book}/sections/{section}', [SectionController::class, 'show']);
    Route::put('/books/{book}/sections/{section}', [SectionController::class, 'update']);
    Route::delete('/books/{book}/sections/{section}', [SectionController::class, 'destroy']);

    // Collaborators
    Route::post('/books/{id}/invite-collaborator', [BookController::class, 'inviteCollaborator']);
    Route::post('/books/{id}/remove-collaborator', [BookController::class, 'removeCollaborator']);
});