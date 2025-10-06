<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cctv;
use Illuminate\Support\Facades\Http;

class CameraController extends Controller
{
    // Tambah kamera baru ke DB + register ke Python
    public function storeToDb(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rtsp_url' => 'required|string',
        ]);

        $cam = Cctv::create([
            'name' => $request->name,
            'rtsp_url' => $request->rtsp_url,
            'is_active' => 1,
            'min_session_duration' => 10,
        ]);

        // register ke Python
        try {
            Http::post(pythonApi('camera'), [
                'id'       => $cam->id,
                'name'     => $cam->name,
                'rtsp_url' => $cam->rtsp_url,
            ]);
        } catch (\Exception $e) {
            return back()->with('status', 'Kamera tersimpan tapi gagal register ke Python: ' . $e->getMessage());
        }

        return back()->with('status', 'Kamera berhasil ditambahkan');
    }


    // Hapus kamera dari DB + beritahu Python
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

    // Start recording
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

    // Edit zone
    public function editZone($id)
    {
        $camera = Cctv::findOrFail($id);

        // --- Zona dari DB
        $zonesDb = $camera->zones()->get()->map(function ($z) {
            return [
                'id'         => $z->id,
                'name'       => $z->zone_name,
                'max_people' => $z->max_people ?? 0,
                'coordinates' => $z->coordinates,
                'source'     => 'db',
            ];
        })->toArray();

        // --- Zona dari API
        try {
            $resp = Http::get(pythonApi("cameras/{$id}/zones"));
            $zonesApi = $resp->successful() ? collect($resp->json())->map(function ($z) {
                return [
                    'id'         => $z['id'],
                    'name'       => $z['name'],
                    'max_people' => $z['max_people'] ?? 0,
                    'coordinates' => $z['coordinates'] ?? null,
                    'source'     => 'api',
                ];
            })->toArray() : [];
        } catch (\Exception $e) {
            $zonesApi = [];
        }

        // Gabungkan keduanya
        $zones = array_merge($zonesDb, $zonesApi);

        return view('cameras.edit-zone', compact('camera', 'zones'));
    }


    // Simpan zona baru
    public function storeZone(Request $request, $id)
    {
        $request->validate([
            'zone_name' => 'required|string|max:255',
            'zone_coordinates' => 'required|string',
            'zone_max_people' => 'nullable|integer|min:0',
        ]);

        // Simpan ke DB
        $zone = new \App\Models\Zone();
        $zone->camera_id = $id;
        $zone->zone_name = $request->zone_name;
        $zone->coordinates = $request->zone_coordinates;
        $zone->max_people = $request->zone_max_people ?? 4;
        $zone->max_empty_duration = 300;
        $zone->inactive_threshold = 0;
        $zone->save();

        // Kirim ke Python API juga (sinkronisasi)
        try {
            Http::post(pythonApi("camera/{$id}/zone"), [
                'name'        => $zone->zone_name,
                'coordinates' => $zone->coordinates,
                'max_people'  => $zone->max_people,
            ]);
        } catch (\Exception $e) {
            // Bisa diabaikan kalau Python offline
        }

        return back()->with('status', 'Zona berhasil disimpan ke DB dan sinkron ke Python');
    }



    // Hapus zona
    public function deleteZone($id, $zoneId)
    {
        $camera = Cctv::findOrFail($id);

        // Coba hapus dari DB dulu
        $zone = $camera->zones()->find($zoneId);
        if ($zone) {
            $zone->delete();
            return back()->with('status', 'Zona berhasil dihapus dari database');
        }

        // Kalau tidak ada di DB, hapus via Python API
        try {
            $resp = Http::delete(pythonApi("camera/{$id}/zone/{$zoneId}"));
            return back()->with('status', $resp->successful() ? 'Zona dihapus dari Python API' : 'Gagal hapus zona di Python');
        } catch (\Exception $e) {
            return back()->with('status', 'Gagal hapus zona: ' . $e->getMessage());
        }
    }


    //  Atur minimal durasi sesi
    public function setMinSession(Request $request, $id)
    {
        $totalSeconds =
            ($request->min_duration_hours * 3600) +
            ($request->min_duration_minutes * 60) +
            $request->min_duration_seconds;

        $resp = Http::post(pythonApi("camera/{$id}/min_session"), [
            'min_session_duration' => $totalSeconds,
        ]);

        return back()->with('status', $resp->successful() ? 'Durasi disimpan' : 'Gagal simpan durasi');
    }
}
