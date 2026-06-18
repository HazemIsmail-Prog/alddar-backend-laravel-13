<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Party;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AttachmentController
{
    /**
     * @return array<string, class-string<Model>>
     */
    protected function attachableTypeMap(): array
    {
        return [
            'order' => Order::class,
            'invoice' => Invoice::class,
            'party' => Party::class,
        ];
    }

    protected function resolveAttachableClass(string $alias): string
    {
        $map = $this->attachableTypeMap();

        abort_unless(isset($map[$alias]), 422, 'Invalid attachable_type.');

        return $map[$alias];
    }

    protected function attachmentPayload(Attachment $attachment): array
    {
        $data = $attachment->load(['creator:id,name_en,name_ar'])->toArray();

        if ($attachment->file_disk === 'public' && $attachment->file_path) {
            $data['url'] = Storage::disk('public')->url($attachment->file_path);
        } else {
            $data['url'] = null;
        }

        return $data;
    }

    public function index(Request $request)
    {
        $aliasKeys = array_keys($this->attachableTypeMap());

        $validated = $request->validate([
            'attachable_type' => ['required', 'string', Rule::in($aliasKeys)],
            'attachable_id' => ['required', 'integer'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $class = $this->resolveAttachableClass($validated['attachable_type']);
        $parent = $class::query()->findOrFail($validated['attachable_id']);

        $attachments = Attachment::query()
            ->whereMorphedTo('attachable', $parent)
            ->with(['creator:id,name_en,name_ar'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 50));

        $attachments->getCollection()->transform(function (Attachment $attachment) {
            return $this->attachmentPayload($attachment);
        });

        return response()->json($attachments);
    }

    public function store(Request $request)
    {
        $aliasKeys = array_keys($this->attachableTypeMap());

        $validated = $request->validate([
            'attachable_type' => ['required', 'string', Rule::in($aliasKeys)],
            'attachable_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:5000'],
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $class = $this->resolveAttachableClass($validated['attachable_type']);
        $parent = $class::query()->findOrFail($validated['attachable_id']);

        $file = $request->file('file');
        $dir = 'attachments/'.now()->format('Y/m');
        $path = $file->store($dir, 'public');

        DB::beginTransaction();
        try {
            $attachment = new Attachment([
                'attachable_type' => $parent->getMorphClass(),
                'attachable_id' => $parent->getKey(),
                'created_by' => $request->user()->id,
                'description' => $validated['description'] ?? null,
                'file_disk' => 'public',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
            ]);
            $attachment->save();

            DB::commit();

            return response()->json($this->attachmentPayload($attachment), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, Attachment $attachment)
    {
        abort_unless($attachment->created_by === $request->user()->id, 403);

        if ($attachment->file_disk && $attachment->file_path) {
            Storage::disk($attachment->file_disk)->delete($attachment->file_path);
        }

        $attachment->delete();

        return response()->noContent();
    }
}
