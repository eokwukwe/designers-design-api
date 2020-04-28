<?php

namespace App\Http\Controllers\Designs;

use App\Http\Controllers\Controller;
use App\Jobs\UploadImage;
use Illuminate\Http\Request;
use JD\Cloudder\Facades\Cloudder;
use Intervention\Image\Facades\Image;

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

        // get original filename, replace any spaces $ underscores
        // with dash '-' amd convert to lowercase.
        // E.g. Business Cards.png ==> business-cards.png
        $image_name = preg_replace(
            '/[\s_]+/',
            '-',
            strtolower($image->getClientOriginalName())
        );

        $filename = 'original' . "-" . time() . '-' . $image_name;

        // move image to temporary location (tmp)
        $tmp = $image->storeAs('uploads' . DIRECTORY_SEPARATOR . 'original', $filename, 'tmp');

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
