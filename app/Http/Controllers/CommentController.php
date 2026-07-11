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

    protected array $with = ['creator:id,name_ar,name_en'];

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
                $dir = 'voices/';
                $ext = strtolower((string) $file->getClientOriginalExtension());
                if ($ext === '' || ! preg_match('/^[a-z0-9]{1,12}$/', $ext)) {
                    $ext = 'webm';
                }
                $path = $file->storeAs($dir, 'voice-'.now()->format('Ymd-His').'-'.uniqid('', true).'.'.$ext, 'public');
                $validated['media_path'] = $path;
                $validated['media_disk'] = 'public';
            }

            $comment = Comment::create($validated);
            broadcast(new CommentCreated($comment->load($this->with)));

            DB::commit();

            return response()->json($comment->load($this->with), 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Comment $comment)
    {

        // Return the webm file if present, otherwise 404
        if ($comment->media_disk === 'public' && $comment->media_path && Storage::disk('public')->exists($comment->media_path)) {
            return response()->file(
                Storage::disk('public')->path($comment->media_path),
                [
                    'Content-Type' => 'audio/webm',
                    'Content-Disposition' => 'inline; filename="' . basename($comment->media_path) . '.webm"',
                ]
            );
        }
        abort(404, 'Media file not found.');

    }

    public function markAsRead(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'integer'],
        ]);

        // Resolve FQCN from morph map or fallback to type directly
        $commentableType = $validated['commentable_type'];
        $commentableId = $validated['commentable_id'];

        // Handle Laravel morph map (if any)
        $morphMap = \Illuminate\Database\Eloquent\Relations\Relation::morphMap();
        $morphClass = $morphMap[$commentableType] ?? $commentableType;

        if (!class_exists($morphClass)) {
            return response()->json(['message' => "Class $morphClass not found."], 400);
        }

        // Fetch commentable model
        $model = $morphClass::find($commentableId);
        if (!$model) {
            return response()->json(['message' => "Resource not found."], 404);
        }

        // Get unread comments for this user on the given resource
        $readCommentIds = request()->user()->commentReaders()->pluck('comments.id');
        $comments = $model->comments()->whereNotIn('id', $readCommentIds)->pluck('id');
        request()->user()->commentReaders()->syncWithoutDetaching($comments);

        return response()->json(['message' => 'Comments marked as read'], 200);
    }
}
