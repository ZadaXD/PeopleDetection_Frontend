<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil daftar kamera dari DB Laravel
        $cameras = Cctv::all();

        // Ambil semua events dari backend Python
        $events = Http::get(pythonApi('events_json'))->json() ?? [];

        // Kelompokkan event per kamera
        $eventsByCamera = collect($events)->groupBy('camera_id');

        return view('dashboard', compact('cameras', 'eventsByCamera'));
    }

    public function startRecording($id)
    {
        $resp = Http::post(pythonApi("start_recording/{$id}"));
        return back()->with(
            'status',
            $resp->successful() ? 'Recording started' : 'Failed to start recording'
        );
    }

    public function stopRecording($id)
    {
        $resp = Http::post(pythonApi("stop_recording/{$id}"));
        return back()->with(
            'status',
            $resp->successful() ? 'Recording stopped' : 'Failed to stop recording'
        );
    }

    public function deleteCamera($id)
    {
        $resp = Http::delete(pythonApi("camera/{$id}"));
        return back()->with(
            'status',
            $resp->successful() ? 'Camera deleted' : 'Failed to delete camera'
        );
    }
}
