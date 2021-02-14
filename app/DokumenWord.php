<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DokumenWord extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $table = "dokumen_word";

    const COLLECTION_WORD_FILE = "word_files";
    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION_WORD_FILE)
            ->singleFile();
    }
}
