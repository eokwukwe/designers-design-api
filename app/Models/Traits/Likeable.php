<?php

namespace App\Models\Traits;

use App\Models\Like;

trait Likeable
{
    /**
     * Delete relate likes when a model is delete
     */
    public static function bootLikeable()
    {
        static::deleting(function ($model) {
            $model->removeLikes();
        });
    }

    public function removeLikes()
    {
        if ($this->likes()->count()) {
            $this->likes()->delete();
        }
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function like()
    {
        if (!auth()->check()) return;

        // Check if the user has already liked the model
        if ($this->isLikedByUser(auth()->id())) {
            return;
        }

        return $this->likes()->create(['user_id' => auth()->id()]);
    }

    public function unlike()
    {
        if (!auth()->check()) return;

        // Check if the user has already liked the model
        if (!$this->isLikedByUser(auth()->id())) {
            return;
        }

        return $this->likes()
            ->where('user_id', auth()->id())
            ->delete();
    }

    public function isLikedByUser($user_id)
    {
        return (bool) $this->likes()
            ->where('user_id', $user_id)
            ->count();
    }
}
