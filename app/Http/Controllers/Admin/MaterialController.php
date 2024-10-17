<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Point;
use App\Models\Series;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function create($slug)
    {
        $series = Series::where('slug', $slug)->with('chapters.points')->firstOrFail();
        return view('admin.materials.create', compact('series'));
    }

public function store(Request $request, $slug)
{
    $series = Series::where('slug', $slug)->firstOrFail();

    // Validasi
    if ($request->chapter_id === "new") {
        $request->validate([
            'new_chapter_title' => 'required_without:chapter_id|string|max:255',
            'new_chapter_order' => 'nullable|integer',
            'points.*.title' => 'nullable|string|max:255',
            'points.*.content' => 'nullable',
        ]);
    } else {
        $request->validate([
            'chapter_id' => 'exists:chapters,id',
            'edit_chapter_title' => 'nullable|string|max:255',
            'edit_chapter_order' => 'nullable|integer',
            'points.*.title' => 'nullable|string|max:255',
            'points.*.content' => 'nullable',
        ]);
    }

    // Proses Chapter
    if ($request->chapter_id !== "new") {
        // Update Chapter yang Ada
        $chapter = Chapter::findOrFail($request->chapter_id);
        $chapter->update([
            'title' => $request->edit_chapter_title ?? $chapter->title,
            'order' => $request->edit_chapter_order ?? $chapter->order,
        ]);
    } else {
        // Buat Chapter Baru
        $chapter = $series->chapters()->create([
            'title' => $request->new_chapter_title,
            'order' => $request->new_chapter_order ?? 1,
        ]);
    }

    // Loop untuk Menambah atau Memperbarui Points
    foreach ($request->points as $pointData) {
        if (!empty($pointData['title']) && !empty($pointData['content'])) {
            if (isset($pointData['id']) && !empty($pointData['id'])) {
                // Update Point yang Ada
                $point = Point::findOrFail($pointData['id']);
                $point->update([
                    'title' => $pointData['title'],
                    'content' => $pointData['content'],
                    'order' => $pointData['order'] ?? $point->order,
                ]);
            } else {
                // Tambahkan Point Baru
                $chapter->points()->create([
                    'title' => $pointData['title'],
                    'content' => $pointData['content'],
                    'order' => $pointData['order'] ?? 1,
                ]);
            }
        }
    }

    // Hapus Points jika ada
    if ($request->has('deleted_points')) {
        Point::whereIn('id', $request->deleted_points)->delete();
    }

    // Hapus Chapter jika ada
    if ($request->has('delete_chapter')) {
        $chapterToDelete = Chapter::findOrFail($request->delete_chapter);
        $chapterToDelete->points()->delete(); // Hapus semua point terkait
        $chapterToDelete->delete();
    }

    // Redirect kembali dengan pesan sukses
    return redirect(route('materi.create', $series->slug))
        ->with('toast_success', 'Materi berhasil disimpan');
}


    public function edit($slug, $chapterId)
    {
        $series = Series::where('slug', $slug)->firstOrFail();
        $chapter = Chapter::findOrFail($chapterId);

        return view('admin.materials.edit', compact('series', 'chapter'));
    }

    public function update(Request $request, $slug, $chapterId)
    {
        $series = Series::where('slug', $slug)->firstOrFail();
        $chapter = Chapter::findOrFail($chapterId);

        $request->validate([
            'chapter_title' => 'required|string|max:255',
            'points' => 'required|array',
            'points.*.title' => 'required|string|max:255',
            'points.*.content' => 'required',
        ]);

        $chapter->update([
            'title' => $request->chapter_title,
            'order' => $request->chapter_order ?? 1,
        ]);

        foreach ($request->points as $pointId => $pointData) {
            $point = Point::find($pointId);

            if ($point) {
                $point->update([
                    'title' => $pointData['title'],
                    'content' => $pointData['content'],
                    'order' => $pointData['order'] ?? 1,
                ]);
            } else {
                $chapter->points()->create([
                    'title' => $pointData['title'],
                    'content' => $pointData['content'],
                    'order' => $pointData['order'] ?? 1,
                ]);
            }
        }

        return redirect(route('admin.series.show', $series->slug))->with('toast_success', 'Materi berhasil diperbarui');
    }

    public function destroy($chapterId)
    {
        $chapter = Chapter::findOrFail($chapterId);
        $chapter->delete();

        return back()->with('toast_success', 'Materi berhasil dihapus');
    }
}
