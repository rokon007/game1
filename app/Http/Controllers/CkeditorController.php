<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CkeditorController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/temp/'), $fileName);

            $url = asset('uploads/temp/' . $fileName);
            return response()->json([
                'uploaded' => 1,
                'fileName' => $fileName,
                'url' => $url
            ]);
        }

        return response()->json(['uploaded' => 0, 'error' => ['message' => 'File upload failed.']], 400);
    }

    public function deleteImage(Request $request)
    {
        $imageUrl = $request->input('image_url');

        // ফাইল পাথ সেট করুন
        $filePath = public_path('uploads/post_image/' . basename($imageUrl));

        // যদি ফাইলটি থাকে তবে ডিলিট করুন
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        return response()->json(['success' => true]);
    }
}
