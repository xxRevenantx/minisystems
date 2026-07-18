<?php

namespace App\Http\Controllers;

use App\Models\AiSocialGenerationImage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SocialAiMediaController extends Controller
{
    public function preview(AiSocialGenerationImage $image): Response
    {
        abort_unless($image->generacion()->where('user_id', auth()->id())->exists(), 403);
        abort_unless($image->ruta_preview && Storage::disk('local')->exists($image->ruta_preview), 404);

        return response(Storage::disk('local')->get($image->ruta_preview), 200, [
            'Content-Type' => $image->mime_type ?: 'image/jpeg',
            'Cache-Control' => 'private, max-age=900',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
