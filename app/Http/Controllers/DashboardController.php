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

    // ➡️ Hapus kamera dari DB + Python API
    public function deleteCamera($id)
    {
        // Hapus dari DB
        $cam = Cctv::find($id);
        if ($cam) {
            $cam->delete();
        }

        // Hapus dari Python API (abaikan error)
        try {
            Http::delete(pythonApi("camera/{$id}"));
        } catch (\Exception $e) {
            // Abaikan error dari Python
        }

        // Selalu kirim pesan sukses
        return back()->with('status', 'Kamera berhasil dihapus');
    }
}
