<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DesignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'is_live' => $this->is_live,
            'likes_count' => $this->likes()->count(),
            'team' => $this->team ? [
                'name' => $this->team->name,
                'slug' => $this->team->slug,
            ] : null,
            'images' => $this->images,
            'tag_list' => [
                'tags' => $this->tagArray,
                'normalized' => $this->tagArrayNormalized,
            ],
            'created_dates' => [
                'created_at_human' => $this->created_at->diffForHumans(),
                'created_at' => $this->created_at
            ],
            'updated_dates' => [
                'updated_at_human' => $this->updated_at->diffForHumans(),
                'updated_at' => $this->updated_at
            ],
            'user' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection(
                $this->whenLoaded('comments')
            ),
        ];
    }
}
