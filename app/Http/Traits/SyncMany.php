<?php

namespace App\Http\Traits;

trait SyncMany
{
    public function syncMany(string $relation, array $newValues): void
    {
        $idsToKeep = collect($newValues)->pluck('id')->filter()->all();
        $this->{$relation}()->whereNotIn('id', $idsToKeep)->delete();
        foreach ($newValues as $row) {
            $this->{$relation}()->updateOrCreate(['id' => $row['id'] ?? null], $row);
        }
    }
}
