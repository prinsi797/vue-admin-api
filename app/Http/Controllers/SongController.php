<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Song;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class SongController extends Controller
{
    public function index()
    {
        return Song::with(['artist', 'album'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'album_id' => 'nullable|exists:albums,id',
            'file_url' =>'nullable|file|mimes:audio/mpeg,mpga,mp3,wav,aac'
            // 'file_url' => 'nullable|file|mimes:mp4,mov,ogg,qt' 
        ]);

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('file_url')) {
            $filePath = $request->file('file_url')->store('file_url', 'public');
        }

        $song = Song::create([
            'name' => $request->name,
            'artist_id' => $request->artist_id,
            'album_id' => $request->album_id,
            'file_url' => $filePath ? asset('storage/' . $filePath) : null, // Full path
        ]);

        return response()->json($song, 201);
    }

    public function show($id)
    {
        $song = Song::with(['artist', 'album'])->findOrFail($id);

        if ($song->file_url) {
            $song->file_url = asset('storage/' . $song->file_url);
        }
        return $song;
    }

    public function update(Request $request, $id)
    {
        $song = Song::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'album_id' => 'nullable|exists:albums,id',
            'file_url' => 'nullable|mimes:audio/mpeg,mpga,mp3,wav', // Max size 10 MB
        ]);

        // Handle file upload
        if ($request->hasFile('file_url')) {
            // Delete old file if exists
            if ($song->file_url) {
                $oldFilePath = str_replace(asset('storage/'), '', $song->file_url);
                Storage::disk('public')->delete($oldFilePath);
            }

            $filePath = $request->file('file_url')->store('songs', 'public');
            $song->file_url = asset('storage/' . $filePath); // Full path
        }

        $song->update($request->except('file_url'));

        return response()->json($song);
    }

    public function destroy($id)
    {
        $song = Song::findOrFail($id);
        // Delete file if exists
        if ($song->file_url) {
            $filePath = str_replace(asset('storage/'), '', $song->file_url);
            Storage::disk('public')->delete($filePath);
        }

        $song->delete();

        return response()->json(null, 204);
    }
}
