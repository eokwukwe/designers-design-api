<?php

namespace App\Models;

use App\Models\Traits\Likeable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Cviebrock\EloquentTaggable\Taggable;

class Design extends Model
{
    use Taggable, Likeable;

    protected $fillable = [
        'user_id',
        'team_id',
        'image',
        'title',
        'description',
        'slug',
        'close_to_comment',
        'is_live',
        'upload_successful',
        'disk',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')
            ->orderBy('created_at', 'asc');
    }

    public function getImagesAttribute()
    {
        $thumbnail = $this->getImagePath('thumbnail');

        return [
            'original' => $this->getImagePath('original'),
            'large' => $this->getImagePath('large'),
            'thumbnail' => $this->getImagePath('thumbnail'),
        ];
    }

    /**
     * Replace orignial in filename for large and thumbnail
     *
     * @param string $size The subfolder of the image.
     * @return string
     */
    protected function getImagePath($size)
    {
        return Storage::disk($this->disk)->url(
            "uploads/designs/{$size}/" .
                preg_replace('/original/', $size, $this->image)
        );
    }
}
