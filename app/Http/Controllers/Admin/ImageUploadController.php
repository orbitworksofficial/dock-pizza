<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ImageUploadController extends Controller
{
    public function __construct(private readonly ImageUploadService $uploads)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'image' => ['required', 'file', 'max:12288'],
            'directory' => ['nullable', 'string', 'in:seo,products,banners'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $result = $this->uploads->store(
                $request->file('image'),
                $request->input('directory', 'seo')
            );

            return response()->json(['success' => true] + $result);
        } catch (RuntimeException $e) {
            // Expected, explainable failures — the message names the cause.
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Image upload failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'The image could not be processed. The error has been logged.',
            ], 500);
        }
    }
}
