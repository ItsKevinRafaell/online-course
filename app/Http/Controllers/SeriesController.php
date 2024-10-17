<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Carbon\Carbon;
use App\Models\Video;
use App\Models\Series;
use App\Traits\HasSeries;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class SeriesController extends Controller
{
    use HasSeries;

    public function index()
    {
        // get all series
        $series = Series::with('chapters')->latest()->get();

        // return to landing page
        return view('landing.series.index', compact('series'));
    }

    public function show($slug)
{
    // Dapatkan series berdasarkan slug
    $series = Series::where('slug', $slug)->firstOrFail();

    // Dapatkan chapters beserta points terkait
    $chapters = Chapter::where('series_id', $series->id)
                ->with('points:id,chapter_id,title,order')
                ->select('id', 'title', 'order')
                ->get();

    // Hitung jumlah members
    $members = $this->members($series)->count();

    // Cek transaksi untuk user saat ini
    $transaction = Transaction::with('details')
        ->where('user_id', Auth::id())
        ->where('status', 1)
        ->whereHas('details', function ($query) use ($series) {
            $query->where('series_id', $series->id);
        })
        ->get();

    // Tentukan apakah sudah dibeli
    $purchased = $transaction->count() > 0 
        ? $this->userSeries()->get() 
        : 0;

    return view('landing.series.show', compact('series', 'chapters', 'members', 'purchased', 'transaction'));
}


    public function video($slug, $episode)
    {
        // get series by slug
        $series = Series::where('slug', $slug)->first();

        // get video all video by series
        $video = Video::where('series_id', $series->id)->where('episode', $episode)->first();

        // get transaction by user id
        $transaction = Transaction::with('details')->where('user_id', Auth::id())->where('status', 1)
        ->whereHas('details', function($query) use($series){
            $query->where('series_id', $series->id);
        })->get();

        // define variable $purchased
        $purchased = null;

        // if transaction is not empty
        if($transaction->count() > 0){
            // get all userSeries, call from method userSeries, trait hasSeries
            $purchased = $this->userSeries()->get();
        }else{
            $purchased = 0;
        }

        // define variable $videos
        $videos = '';

        // user can watch full video if user have this series or user still can watch video only intro video
        if($purchased || $video->intro == 1){
            // if true, get all video by series
            $videos = Video::where('series_id', $series->id)->orderBy('episode')->paginate(10);
        }else{
            // if false, get only intro video
            return back()->with('toast_error', 'You must buy this series first');
        }
        // return to view
        return view('landing.series.video', compact('series','video','videos'));
    }
}
