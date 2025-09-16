@extends('layouts.sneat')

@section('content')
<div class="container">
    <h4 class="fw-bold py-3 mb-4">Dashboard Kamera</h4>

    {{-- Tombol tambah kamera --}}
    <div class="mb-3 text-middle">
        <button class="btn rounded-pill btn-primary" data-bs-toggle="modal" data-bs-target="#addCameraModal">
            <i class="bx bx-cctv me-2"></i> Tambah Kamera
        </button>
    </div>

    <div class="row">
        @forelse($cameras as $cam)
            <div class="col-md-6">
                <div class="card mb-4 shadow">
                    {{-- Stream video dari Python (MJPEG) --}}
                    <img src="{{ pythonApi('video_feed/' . $cam->id) }}"
                         class="card-img-top"
                         alt="{{ $cam->name }}"
                         style="height:300px; object-fit:cover;">

                    <div class="card-body">
                        <h5 class="card-title">{{ $cam->name }}</h5>
                        <p class="card-text"><small class="text-muted">{{ $cam->rtsp_url }}</small></p>

                        <div class="d-flex justify-content-between mb-3">
                            {{-- Start recording --}}
                            <form method="POST" action="{{ route('cameras.start', $cam->id) }}">
                                @csrf
                                <button class="btn btn-success rounded-pill">
                                    <i class="bx bx-caret-right-circle me-2"></i> Start Recording
                                </button>
                            </form>

                            {{-- Edit zone --}}
                            <a href="{{ route('cameras.editZone', $cam->id) }}"
                               class="btn btn-warning rounded-pill">
                                <i class="bx bx-edit me-2"></i> Edit Zone
                            </a>

                            {{-- Hapus kamera dari DB --}}
                            <form method="POST" action="{{ route('cameras.destroy', $cam->id) }}"
                                  onsubmit="return confirm('Hapus kamera ini dari database?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger rounded-pill">
                                    <i class="bx bx-trash me-2"></i> Hapus
                                </button>
                            </form>
                        </div>

                        <div class="mb-3">
                            <strong>Orang Terdeteksi:</strong>
                            <span id="people-count-{{ $cam->id }}">0</span>
                        </div>

                        {{-- Riwayat sesi per kamera --}}
                        <h6>Riwayat Sesi</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered session-table" id="session-table-{{ $cam->id }}">
                                <thead class="table-light">
                                    <tr>
                                        <th>Zona</th>
                                        <th>Masuk</th>
                                        <th>Keluar</th>
                                        <th>Durasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($eventsByCamera[$cam->id] ?? [] as $ev)
                                        <tr>
                                            <td>{{ $ev['zone_name'] ?? '-' }}</td>
                                            <td>{{ $ev['start_time'] }}</td>
                                            <td>{{ $ev['end_time'] ?? '-' }}</td>
                                            <td>{{ $ev['duration'] ?? 'Aktif' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-muted text-center">Belum ada riwayat sesi</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted">Belum ada kamera di database.</p>
        @endforelse
    </div>
</div>

{{-- Modal Tambah Kamera --}}
<div class="modal fade" id="addCameraModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('cameras.storeDb') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kamera Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kamera</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RTSP URL</label>
                        <input type="text" name="rtsp_url" class="form-control" required>
                        <small class="text-muted">Contoh: rtsp://user:pass@192.168.1.10:554/Streaming/Channels/101</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const PY_API_URL = "{{ rtrim(pythonApi(''), '/') }}";
    const cameras = @json($cameras);

    // --- DataTables untuk setiap kamera ---
    @foreach ($cameras as $cam)
    $('#session-table-{{ $cam->id }}').DataTable({
        pageLength: 5,
        lengthMenu: [5, 10, 20, 50],
        ordering: true,
        searching: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ entri",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
            paginate: { first: "Pertama", last: "Terakhir", next: "›", previous: "‹" },
            zeroRecords: "Tidak ada data"
        }
    });
    @endforeach

    // --- Update people count setiap kamera ---
    function updatePeopleCount(camId) {
        fetch(`${PY_API_URL}/person_count/${camId}`)
            .then(r => r.ok ? r.json() : Promise.reject('no response'))
            .then(data => {
                const el = document.getElementById(`people-count-${camId}`);
                if (el) el.textContent = (data.count !== undefined ? data.count : 0);
            })
            .catch(err => console.debug('people_count error', camId, err));
    }

    cameras.forEach(c => {
        updatePeopleCount(c.id);
        setInterval(() => updatePeopleCount(c.id), 2000);
    });
});
</script>
@endpush
