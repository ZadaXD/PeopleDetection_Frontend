@extends('layouts.sneat')

@section('content')
    <div class="container">
        <h4 class="fw-bold py-3 mb-4">Dashboard Kamera</h4>

        {{-- Tombol tambah kamera dan search --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn rounded-pill btn-primary" data-bs-toggle="modal" data-bs-target="#addCameraModal">
                <i class="bx bx-cctv"></i> Tambah Kamera
            </button>

            {{-- Input Pencarian Kamera --}}
            <div class="d-flex align-items-center">
                <input type="text" id="searchCamera" class="form-control rounded-pill" placeholder="Cari kamera..."
                    style="max-width: 250px;">
            </div>
        </div>

        {{-- Daftar Kamera --}}
        <div class="row" id="cameraContainer">
            @forelse($cameras as $cam)
                <div class="col-md-6 camera-card" data-name="{{ strtolower($cam->name) }}">
                    <div class="card mb-4 shadow">
                        {{-- Stream video dari Python (MJPEG) --}}
                        <img src="{{ pythonApi('video_feed/' . $cam->id) }}" class="card-img-top" alt="{{ $cam->name }}"
                            style="height:300px; object-fit:cover;">

                        <div class="card-body">
                            <h5 class="card-title">{{ $cam->name }}</h5>
                            <p class="card-text"><small class="text-muted">{{ $cam->rtsp_url }}</small></p>

                            <div class="d-flex justify-content-between mb-3">
                                {{-- Start/Stop recording --}}
                                @if (!$cam->is_recording)
                                    <form method="POST" action="{{ route('cameras.start', $cam->id) }}">
                                        @csrf
                                        <button class="btn btn-success rounded-pill">
                                            <i class="bx bx-caret-right-circle"></i> Start Recording
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('cameras.stop', $cam->id) }}">
                                        @csrf
                                        <button class="btn btn-danger rounded-pill">
                                            <i class="bx bx-stop-circle"></i> Stop Recording
                                        </button>
                                    </form>
                                @endif

                                {{-- Edit zone --}}
                                <a href="{{ route('cameras.editZone', $cam->id) }}" class="btn btn-warning rounded-pill">
                                    <i class="bx bx-edit"></i> Edit Zone
                                </a>

                                {{-- Hapus kamera --}}
                                <form method="POST" action="{{ route('cameras.destroy', $cam->id) }}"
                                    class="delete-camera-form" data-name="{{ $cam->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger rounded-pill">
                                        <i class="bx bx-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>

                            {{-- People Count --}}
                            <div class="mb-3">
                                <strong>Total Orang dalam Zona:</strong>
                                <span id="people-count-{{ $cam->id }}">0</span>
                            </div>

                            {{-- Riwayat sesi per kamera --}}
                            <h6>Riwayat Sesi</h6>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-sm table-striped session-table"
                                    id="session-table-{{ $cam->id }}">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Zona</th>
                                            <th>Masuk</th>
                                            <th>Keluar</th>
                                            <th>Durasi</th>
                                        </tr>
                                    </thead>
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
                            <small class="text-muted">Contoh:
                                rtsp://user:pass@192.168.1.10:554/Streaming/Channels/101</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                            <i class="bx bx-x"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill">
                            <i class="bx bx-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cameras = @json($cameras);

            // === FITUR SEARCH KAMERA ===
            const searchInput = document.getElementById('searchCamera');
            const cameraCards = document.querySelectorAll('.camera-card');

            searchInput.addEventListener('keyup', () => {
                const keyword = searchInput.value.toLowerCase();
                cameraCards.forEach(card => {
                    const name = card.dataset.name;
                    card.style.display = name.includes(keyword) ? '' : 'none';
                });
            });

            // === LOAD EVENT SESSIONS VIA AJAX ===
            @foreach ($cameras as $cam)
                let table{{ $cam->id }} = $('#session-table-{{ $cam->id }}').DataTable({
                    ajax: {
                        url: "{{ route('dashboard.events') }}",
                        dataSrc: function(json) {
                            if (!json.success) return [];
                            return json.data.filter(ev => ev.camera_id == {{ $cam->id }});
                        }
                    },
                    columns: [{
                            data: 'zone_name',
                            defaultContent: '-'
                        },
                        {
                            data: 'start_time',
                            defaultContent: '-'
                        },
                        {
                            data: 'end_time',
                            render: function(data, type, row) {
                                return row.is_active ?
                                    '<span class="badge bg-success">Sesi Aktif</span>' :
                                    (data ?? '-');
                            }
                        },
                        {
                            data: 'duration',
                            render: function(data, type, row) {
                                if (row.is_active) {
                                    return `<span class="realtime-timer" data-start="${row.start_ts}">00:00:00</span>`;
                                }
                                return data ?? '-';
                            }
                        }
                    ],
                    pageLength: 1,
                    lengthMenu: [1, 5, 10, 20, 50],
                    ordering: true,
                    searching: true,
                    dom: 'Blfrtip',
                    buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="bx bxs-file-export"></i> Export Excel',
                        className: 'btn btn-sm btn-outline-success mb-2',
                        title: 'Riwayat Sesi - {{ $cam->name }}',
                        exportOptions: {
                            columns: ':visible'
                        }
                    }],
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ entri",
                        info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "›",
                            previous: "‹"
                        },
                        zeroRecords: "Tidak ada data"
                    },
                    drawCallback: function() {
                        updateDurations();
                    }
                });

                // reload tiap detik
                setInterval(() => {
                    table{{ $cam->id }}.ajax.reload(null, false);
                }, 1000);
            @endforeach

            // === UPDATE PEOPLE COUNT (REALTIME) ===
            function updatePeopleCount() {
                fetch("{{ route('dashboard.people') }}")
                    .then(res => res.json())
                    .then(json => {
                        if (!json.success) return;
                        let counts = json.people_counts || {};
                        let total = json.total_people || 0;

                        cameras.forEach(cam => {
                            const el = document.getElementById('people-count-' + cam.id);
                            if (el) el.textContent = counts[cam.id] || 0;
                        });
                        const totalEl = document.getElementById('people-count-total');
                        if (totalEl) totalEl.textContent = total;
                    })
                    .catch(err => console.error("updatePeopleCount error:", err));
            }

            updatePeopleCount();
            setInterval(updatePeopleCount, 2000);

            // === HAPUS KAMERA ===
            document.querySelectorAll('.delete-camera-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const camName = this.dataset.name;
                    Swal.fire({
                        title: 'Apakah kamu yakin?',
                        html: `Kamera <b>${camName}</b> akan dihapus permanen.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bx bx-trash"></i> Ya, Hapus',
                        cancelButtonText: '<i class="bx bx-x"></i> Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit();
                        }
                    });
                });
            });

            // === FLASH SUCCESS ===
            @if (session('status'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: "{{ session('status') }}",
                    confirmButtonColor: '#28a745'
                });
            @endif
        });

        // === UPDATE DURASI REALTIME ===
        function pad(num) {
            return num.toString().padStart(2, '0');
        }

        function updateDurations() {
            document.querySelectorAll('.realtime-timer').forEach(el => {
                let start = parseInt(el.dataset.start, 10);
                if (!start) return;
                const now = Math.floor(Date.now() / 1000);
                let diff = now - start;
                if (diff < 0) diff = 0;

                const h = pad(Math.floor(diff / 3600));
                const m = pad(Math.floor((diff % 3600) / 60));
                const s = pad(diff % 60);
                el.textContent = `${h}:${m}:${s}`;
            });
        }

        setInterval(updateDurations, 1000);
    </script>
@endpush
