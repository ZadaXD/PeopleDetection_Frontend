<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $cameras = Cctv::all();

        // Ambil events dari Python
        $events = Http::get(pythonApi('events_json'))->json() ?? [];

        $events = collect($events)->map(function ($ev) {
            $ev['is_active'] = empty($ev['end_time']);
            // kalau Python sudah kirim duration, biarkan
            return $ev;
        });

        if (request()->ajax()) {
            return response()->json(['data' => $events]);
        }

        return view('dashboard', compact('cameras'));
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
