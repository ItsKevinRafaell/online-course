@extends('layouts.backend.master')

@section('title', 'Create or Edit Materi')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Form Create/Edit Materi --}}
        <div class="col-md-8">
            <x-card.card title="Create or Edit Materi">
                <form action="{{ route('materi.store', $series->slug) }}" method="POST">
                    @csrf

                    {{-- Pesan Error --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Pilih Chapter --}}
                    <div class="mb-3">
                        <label for="chapter-select" class="form-label">Select Chapter</label>
                        <select id="chapter-select" name="chapter_id" class="form-control">
                            <option value="">-- Select Chapter --</option>
                            @foreach ($series->chapters as $chapter)
                                <option value="{{ $chapter->id }}" 
                                    {{ old('chapter_id') == $chapter->id ? 'selected' : '' }}>
                                    {{ $chapter->title }}
                                </option>
                            @endforeach
                            <option value="new" {{ old('chapter_id') == 'new' ? 'selected' : '' }}>
                                -- Create New Chapter --
                            </option>
                        </select>
                    </div>

                    {{-- Input Chapter Baru --}}
                    <div id="new-chapter" style="display: none;">
                        <x-form.input type="text" title="Chapter Title" name="new_chapter_title" 
                            value="{{ old('new_chapter_title') }}" placeholder="Enter new chapter title" />
                        <x-form.input type="number" title="Chapter Order" name="new_chapter_order" 
                            value="{{ old('new_chapter_order') }}" placeholder="Enter chapter order (optional)" />
                    </div>

                    {{-- Edit Chapter --}}
                    <div id="edit-chapter" style="display: none;">
                        <x-form.input type="text" id="edit-chapter-title" title="Edit Chapter Title" 
                            name="edit_chapter_title" value="" placeholder="Edit chapter title" />
                        <x-form.input type="number" id="edit-chapter-order" title="Edit Chapter Order" 
                            name="edit_chapter_order" value="" placeholder="Edit chapter order" />
                    </div>

                    {{-- Pilih Point --}}
                    <div class="mb-3">
                        <label for="point-select" class="form-label">Select Point</label>
                        <select id="point-select" class="form-control">
                            <option value="">-- Select Point --</option>
                        </select>
                    </div>

                    {{-- Form Point --}}
                    <div id="point-form">
                    {{-- Placeholder untuk points yang ada atau baru --}}
                    <input type="hidden" name="points[0][id]" id="point-id" value="{{ old('points.0.id') }}">

                    <div class="point-item mb-4" data-index="0">
                        <x-form.input type="text" title="Point Title" 
                            name="points[0][title]" id="point-title-0" 
                            value="{{ old('points.0.title') }}" placeholder="Enter point title" />

                        <x-form.textarea title="Point Content" 
                            name="points[0][content]" id="point-content-0" 
                            placeholder="Enter point content">{{ old('points.0.content') }}</x-form.textarea>

                        <x-form.input type="number" title="Point Order" 
                            name="points[0][order]" id="point-order-0" 
                            value="{{ old('points.0.order') }}" placeholder="Enter point order (optional)" />

                        {{-- Remove Button --}}
                        <button type="button" class="btn btn-danger remove-point">
                            <i class="fa fa-trash"></i> Remove Point
                        </button>
                    </div>
                </div>

                {{-- Add New Point Button --}}
                <button type="button" id="add-point" class="btn btn-success mb-3">
                    <i class="fa fa-plus"></i> Add New Point
                </button>


                    {{-- Delete Chapter --}}
                    <label>
                        <input type="checkbox" id="delete-chapter" name="delete_chapter" value="">
                        Delete this Chapter
                    </label>

                    {{-- Save Button --}}
                    <x-button.button-save title="Save Materi" icon="save" class="btn btn-primary" type="submit" />
                </form>
            </x-card.card>
        </div>

        {{-- Sidebar untuk Semua Chapter dan Points --}}
        <div class="col-md-4">
            @foreach ($series->chapters->sortBy('order') as $chapter)
                <div class="mb-4">
                    <h5>Chapter {{ $chapter->order }}: {{ $chapter->title }}</h5>
                    <ul>
                        @foreach ($chapter->points->sortBy([['order', 'asc'], ['created_at', 'asc']]) as $point)
                            <li>
                                <strong>Point {{ $point->order }}:</strong> {{ $point->title }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let pointIndex = 1;

        const addPointButton = document.getElementById('add-point');
        const pointForm = document.getElementById('point-form');
        const chapterSelect = document.getElementById('chapter-select');
        const newChapterInput = document.getElementById('new-chapter');
        const editChapterInput = document.getElementById('edit-chapter');
        const pointSelect = document.getElementById('point-select');

        const pointsData = @json($series->chapters->mapWithKeys(fn($chapter) => [
            $chapter->id => $chapter->points
        ]));

        chapterSelect.addEventListener('change', function () {
            const selectedValue = this.value;

            if (selectedValue === 'new') {
                newChapterInput.style.display = 'block';
                editChapterInput.style.display = 'none';
                pointSelect.innerHTML = '<option value="">-- Select Point --</option>';
            } else {
                newChapterInput.style.display = 'none';
                editChapterInput.style.display = 'block';
                document.getElementById('edit-chapter-title').value = 
                    chapterSelect.options[chapterSelect.selectedIndex].text;

                const points = pointsData[selectedValue] || [];
                pointSelect.innerHTML = '<option value="">-- Select Point --</option>';
                points.forEach(point => {
                    pointSelect.innerHTML += `<option value="${point.id}">${point.title}</option>`;
                });
            }
        });

        pointSelect.addEventListener('change', function () {
            const pointId = this.value;
            const chapterId = chapterSelect.value;
            const points = pointsData[chapterId] || [];

            const selectedPoint = points.find(point => point.id == pointId);

            if (selectedPoint) {
                document.getElementById('point-id').value = selectedPoint.id;
                document.getElementById('point-title-0').value = selectedPoint.title;
                document.getElementById('point-content-0').value = selectedPoint.content;
                document.getElementById('point-order-0').value = selectedPoint.order;
            } else {
                document.getElementById('point-id').value = '';
                document.getElementById('point-title-0').value = '';
                document.getElementById('point-content-0').value = '';
                document.getElementById('point-order-0').value = '';
            }
        });

         addPointButton.addEventListener('click', function () {
        const newPointHtml = `
            <div class="point-item mb-4" data-index="${pointIndex}">
                        <x-form.input type="text" title="Point Title" 
                            name="points[${pointIndex}][title]" placeholder="Enter point title" value="{{ old('points.${pointIndex}.title') }}}" />

                        <x-form.textarea title="Point Content" 
                            name="points[${pointIndex}][content]" placeholder="Enter point content" value="{{ old('points.${pointIndex}.content') }}"></x-form.textarea>

                        <x-form.input type="number" title="Point Order" 
                            name="points[${pointIndex}][order]" placeholder="Enter point order" value="{{ old('points.${pointIndex}.order') }}"/>
                    </div>
        `;

        // Tambahkan point baru ke dalam form
            pointForm.insertAdjacentHTML('beforeend', newPointHtml);
            pointIndex++;
        });

        // Event delegation untuk menangani klik tombol Remove Point
        pointForm.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-point')) {
                const pointItem = e.target.closest('.point-item');
                pointItem.remove();
            }
        });
    });
</script>
@endsection
