<?php

namespace App\Http\Controllers\Resources;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoRequest;
use App\Jobs\ConvertVideoForStreaming;
use App\Models\Video;
use Bouncer;
use Carbon\Carbon;
use Exception;
use FFMpeg\Exception\InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ProtoneMedia\LaravelFFMpeg\Exporters\EncodingException;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class VideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		Log::info(['>>> VideoController - index: .']);
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVideoRequest $request)
    {
		Log::info(['>>> VideoController - store: .']);
		Log::debug(['>>> VideoController - store: .']);
		Log::error(['>>> VideoController - store: .']);
		Log::warning(['>>> VideoController - store: .']);
        if ($request->user()->can('create', Video::class)) {
			Log::info(['>>> VideoController - store: i1']);
            if ($request->has('video')) {
				Log::info(['>>> VideoController - store: i2']);
                try {
					Log::info(['>>> VideoController - store: t1']);
                    $video = Video::create([
                        'disk' => 'video_storage',
                        'original_name' => $request->file('video')->getClientOriginalName(),
                        'path' => $request->file('video')->store('temp', 'video_storage'),
                        'name' => $request->file('video')->getClientOriginalName(),
                        'created_at' => Carbon::now(),
                    ]);
					
					Log::info(['>>> VideoController - store: t2']);

                    // Dispatch a queue event to update video size and filetype
                    $this->dispatch(new ConvertVideoForStreaming($video));
					
					Log::info(['>>> VideoController - store: t3']);

                    return response()->json([
                        'msg' => 'Video uploaded successfully',
                        'uuid' => $video->id,
                        'name' => $video->name,
                    ]);
					
					Log::info(['>>> VideoController - store: t4']);
                } catch (Exception $exception) {
					Log::info(['>>> VideoController - store: e']);
					Log::error(['>>> VideoController - store: ',$exception]);
                    abort(500, 'Failed to process video');
                }
            } else {
				Log::info(['>>> VideoController - store: e.i2']);
				Log::debug(['>>> VideoController - store: request missing video; ',$request]);
                abort(404);
            }
        } else {
			Log::info(['>>> VideoController - store: e.i1']);
			Log::debug(['>>> VideoController - store: user not able to create video; ',$request->user()]);
            abort(403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
		Log::info(['>>> VideoController - show: .', $id]);
        if (Auth::user()) {
            $video = Video::find($id);
            $videoFile = Storage::disk('video_storage')->get($video->path);
            $response = FacadeResponse::make($videoFile, 200);
            $response->header('Content-Type', 'video/mp4');
            return $response;
        } else {
            abort(403);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
		Log::info(['>>> VideoController - edit: .', $id]);
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		Log::info(['>>> VideoController - update: .', $id]);
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		Log::info(['>>> VideoController - destroy: .', $id]);
        //
    }
}
