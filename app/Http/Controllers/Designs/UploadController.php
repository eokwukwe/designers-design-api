<?php

namespace App\Http\Controllers\Designs;

use App\Http\Controllers\Controller;
use App\Jobs\UploadImage;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        // validate the request
        $this->validate($request, [
            'image' => ['required', 'mimes:jpeg,gif,bmp,png', 'max:2048']
        ]);

        // get image
        $image = $request->file('image');
        $image_path = $image->getPathname();

        // get original filename, replace any spaces with underscore '_' and
        // convert to lowercase and add timestamp.
        // E.g. Business Cards.png ==> timestamp()_business_cards.png
        $filename = time(). "_" .preg_replace('/\$+/', '_', strtolower($image->getClientOriginalName()));

        // move image to temporary location (tmp)
        $tmp = $image->storeAs('uploads/original', $filename, 'tmp');

        // create the database record for the uploaded design
        $design = auth()->user()->designs()->create([
            'image' => $filename,
            'disk' => config('site.upload_disk'),
        ]);

        // dispatch a job to handle image manipulation
        $this->dispatch(new UploadImage($design));

        return response()->json($design, 200);

    }
}
