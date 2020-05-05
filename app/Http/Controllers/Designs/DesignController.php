<?php

namespace App\Http\Controllers\Designs;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Repositories\Contracts\IDesign;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Eloquent\Criteria\{
    IsLive,
    ForUser,
    EagerLoad,
    LatestFirst
};

class DesignController extends Controller
{
    protected $designs;

    public function __construct(IDesign $designs)
    {
        $this->designs = $designs;
    }

    public function index()
    {
        $designs = $this->designs->withCriteria([
            new LatestFirst,
            new IsLive,
            new ForUser(1),
            new EagerLoad(['comments'])
        ])->all();

        return DesignResource::collection($designs);
    }

    /**
     *  find design by ID
     */
    public function findDesign($id)
    {
        return new DesignResource($this->designs->find($id));
    }

    /**
     * Update design
     */
    public function update(Request $request, $id)
    {
        $design = $this->designs->find($id);

        // Apply policy
        $this->authorize('update', $design);

        $this->validate($request, [
            'title' => ['required', 'unique:designs,title,' . $design->id],
            'description' => ['required', 'string', 'min:20', 'max:200'],
            'tags' => ['required', 'array'],
            'team' => ['required_if:assign_to_team,true']
        ]);

        $design = $this->designs->update($id, [
            'team_id' => $request->team,
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
            'is_live' => !$design->upload_successful
                ? false : $request->is_live,
        ]);

        // apply the tags
        $this->designs->applyTags($id, $request->tags);

        return new DesignResource($design);
    }

    /**
     * Delete design from database
     */
    public function destroy($id)
    {
        $design = $this->designs->find($id);

        // Apply policy
        $this->authorize('delete', $design);

        // delete file associated with the record from the disk
        $sizes = ['thumbnail', 'large', 'original'];
        foreach ($sizes as $size) {
            if ($this->fileExits($design, $size)) {
                $this->deleteFile($design, $size);
            }
        }

        // delete image from the database
        $this->designs->delete($id);

        return response()->json([
            'message' => 'Record deleted successfully'
        ], 200);
    }

    /**
     * Like dsign
     */
    public function like($id)
    {
        $this->designs->like($id);

        return response()->json([
            'message' => 'Successfully'
        ], 200);
    }

    /**
     * Check if a user has liked a design
     */
    public function checkIfUserHasLiked($designId)
    {
        $liked = $this->designs->designLikedByUser($designId);

        return response()->json([
            'liked' => $liked,
        ], 200);
    }

    /**
     * Find design by slug
     */
    public function findBySlug($slug)
    {
        $design = $this->designs->withCriteria([
            new IsLive
        ])->findWhereFirst('slug', $slug);

        return new DesignResource($design);
    }


    /**
     * Search for designs
     */
    public function search(Request $request)
    {
        $designs = $this->designs->search($request);

        return DesignResource::collection($designs);
    }

    /**
     * Check if a file exists in a disk
     */
    protected function fileExits($design, $size)
    {
        return Storage::disk($design->disk)->exists(
            "uploads/designs/{$size}/" .
                preg_replace('/original/', $size, $design->image)
        );
    }

    /**
     * Delete from from disk
     */
    protected function deleteFile($design, $size)
    {
        return Storage::disk($design->disk)->delete(
            "uploads/designs/{$size}/" .
                preg_replace('/original/', $size, $design->image)
        );
    }
}
