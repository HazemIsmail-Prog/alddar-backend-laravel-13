<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\Comments\CommentCreated;
use App\Http\Requests\Comments\StoreCommentRequest;

class CommentController
{

    protected array $with = ['creator'];

    public function index(Request $request)
    {

        $validated = $request->validate([
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'integer'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $comments = Comment::query()
            ->where('commentable_type', $validated['commentable_type'])
            ->where('commentable_id', $validated['commentable_id'])
            ->with($this->with)
            ->paginate($request->integer('per_page', 15));

        return response()->json($comments);
    }

    public function store(StoreCommentRequest $request)
    {

        $validated = $request->validated();
        $path = null;

        DB::beginTransaction();
        try {

            if ($request->file('file')) {
                $file = $request->file('file');
                $dir = 'comments/'.now()->format('Y/m');
                $ext = strtolower((string) $file->getClientOriginalExtension());
                if ($ext === '' || ! preg_match('/^[a-z0-9]{1,12}$/', $ext)) {
                    $ext = 'webm';
                }
                $path = $file->storeAs($dir, 'voice-'.uniqid('', true).'.'.$ext, 'public');
                $validated['media_path'] = $path;
                $validated['media_disk'] = 'public';
            }

            $comment = Comment::create($validated);

            DB::commit();

            event(new CommentCreated($comment->load($this->with)));

            return response()->json($comment->load($this->with), 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
