<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cctv;
use Illuminate\Support\Facades\Http;

class CameraController extends Controller
{
    public function storeToDb(Request $request)
    {
        $cam = new Cctv();
        $cam->name = $request->name;
        $cam->rtsp_url = $request->rtsp_url;
        $cam->save();

        // register ke Python
        Http::post(pythonApi('camera'), [
            'id'   => $cam->id,
            'name' => $cam->name,
            'rtsp_url' => $cam->rtsp_url,
        ]);

        return back()->with('status', 'Kamera berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $cam = Cctv::findOrFail($id);
        $cam->delete();

        Http::delete(pythonApi("camera/{$id}"));

        return back()->with('status', 'Kamera dihapus');
    }

    public function startRecording($id)
    {
        $resp = Http::post(pythonApi("start_recording/{$id}"));
        return back()->with('status', $resp->successful() ? 'Recording started' : 'Failed to start');
    }

    public function editZone($id)
    {
        $camera = Cctv::findOrFail($id);

        // Ambil zona dari DB
        $zonesDb = $camera->zones()->get()->map(function ($z) {
            return [
                'id'         => $z->id,
                'name'       => $z->name,
                'max_people' => $z->max_people ?? 0,
                'source'     => 'db', // penanda sumber
            ];
        })->toArray();

        // Ambil zona dari API
        try {
            $resp = Http::get(pythonApi("cameras/{$id}/zones"));
            $zonesApi = $resp->successful() ? collect($resp->json())->map(function ($z) {
                return [
                    'id'         => $z['id'],
                    'name'       => $z['name'],
                    'max_people' => $z['max_people'] ?? 0,
                    'source'     => 'api', // penanda sumber
                ];
            })->toArray() : [];
        } catch (\Exception $e) {
            $zonesApi = [];
        }

        // Gabungkan keduanya
        $zones = array_merge($zonesDb, $zonesApi);

        return view('cameras.edit-zone', compact('camera', 'zones'));
    }



    public function storeZone(Request $request, $id)
    {
        $resp = Http::post(pythonApi("camera/{$id}/zone"), [
            'name'        => $request->zone_name,
            'coordinates' => $request->zone_coordinates,
            'max_people'  => $request->zone_max_people,
        ]);

        return back()->with('status', $resp->successful() ? 'Zona disimpan' : 'Gagal simpan zona');
    }

    public function deleteZone($id, $zoneId)
    {
        $resp = Http::delete(pythonApi("camera/{$id}/zone/{$zoneId}"));
        return back()->with('status', $resp->successful() ? 'Zona dihapus' : 'Gagal hapus zona');
    }

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
