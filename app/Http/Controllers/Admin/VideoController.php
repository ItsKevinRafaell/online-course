<?php

// namespace App\Http\Controllers\Admin;

// use App\Models\Video;
// use App\Models\Series;
// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;

// class VideoController extends Controller
// {
//     public function create($slug)
//     {
//         $series = Series::where('slug', $slug)->first();

//         return view('admin.video.create', compact('series'));
//     }

//     public function store(Request $request, $slug)
//     {
//         $series = Series::where('slug', $slug)->first();

//         $series->videos()->create([
//             'name' => $request->name,
//             'video_code' => $request->video_code,
//             'episode' => $request->episode,
//             'duration' => $request->duration,
//             'intro' => $request->intro ? 1 : 0
//         ]);

//         return redirect(route('admin.series.show', $series->slug))->with('toast_success', 'Video created successfully ');
//     }

//     public function edit($slug, $video_code)
//     {
//         $series = Series::where('slug', $slug)->first();

//         $video = Video::where('video_code', $video_code)->first();

//         return view('admin.video.edit', compact('series', 'video'));
//     }

//     public function update(Request $request, $slug, $video_code)
//     {
//         $series = Series::where('slug', $slug)->first();

//         $video = Video::where('video_code', $video_code)->first();

//         $video->update([
//             'name' => $request->name,
//             'video_code' => $request->video_code,
//             'episode' => $request->episode,
//             'duration' => $request->duration,
//             'intro' => $request->intro ? 1 : 0
//         ]);

//         return redirect(route('admin.series.show', $series->slug))->with('toast_success', 'Video updated successfully ');
//     }

//     public function destroy(video $video)
//     {
//         $video->delete();

//         return back()->with('toast_success', 'Video deleted successfully');
//     }
// }
