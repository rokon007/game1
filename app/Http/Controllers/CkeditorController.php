<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CkeditorController extends Controller
{
    public function upload(Request $request)
    {
        Log::info('CKEditor Upload:', [
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

                // Store in storage/app/public/ckeditor directory
                $path = $file->storeAs(
                    'ckeditor/' . date('Y/m'),
                    $cleanName,
                    'public'
                );

                $url = Storage::url($path);

                // Return response in CKEditor expected format
                return response()->json([
                    'uploaded' => true,
                    'fileName' => $cleanName,
                    'url' => $url
                ]);
            }
        } catch (\Exception $e) {
            Log::error('CKEditor Upload Error: ' . $e->getMessage());
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

            // Extract storage path from URL
            $path = parse_url($imageUrl, PHP_URL_PATH);
            $storagePath = str_replace('/storage/', '', $path);

            // Delete from storage
            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
                return response()->json(['success' => true, 'message' => 'Image deleted successfully']);
            }

            return response()->json(['success' => false, 'message' => 'Image not found'], 404);
        } catch (\Exception $e) {
            Log::error('CKEditor Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete image: ' . $e->getMessage()], 500);
        }
    }
}
