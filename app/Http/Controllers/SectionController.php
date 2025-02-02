<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SectionController extends Controller
{
    /**
     * Store a newly created section in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $bookId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $bookId)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'parent_id' => 'integer|exists:sections,id',
        ]);

        $book = Book::findOrFail($bookId);

        Gate::authorize('create', [Section::class, $book]);

        return Section::create([
            'title' => $request->title,
            'content' => $request->content,
            'book_id' => $bookId,
            'parent_id' => $request->parent_id,
        ]);
    }

    /**
     * Display the specified section.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $section = Section::findOrFail($id);

        Gate::authorize('view', $section);

        return $section;
    }

    /**
     * Update the specified section.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        Gate::authorize('update', $section);

        $request->validate([
            'title' => 'required|string',
            'content' => 'string',
            'parent_id' => 'integer|exists:sections,id',
        ]);

        $section->update([
            'title' => $request->title,
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        return $section;
    }

    /**
     * Remove the specified section from storage.
     *
     * @param  int  $id  The ID of the section to be removed.
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $section = Section::findOrFail($id);

        Gate::authorize('delete', $section);

        $section->delete();

        return response()->json([
            'message' => 'Section deleted successfully',
        ]);
    }
}
