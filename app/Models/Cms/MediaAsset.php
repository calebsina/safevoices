<?php

namespace App\Models\Cms;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Public CMS media - lives on the PUBLIC disk, completely separate from
 * the encrypted evidence vault. Alt text / captions are translatable
 * for accessibility and SEO.
 */
class MediaAsset extends Model
{
    use Translatable;

    protected $fillable = ['disk', 'path', 'filename', 'mime_type', 'size', 'created_by'];

    protected $appends = ['url'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
