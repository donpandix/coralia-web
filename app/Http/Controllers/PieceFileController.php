<?php

namespace App\Http\Controllers;

use App\Models\Piece;
use App\Models\PieceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PieceFileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Piece $piece, PieceFile $file): StreamedResponse
    {
        abort_unless($file->piece_id === $piece->id, 404);
        abort_unless(Gate::allows('view', $piece), 404);

        $disk = Storage::disk($file->storage_disk);
        abort_unless($disk->exists($file->storage_path), 404);

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return $disk->response(
            $file->storage_path,
            $file->original_filename,
            [
                'Content-Disposition' => $disposition.'; filename="'.$file->original_filename.'"',
                'Content-Type' => $file->mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
