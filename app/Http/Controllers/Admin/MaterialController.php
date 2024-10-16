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

    // dd($request->all());

    if ($request->chapter_id === "new") {
         $request->validate([
        'new_chapter_title' => 'required_without:chapter_id|string|max:255|nullable',
        'new_chapter_order' => 'nullable|integer',
        'points' => 'required|array',
        'points.*.title' => 'required|string|max:255',
        'points.*.content' => 'required',
    ]);
    } else {
        $request->validate([
        'chapter_id' => 'exists:chapters,id', 
        'new_chapter_title' => 'required_without:chapter_id|string|max:255|nullable',
        'new_chapter_order' => 'nullable|integer',
        'points' => 'required|array',
        'points.*.title' => 'required|string|max:255',
        'points.*.content' => 'required',
    ]);
    }

    if ($request->chapter_id !== "new") {
        $chapter = Chapter::findOrFail($request->chapter_id);
    } else {
        $chapter = $series->chapters()->create([
            'title' => $request->new_chapter_title,
            'order' => $request->new_chapter_order ?? 1,
        ]);
    }

    // Loop untuk menambah atau update points
    foreach ($request->points as $pointData) {
        if (isset($pointData['id']) && !empty($pointData['id'])) {
            $point = Point::findOrFail($pointData['id']);
            $point->update([
                'title' => $pointData['title'],
                'content' => $pointData['content'],
                'order' => $pointData['order'] ?? $point->order,
            ]);
        } else {
            $chapter->points()->create([
                'title' => $pointData['title'],
                'content' => $pointData['content'],
                'order' => $pointData['order'] ?? 1,
            ]);
        }
    }

    // Cek apakah ada poin atau chapter yang dihapus
    if ($request->has('deleted_points')) {
        Point::whereIn('id', $request->deleted_points)->delete();
    }

    if ($request->has('delete_chapter')) {
        $chapterToDelete = Chapter::findOrFail($request->delete_chapter);
        $chapterToDelete->points()->delete(); // Hapus semua poin terkait
        $chapterToDelete->delete();
    }

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
