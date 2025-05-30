@extends('layouts.dashboard')

@section('title', 'Ulasan Pelangan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/summernote/summernote-bs4.css') }}">
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
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            @if ($review)
                                <form action="{{ route('customer-review.update', $review->id) }}" method="POST">
                                    @csrf
                                    @method('put')
                                    <fieldset class="form-group">
                                        <legend class="col-form-label pt-0 text-center">&nbsp;</legend>
                                        <div class="d-flex justify-content-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input d-none" type="radio" name="rating"
                                                        id="rating{{ $i }}" value="{{ $i }}"
                                                        {{ old('rating', $review->rating) == $i ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="rating{{ $i }}">
                                                        <i class="fa-star star-icon {{ old('rating', $review->rating) >= $i ? 'fas text-warning' : 'far' }}"
                                                            data-value="{{ $i }}"
                                                            style="font-size: 2.25rem; cursor: pointer;"></i>
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>

                                        <input type="hidden" id="ratingValue" name="rating"
                                            value="{{ old('rating', $review->rating) }}">

                                        @error('rating')
                                            <div class="invalid-feedback d-block text-center mt-3">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </fieldset>

                                    <div class="form-group">
                                        <label for="review">&nbsp;</label>
                                        <textarea name="review" id="review" rows="5"
                                            class="form-control summernote-simple @error('review') is-invalid @enderror" data-placeholder="Tulis ulasan...">{!! old('review', $review->review) !!}</textarea>
                                        <div class="invalid-feedback">
                                            @error('review')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end align-items-center" style="gap: .5rem">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            @else
                                <form action="{{ route('customer-review.store') }}" method="POST">
                                    @csrf
                                    <fieldset class="form-group">
                                        <legend class="col-form-label pt-0 text-center">&nbsp;</legend>
                                        <div class="d-flex justify-content-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input d-none" type="radio" name="rating"
                                                        id="rating{{ $i }}" value="{{ $i }}"
                                                        {{ old('rating', 0) == $i ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="rating{{ $i }}">
                                                        <i class="fa-star star-icon {{ old('rating', 0) >= $i ? 'fas text-warning' : 'far' }}"
                                                            data-value="{{ $i }}"
                                                            style="font-size: 2.25rem; cursor: pointer;"></i>
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>

                                        <input type="hidden" id="ratingValue" name="rating"
                                            value="{{ old('rating', 0) }}">

                                        @error('rating')
                                            <div class="invalid-feedback d-block text-center mt-3">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </fieldset>

                                    <div class="form-group">
                                        <label for="review">&nbsp;</label>
                                        <textarea name="review" id="review" rows="5"
                                            class="form-control summernote-simple @error('review') is-invalid @enderror" data-placeholder="Tulis ulasan...">{!! old('review') !!}</textarea>
                                        <div class="invalid-feedback">
                                            @error('review')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end align-items-center" style="gap: .5rem">
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('modules/summernote/summernote-bs4.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star-icon');
            const ratingInput = document.getElementById('ratingValue');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const selectedRating = parseInt(this.getAttribute('data-value'));
                    ratingInput.value = selectedRating;

                    stars.forEach(s => {
                        const value = parseInt(s.getAttribute('data-value'));
                        if (value <= selectedRating) {
                            s.classList.remove('far');
                            s.classList.add('fas', 'text-warning');
                        } else {
                            s.classList.remove('fas', 'text-warning');
                            s.classList.add('far');
                        }
                    });
                });
            });
        });
    </script>
@endpush
