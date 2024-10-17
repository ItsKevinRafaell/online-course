@extends('layouts.backend.master')

@section('title', $series->name)

@section('content')
<div class="container-xl">
    <div class="row">
        <!-- Tab Navigation for Chapters and Points -->
        <div class="col-3 bg-light border-end vh-100 overflow-auto">
            <div class="nav flex-column p-3" id="sidebar-nav">
                @foreach ($chapters as $chapter)
                    <div class="mb-3">
                        <!-- Chapter as a Dropdown Toggle -->
                        <a class="nav-link px-3 py-2 fw-bold rounded d-flex justify-content-between align-items-center text-dark" 
                           id="v-pills-chapter-{{ $chapter->id }}-tab" 
                           data-bs-toggle="collapse" 
                           href="#collapse-chapter-{{ $chapter->id }}" 
                           role="button" 
                           aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                           aria-controls="collapse-chapter-{{ $chapter->id }}">
                            Chapter {{ $chapter->order }}: {{ $chapter->title }}
                        </a>

                        <!-- Points Dropdown -->
                        <div class="collapse {{ $loop->first ? 'show' : '' }}" id="collapse-chapter-{{ $chapter->id }}">
                            <div class="ms-3 mt-2">
                                @foreach ($chapter->points as $point)
                                    <a class="nav-link px-3 py-2 rounded mb-1 text-dark {{ $loop->first && $loop->parent->first ? 'bg-primary text-white' : '' }}" 
                                       id="v-pills-point-{{ $point->id }}-tab" 
                                       data-bs-toggle="pill" 
                                       href="#v-pills-point-{{ $point->id }}" 
                                       role="tab" 
                                       aria-controls="v-pills-point-{{ $point->id }}" 
                                       aria-selected="{{ $loop->first && $loop->parent->first ? 'true' : 'false' }}">
                                        Point {{ $point->order }}: {{ $point->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Tab Content for Material -->
        <div class="col-9">
            <div class="tab-content p-4" id="v-pills-tabContent">
                @foreach ($chapters as $chapter)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                         id="v-pills-chapter-{{ $chapter->id }}" 
                         role="tabpanel" 
                         aria-labelledby="v-pills-chapter-{{ $chapter->id }}-tab">
                        <h4>{{ $chapter->title }}</h4>
                        <p>{{ $chapter->description }}</p>
                    </div>

                    @foreach ($chapter->points as $point)
                        <div class="tab-pane fade" 
                             id="v-pills-point-{{ $point->id }}" 
                             role="tabpanel" 
                             aria-labelledby="v-pills-point-{{ $point->id }}-tab">
                            <h5>{{ $point->title }}</h5>
                            <p>{{ $point->content }}</p>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to Handle Active States -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pointLinks = document.querySelectorAll('#sidebar-nav .ms-3 .nav-link');

        // Function to clear all active states from points
        function clearActiveStates() {
            pointLinks.forEach(item => {
                item.classList.remove('bg-primary', 'text-white');
                item.classList.add('text-dark');
            });
        }

        // Listen for clicks on point links
        pointLinks.forEach(link => {
            link.addEventListener('click', function () {
                // Clear existing active states
                clearActiveStates();

                // Apply active state to the clicked point
                this.classList.add('bg-primary', 'text-white');
                this.classList.remove('text-dark');

                // Expand the parent chapter if it's collapsed
                const parentChapter = this.closest('.collapse');
                const bsCollapse = new bootstrap.Collapse(parentChapter, {
                    toggle: false
                });

                // Ensure the chapter is expanded
                if (!parentChapter.classList.contains('show')) {
                    bsCollapse.show();
                }

                // Hide all point contents and show the selected point
                const pointContents = document.querySelectorAll('.tab-pane[id^="v-pills-point-"]');
                pointContents.forEach(content => {
                    content.classList.remove('show', 'active');
                });

                const selectedPointContent = document.getElementById(this.getAttribute('href').substring(1));
                selectedPointContent.classList.add('show', 'active');
            });
        });

        // Set the first point active on page load
        if (pointLinks.length > 0) {
            clearActiveStates();
            pointLinks[0].classList.add('bg-primary', 'text-white');
            pointLinks[0].classList.remove('text-dark');

            // Also show the content of the first point
            const firstPointContent = document.getElementById(pointLinks[0].getAttribute('href').substring(1));
            firstPointContent.classList.add('show', 'active');
        }
    });
</script>
@endsection
