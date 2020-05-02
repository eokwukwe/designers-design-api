<?php

namespace App\Repositories\Eloquent;

use App\Models\Design;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\BaseRepository;

class DesignRepository extends BaseRepository implements IDesign
{
    public function model()
    {
        return Design::class;  // === return 'App\Models\Design'
    }

    public function applyTags($id, array $data)
    {
        $design = $this->find($id);
        return $design->retag($data);
    }

    public function addComment($designId, array $data)
    {
        // get the design
        $design = $this->find($designId);

        // create the comment
        return $design->comments()->create($data);
    }

    public function like($id)
    {
        $design =  $this->model->findOrFail($id);

      return $design->isLikedByUser(auth()->id())
            ? $design->unlike()
            : $design->like();
    }

    public function designLikedByUser($designId)
    {
        $design =  $this->model->findOrFail($designId);
        return $design->isLikedByUser(auth()->id());
    }
}
