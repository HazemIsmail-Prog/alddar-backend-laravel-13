<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Attachments\StoreAttachmentRequest;

class AttachmentController
{

    protected array $with = ['creator:id,name_ar,name_en'];

    public function index(Request $request)
    {

        $validated = $request->validate([
            'attachable_type' => ['required', 'string'],
            'attachable_id' => ['required', 'integer'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $attachments = Attachment::query()
            ->where('attachable_type', $validated['attachable_type'])
            ->where('attachable_id', $validated['attachable_id'])
            ->with($this->with)
            ->paginate($request->integer('per_page', 15));

        return response()->json($attachments);
    }

    public function store(StoreAttachmentRequest $request)
    {

        $validated = $request->validated();
        $path = null;

        DB::beginTransaction();
        try {

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $dir = 'attachments/';
            $prefix = $validated['attachable_type'].'-'.$validated['attachable_id'].'-';
            $name = $prefix.now()->format('Ymd-His').'-'.uniqid('', true);
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $path = $file->storeAs($dir, $name.'.'.$ext, 's3');
            $validated['file_disk'] = 's3';
            $validated['file_path'] = $path;
            $validated['original_name'] = $originalName;
            $validated['mime_type'] = $file->getMimeType();

            $attachment = Attachment::create($validated);
            // broadcast(new AttachmentCreated($attachment->load($this->with)));

            DB::commit();

            return response()->json($attachment->load($this->with), 201);

        } catch (\Throwable $e) {

            DB::rollBack();

            if ($path && Storage::disk('s3')->exists($path)) {
                Storage::disk('s3')->delete($path);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Attachment $attachment)
    {
        // wanna return the file it self not the url
        $file = Storage::disk('s3')->get($attachment->file_path);
        if (!$file) {
            return response()->json(['message' => 'File not found.'], 404);
        }
        return response($file, 200, [
            'Content-Type' => $attachment->mime_type, 
            'Content-Disposition' => 'inline; filename="' . basename($attachment->file_path) . '"',
        ]);
    }

    public function destroy(Request $request, Attachment $attachment)
    {
        if ($attachment->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($attachment->file_disk && $attachment->file_path) {
            Storage::disk($attachment->file_disk)->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->noContent();
    }
}
