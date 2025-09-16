@extends('layouts.app')

@section('title', 'Edit Zona Kamera - ' . $camera->name)

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">Edit Zona - {{ $camera->name }}
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Zona Kamera</h5>
                    </div>
                    <div class="card-body">
                        {{-- Daftar Zona --}}
                        <h6 class="mb-3">Daftar Zona Tersimpan</h6>
                        @if (!empty($zones))
                            <ul class="list-group">
                                @foreach ($zones as $zone)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $zone['name'] }}</strong>
                                            <small class="text-muted">
                                                (Max: {{ $zone['max_people'] }})
                                                - sumber: {{ strtoupper($zone['source']) }}
                                            </small>
                                        </div>
                                        <form method="POST"
                                            action="{{ route('cameras.deleteZone', [$camera->id, $zone['id']]) }}"
                                            onsubmit="return confirm('Hapus zona {{ $zone['name'] }} ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">Belum ada zona untuk kamera ini.</p>
                        @endif

                        <hr>

                        {{-- Form tambah zona --}}
                        <form id="zoneForm" method="POST" action="{{ route('cameras.storeZone', $camera->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nama Zona Baru</label>
                                <input type="text" name="zone_name" id="zone_name" class="form-control"
                                    placeholder="Contoh: Pintu Masuk">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Maksimal Orang</label>
                                <input type="number" name="zone_max_people" id="zone_max_people" class="form-control"
                                    value="0" min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Gambar Zona</label>
                                <div class="position-relative border rounded">
                                    <img id="video-feed" src="{{ pythonApi("video_feed/{$camera->id}") }}"
                                        class="img-fluid w-100" alt="Video Feed">
                                    <canvas id="drawing-canvas" class="position-absolute top-0 start-0 w-100 h-100"
                                        style="cursor: crosshair;"></canvas>
                                </div>
                                <small class="text-muted">Klik pada gambar untuk menggambar polygon zona.</small>
                            </div>

                            <input type="hidden" name="zone_coordinates" id="zone-input">

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Zona</button>
                                <button type="button" id="reset-drawing-btn" class="btn btn-warning">Reset Gambar</button>
                            </div>
                        </form>

                        <hr>

                        {{-- Durasi minimal sesi --}}
                        <form method="POST" action="{{ route('cameras.setMinSession', $camera->id) }}" class="mt-4">
                            @csrf
                            <h6 class="mb-3">Durasi Sesi Minimal</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Jam</label>
                                    <input type="number" name="hours" class="form-control"
                                        value="{{ floor($camera->min_session_duration / 3600) }}" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Menit</label>
                                    <input type="number" name="minutes" class="form-control"
                                        value="{{ floor(($camera->min_session_duration % 3600) / 60) }}" min="0"
                                        max="59">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Detik</label>
                                    <input type="number" name="seconds" class="form-control"
                                        value="{{ $camera->min_session_duration % 60 }}" min="0" max="59">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success mt-3">Simpan Durasi</button>
                        </form>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary rounded-pill">
                    <i class="bx bx-left-arrow-alt me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    {{-- JS untuk menggambar zona --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('drawing-canvas');
            const videoFeed = document.getElementById('video-feed');
            const zoneInput = document.getElementById('zone-input');
            const resetBtn = document.getElementById('reset-drawing-btn');
            const ctx = canvas.getContext('2d');
            let points = [];

            function resizeCanvas() {
                canvas.width = videoFeed.clientWidth;
                canvas.height = videoFeed.clientHeight;
                redraw();
            }

            function redraw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                if (points.length > 1) {
                    ctx.beginPath();
                    ctx.moveTo(points[0][0], points[0][1]);
                    for (let i = 1; i < points.length; i++) {
                        ctx.lineTo(points[i][0], points[i][1]);
                    }
                    ctx.closePath();
                    ctx.strokeStyle = '#FFFF00';
                    ctx.lineWidth = 2;
                    ctx.stroke();
                }
            }

            canvas.addEventListener('click', e => {
                const rect = canvas.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                points.push([x, y]);
                zoneInput.value = JSON.stringify(points);
                redraw();
            });

            resetBtn.addEventListener('click', () => {
                points = [];
                zoneInput.value = '[]';
                redraw();
            });

            window.addEventListener('resize', resizeCanvas);
            videoFeed.onload = resizeCanvas;
            if (videoFeed.complete) resizeCanvas();
        });
    </script>
@endsection
