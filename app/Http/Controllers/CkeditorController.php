<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CkeditorController extends Controller
{
    public function upload(Request $request)
   {
    \Log::info('CKEditor Upload:', [
        'headers' => $request->headers->all(),
        'files' => $request->allFiles()
    ]);
        try {
            // Validate the uploaded file
            $request->validate([
                'upload' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'upload' => ['required', 'file', 'max:2048', function ($attribute, $value, $fail) {
                    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                    if (!in_array($value->getMimeType(), $allowedMimes)) {
                        $fail('The uploaded file must be a valid image (jpeg, png, jpg, gif, or svg).');
                    }
                }],
            ]);

            if ($request->hasFile('upload')) {
                $file = $request->file('upload');

                // Sanitize the file name
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $cleanName = Str::slug($originalName) . '-' . time() . '.' . $extension;

                // Create temp directory if it doesn't exist
                $tempDir = public_path('uploads/temp/');
                if (!File::exists($tempDir)) {
                    File::makeDirectory($tempDir, 0777, true);
                }

                // Move file to temp directory
                $file->move($tempDir, $cleanName);

                $url = asset('uploads/temp/' . $cleanName);

                // Return response in CKEditor expected format
                return response()->json([
                    'uploaded' => true,
                    'fileName' => $cleanName,
                    'url' => $url
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'uploaded' => false,
                'error' => [
                    'message' => 'File upload failed: ' . $e->getMessage()
                ]
            ], 400);
        }

        return response()->json([
            'uploaded' => false,
            'error' => [
                'message' => 'No file uploaded or invalid file type.'
            ]
        ], 400);
    }

    public function deleteImage(Request $request)
    {
        try {
            $imageUrl = $request->input('image_url');

            if (!$imageUrl) {
                return response()->json(['success' => false, 'message' => 'No image URL provided'], 400);
            }

            // Extract filename from URL
            $fileName = basename($imageUrl);

            // Check both temp and post_image directories
            $tempPath = public_path('uploads/temp/' . $fileName);
            $postImagePath = public_path('uploads/post_image/' . $fileName);

            $deleted = false;

            if (File::exists($tempPath)) {
                File::delete($tempPath);
                $deleted = true;
            }

            if (File::exists($postImagePath)) {
                File::delete($postImagePath);
                $deleted = true;
            }

            if ($deleted) {
                return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
            } else {
                return response()->json(['success' => false, 'message' => 'Image not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }
}
