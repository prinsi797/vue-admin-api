<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Album;
use Illuminate\Support\Facades\Storage;

class AlbumController extends Controller
{
    public function index()
    {
        return Album::with('artist')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max size 2 MB
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('albums', 'public');
        }

        $album = Album::create([
            'name' => $request->name,
            'artist_id' => $request->artist_id,
            'cover_image' => $imagePath ? asset('storage/' . $imagePath) : null, // Full path
        ]);

        return response()->json($album, 201);
    }

    public function show($id)
    {
        $album = Album::with('artist')->findOrFail($id);

        if ($album->cover_image) {
            $album->cover_image = asset('storage/' . $album->cover_image);
        }

        return $album;
    }

    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'artist_id' => 'required|exists:artists,id',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max size 2 MB
        ]);

        // Handle image upload
        if ($request->hasFile('cover_image')) {
            // Delete old cover image if exists
            if ($album->cover_image) {
                $oldImagePath = str_replace(asset('storage/'), '', $album->cover_image);
                Storage::disk('public')->delete($oldImagePath);
            }

            $imagePath = $request->file('cover_image')->store('albums', 'public');
            $album->cover_image = asset('storage/' . $imagePath); // Full path
        }

        $album->update($request->except('cover_image'));

        return response()->json($album);
    }

    public function destroy($id)
    {
        $album = Album::findOrFail($id);

        // Delete cover image if exists
        if ($album->cover_image) {
            $imagePath = str_replace(asset('storage/'), '', $album->cover_image);
            Storage::disk('public')->delete($imagePath);
        }

        $album->delete();

        return response()->json(null, 204);
    }
}
