<?php

namespace App\Repositories\Eloquent;

use App\Models\Design;
use App\Repositories\Contracts\IDesign;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Http\Request;

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

    public function search(Request $request)
    {
        $query = (new $this->model)->newQuery();

        $query->where('is_live', true);

        // only designs with comments
        if ($request->has_comments) {
            $query->has('comments');
        }

        // only designs assigned to teams
        if ($request->has_team) {
            $query->has('team');
        }

        // search title and description from provided strings
        // only designs assigned to teams
        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // order the query by likes or latest first
        if ($request->orderBy == 'likes') {
            $query->withCount('likes')->orderByDesc('likes_count');
        } else {
            $query->latest();
        }

        return $query->get();
    }
}
