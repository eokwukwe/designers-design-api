<?php

namespace App\Http\Controllers\Designs;

use App\Models\Design;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DesignController extends Controller
{
    public function update(Request $request, Design $design)
    {
        $this->validate($request, [
            'title' => ['required', 'unique:designs,title,' . $design->id],
            'description' => ['required', 'string', 'min:20', 'max:200'],
        ]);

        $design->update([
            'title' => $request->title,
            'description' => $request->description,
            'slug' => Str::slug($request->title),
            'is_live' => !$design->upload_successful
                ? false : $request->is_live,
        ]);

        

        return response()->json($design, 200);
    }
}
