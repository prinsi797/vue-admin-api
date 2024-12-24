<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Artist;
use Illuminate\Support\Facades\Storage;

class ArtistController extends Controller
{
    public function index()
    {
        return Artist::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle profile_picture upload
        $filePath = null;
        if ($request->hasFile('profile_picture')) {
            $filePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $artist = Artist::create([
            'name' => $request->name,
            'genre' => $request->genre,
            'bio' => $request->bio,
            'profile_picture' => $filePath ? asset('storage/' . $filePath) : null, // Full path
        ]);

        return response()->json($artist, 201);
    }


    public function show($id)
    {
        $artist = Artist::findOrFail($id);

        if ($artist->profile_picture) {
            $artist->profile_picture = asset('storage/' . $artist->profile_picture);
        }

        return $artist;
    }

    public function update(Request $request, $id)
    {
        $artist = Artist::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle profile_picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($artist->profile_picture) {
                $oldFilePath = str_replace(asset('storage/'), '', $artist->profile_picture);
                Storage::disk('public')->delete($oldFilePath);
            }

            $filePath = $request->file('profile_picture')->store('profile_pictures', 'public');
            $artist->profile_picture = asset('storage/' . $filePath); // Full path
        }

        $artist->update($request->except('profile_picture'));

        return response()->json($artist);
    }

    public function destroy($id)
    {
        $artist = Artist::findOrFail($id);
        if ($artist->profile_picture) {
            $filePath = str_replace(asset('storage/'), '', $artist->profile_picture);
            Storage::disk('public')->delete($filePath);
        }
        $artist->delete();
        return response()->json('Deleted successfully', 204);
    }
}
