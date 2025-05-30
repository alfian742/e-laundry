@extends('layouts.dashboard')

@section('title', 'Ulasan Pelanggan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/select2/dist/css/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/datatables/dataTables.min.css') }}">

    <style>
        .select2-container {
            display: inline-block !important;
            width: auto !important;
            vertical-align: middle;
        }
    </style>
@endpush

@section('main')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>@yield('title')</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="{{ url('/dashboard') }}">Dashboard</a></div>
                    <div class="breadcrumb-item">@yield('title')</div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class=" d-flex flex-wrap justify-content-center justify-content-sm-between align-items-center mb-4"
                                style="gap: .5rem">
                                <form method="GET" action="{{ route('customer-review.index') }}" style="width: 27.5rem">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light">Saring</span>
                                        </div>

                                        {{-- Dropdown Status Baca --}}
                                        <label class="d-none" for="is_read">&nbsp;</label>
                                        <select class="custom-select select2" name="is_read" id="is_read">
                                            <option value="all" {{ $selectedIsRead == 'all' ? 'selected' : '' }}>Semua
                                                Ulasan</option>
                                            <option value="0" {{ $selectedIsRead == 0 ? 'selected' : '' }}>Belum Dibaca
                                            </option>
                                            <option value="1" {{ $selectedIsRead == 1 ? 'selected' : '' }}>Sudah Dibaca
                                            </option>
                                        </select>

                                        {{-- Dropdown Rating --}}
                                        <label class="d-none" for="rating">&nbsp;</label>
                                        <select class="custom-select select2-rating" name="rating" id="rating">
                                            <option value="all" {{ $selectedRating == 'all' ? 'selected' : '' }}>Semua
                                                Rating</option>
                                            @for ($i = 1; $i <= 5; $i++)
                                                <option value="{{ $i }}"
                                                    {{ (int) $selectedRating === $i ? 'selected' : '' }}>
                                                    {!! str_repeat('★', $i) . str_repeat('☆', 5 - $i) !!}
                                                </option>
                                            @endfor
                                        </select>

                                        {{-- Tombol Submit --}}
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Tampilkan</button>
                                        </div>
                                    </div>
                                </form>

                                @if (!$isEmployee)
                                    <a href="{{ url('/customer-review/mark-all-as-read') }}"
                                        class="btn btn-outline-primary ml-auto"
                                        title="Tandai semua ulasan sebagai sudah dibaca">Semua Sudah Dibaca</a>
                                @endif
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-striped table-nowrap table-align-middle" id="table-1">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Dibuat Pada</th>
                                            <th>Nama Pelanggan</th>
                                            <th>Rating</th>
                                            <th>Ulasan</th>
                                            @if (!$isEmployee)
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($customerReviews as $review)
                                            @php
                                                $customer = $review?->reviewingCustomer;
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $review->review_at ? carbon_format_date($review->review_at, 'datetime') . " {$zone}" : '-' }}
                                                </td>
                                                <td>
                                                    @if ($customer)
                                                        <a href="{{ url("/customer/{$customer->id}") }}">
                                                            {{ $customer->fullname ?? 'N/A' }}
                                                        </a>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $rating = $review->rating;
                                                    @endphp

                                                    <div class="d-flex" style="gap: .5rem">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= $rating)
                                                                <i class="fas fa-star text-warning"></i>
                                                                {{-- bintang penuh --}}
                                                            @else
                                                                <i class="far fa-star"></i> {{-- bintang kosong --}}
                                                            @endif
                                                        @endfor
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-wrap-overflow">
                                                        {!! $review->review ?? '-' !!}
                                                    </div>
                                                </td>
                                                @if (!$isEmployee)
                                                    <td class="text-center">
                                                        @if ($review->is_read)
                                                            <span class="badge badge-primary">Sudah Dibaca</span>
                                                        @else
                                                            <span class="badge badge-warning">Belum Dibaca</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center" style="gap: .5rem">
                                                            <form
                                                                action="{{ route('customer-review.destroy', $review->id) }}"
                                                                method="POST" id="delete-form-{{ $review->id }}"
                                                                class="d-inline">
                                                                @csrf
                                                                @method('delete')
                                                                <button type="submit" class="btn btn-danger btn-delete"
                                                                    data-toggle="tooltip" title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/datatables/dataTables.min.js') }}"></script>
    <script src="{{ asset('js/page/modules-datatables.js') }}"></script>
    <script src="{{ asset('modules/select2/dist/js/select2.full.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Set default values dari server, kalau tidak ada gunakan 'all'
            let defaultIsRead = "{{ $selectedIsRead ?? 'all' }}";
            let defaultRating = "{{ $selectedRating ?? 'all' }}";

            // Jika variabel server kosong, pakai 'all'
            if (!defaultIsRead) defaultIsRead = 'all';
            if (!defaultRating) defaultRating = 'all';

            $('#is_read').val(defaultIsRead).select2({
                theme: 'bootstrap4'
            });

            $('#rating').val(defaultRating).select2({
                theme: 'bootstrap4',
                templateResult: formatStars,
                templateSelection: formatStars,
                escapeMarkup: function(m) {
                    return m;
                }
            });

            function formatStars(state) {
                if (!state.id || state.id === 'all') {
                    return state.text;
                }

                var rating = parseInt(state.id);
                var stars = '';

                for (var i = 1; i <= 5; i++) {
                    stars += i <= rating ? '<i class="fas fa-star text-warning"></i>' :
                        '<i class="far fa-star text-muted"></i>';
                }

                return stars;
            }
        });
    </script>

    @if (!$customerReviews->isEmpty() && !$isEmployee)
        <script>
            $(document).ready(function() {
                // Gunakan delegasi untuk tombol hapus
                $(document).on('click', '.btn-delete', function(e) {
                    e.preventDefault();

                    const formId = $(this).closest('form').attr('id');

                    swal({
                        title: 'Hapus Ulasan',
                        text: 'Apakah Anda yakin ingin menghapus ulasan ini?',
                        icon: 'warning',
                        buttons: {
                            cancel: 'Batal',
                            confirm: {
                                text: 'Ya, Hapus!',
                                value: true,
                                className: 'btn-danger',
                            }
                        },
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('#' + formId).submit();
                        }
                    });
                });
            });
        </script>
    @endif
@endpush
