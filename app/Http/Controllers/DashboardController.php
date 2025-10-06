<?php

namespace App\Http\Controllers;

use App\Models\Cctv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    // ========== DASHBOARD UTAMA ==========
    public function index()
    {
        $cameras = Cctv::all();
        return view('dashboard', compact('cameras'));
    }

    // ========== AMBIL DATA EVENTS UNTUK DURASI REALTIME ==========
    public function getEvents(Request $request)
    {
        try {
            $events = Http::get(pythonApi('events_json'))->json() ?? [];

            $events = collect($events)->map(function ($ev) {
                $ev['is_active'] = empty($ev['end_time']);
                $ev['start_ts'] = strtotime($ev['start_time']);
                return $ev;
            });

            return response()->json([
                'success' => true,
                'data' => $events->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ========== AMBIL DATA PEOPLE COUNT ==========
    public function getPeopleCount()
    {
        try {
            $cameras = Cctv::all();
            $events = Http::get(pythonApi('events_json'))->json() ?? [];
            $events = collect($events);

            $peopleCounts = [];

            foreach ($cameras as $cam) {
                // Ambil event terbaru untuk kamera ini
                $latest = $events
                    ->where('camera_id', $cam->id)
                    ->sortByDesc('start_time')
                    ->first();

                // Gunakan hanya 1 event terakhir
                $peopleCounts[$cam->id] = $latest['people_count'] ?? 0;
            }

            $totalPeople = array_sum($peopleCounts);

            return response()->json([
                'success' => true,
                'people_counts' => $peopleCounts,
                'total_people' => $totalPeople
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // ========== FITUR SEARCH ==========
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $cameras = Cctv::where('name', 'like', "%{$query}%")
            ->orWhere('rtsp_url', 'like', "%{$query}%")
            ->get();

        return response()->json([
            'success' => true,
            'cameras' => $cameras
        ]);
    }
}
