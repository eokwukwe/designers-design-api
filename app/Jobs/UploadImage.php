<?php

namespace App\Jobs;

use File;
use Image;
use App\Models\Design;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $design;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Design $design)
    {
        $this->design = $design;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $disk = $this->design->disk;
        $filename = $this->design->image;
        // full path of where the file is store on the system
        $original_file = storage_path() . '/uploads/original/' . $filename;

        try {
            // create a large image and save to tmp disk
            Image::make($original_file)
                ->fit(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(
                    $large = storage_path(
                        'uploads/large/' .
                            preg_replace('/original/', 'large', $filename)
                    )
                );

            // create a thumbnail image and save to tmp disk
            Image::make($original_file)
                ->fit(250, 200, function ($constraint) {
                    $constraint->aspectRatio();
                })
                ->save(
                    $thumbnail = storage_path(
                        'uploads/thumbnail/' .
                            preg_replace('/original/', 'thumbnail', $filename)
                    )
                );

            // store images to permanent disk
            // Original
            if (Storage::disk($disk)->put(
                'uploads/designs/original/' . $filename,
                fopen($original_file, 'r+')
            )) {
                // Delete original file
                File::delete($original_file);
            }

            // Large
            if (Storage::disk($disk)->put(
                'uploads/designs/large/' .  preg_replace('/original/', 'large', $filename),
                fopen($large, 'r+')
            )) {
                // Delete original file
                File::delete($large);
            }
            // thumbnail
            if (Storage::disk($disk)->put(
                'uploads/designs/thumbnail/' .  preg_replace('/original/', 'thumbnail', $filename),
                fopen($thumbnail, 'r+')
            )) {
                // Delete original file
                File::delete($thumbnail);
            }

            // Update database record success flag
            $this->design->update([
                'upload_successful' => true,
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
