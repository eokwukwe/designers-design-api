<?php

namespace App\Http\Controllers\Designs;

use App\Models\Design;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    public function update(Request $request, Design $design)
    {
        $this->authorize('update', $design);

        $this->validate($request, [
            'title' => ['required', 'unique:designs,title,' . $design->id],
            'description' => ['required', 'string', 'min:20', 'max:200'],
            'tags' => ['required', 'array'],
        ]);

        $design->update([
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
            'is_live' => !$design->upload_successful
                ? false : $request->is_live,
        ]);

        // apply the tags
        $design->retag($request->tags);

        return new DesignResource($design);
    }

    public function destroy(Design $design)
    {
        $this->authorize('delete', $design);

        // delete file associated with the record from the disk
        $sizes = ['thumbnail', 'large', 'original'];
        foreach ($sizes as $size) {
            if ($this->fileExits($design, $size)) {
                $this->deleteFile($design, $size);
            }
        }

        // delete image from the database
        $design->delete();

        return response()->json([
            'message' => 'Record deleted successfully'
        ], 200);
    }

    protected function fileExits($design, $size)
    {
        return Storage::disk($design->disk)->exists(
            "uploads/designs/{$size}/" .
                preg_replace('/original/', $size, $design->image)
        );
    }

    protected function deleteFile($design, $size)
    {
        return Storage::disk($design->disk)->delete(
            "uploads/designs/{$size}/" .
                preg_replace('/original/', $size, $design->image)
        );
    }
}
