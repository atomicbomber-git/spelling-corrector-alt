<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FileWord extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = "file_word";
    const COLLECTION_WORD_FILE = "word_files";
    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION_WORD_FILE)
            ->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
