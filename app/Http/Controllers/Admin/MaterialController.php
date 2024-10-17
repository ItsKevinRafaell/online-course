<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Point;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function create($slug)
    {
        $series = Series::where('slug', $slug)->with('chapters.points')->firstOrFail();
        return view('admin.materials.create', compact('series'));
    }

 public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'tags' => 'required|array',
            'price' => 'required|numeric',
            'cover' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'status' => 'nullable|boolean',
        ]);

        $series = new Series();
        $series->name = $request->name;
        $series->price = $request->price;
        $series->description = $request->description;
        $series->level = $request->level;
        $series->status = $request->has('status') ? $request->status : 0;

        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('covers', 'public');
            $series->cover = $coverPath;
        }

        $series->save();

        // Sync tags
        $series->tags()->sync($request->tags);

        return redirect()->route('admin.series.index')->with('toast_success', 'Series berhasil dibuat.');
    }


    public function edit($slug, $chapterId)
    {
        $series = Series::where('slug', $slug)->firstOrFail();
        $chapter = Chapter::findOrFail($chapterId);

        return view('admin.materials.edit', compact('series', 'chapter'));
    }

     public function update(Request $request, $slug)
    {
        // Ambil series berdasarkan slug
        $series = Series::where('slug', $slug)->firstOrFail();

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'tags' => 'required|array',
            'price' => 'required|numeric',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'description' => 'nullable|string',
            'level' => 'required|string|in:Beginner,Intermediate,Advanced',
            'status' => 'nullable|boolean',
        ]);

        // Update data series
        $series->name = $request->name;
        $series->price = $request->price;
        $series->description = $request->description;
        $series->level = $request->level;
        $series->status = $request->has('status') ? $request->status : 0;

        // Upload cover image jika ada
        if ($request->hasFile('cover')) {
            // Hapus cover lama jika ada
            if ($series->cover) {
                Storage::disk('public')->delete($series->cover);
            }
            $coverPath = $request->file('cover')->store('covers', 'public');
            $series->cover = $coverPath;
        }

        $series->save();

        // Sync tags
        $series->tags()->sync($request->tags);

        return redirect()->route('admin.series.index')->with('toast_success', 'Series berhasil diperbarui.');
    }

    public function destroy($chapterId)
    {
        $chapter = Chapter::findOrFail($chapterId);
        $chapter->delete();

        return back()->with('toast_success', 'Materi berhasil dihapus');
    }
}
