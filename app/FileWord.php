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
    const COLLECTION_HTML_FILE = "html_files";
    protected $guarded = [];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION_WORD_FILE)
            ->singleFile();

        $this->addMediaCollection(self::COLLECTION_HTML_FILE)
            ->singleFile();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loadHTML(): ?string
    {
        $mediaPath = $this->getFirstMediaPath(self::COLLECTION_HTML_FILE);

        if ($mediaPath) {
            return file_get_contents($mediaPath);
        }

        return null;
    }

    public function saveHtml(string $htmlString): void
    {
        $this
            ->addMediaFromString($htmlString)
            ->usingFileName($this->nama . ".html")
            ->toMediaCollection(self::COLLECTION_HTML_FILE);
    }
}
