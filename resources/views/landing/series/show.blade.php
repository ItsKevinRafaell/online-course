@extends('layouts.frontend.master')

@section('title', $series->name)

@section('content')
<div class="container-xl">
    <div class="row">
        <div class="col-12">
            <x-card.card-description>
                <div class="row">
                    <div class="col-7">
                        <div class="ribbon bg-yellow">Rp. {{ number_format($series->price) }}</div>
                        <h3 class="card-title">{{ $series->name }}</h3>
                        <p class="card-text">{{ $series->description }}</p>
                        <x-utilities.item 
                            date="{{ $series->created_at->format('d F Y') }}"
                            level="{{ $series->level }}"
                            status="{{ $series->status == 1 ? 'Selesai' : 'Pengembangan' }}"
                            episode="{{ $chapters->count() }} Bab"
                            members="{{ $members }} Peserta" 
                        />
                        <div class="mt-2">
                            @if ($purchased)
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-user-check mr-1"></i>
                                    Pembeli : {{ Auth::user()->name }} ({{ Auth::user()->email }}) —
                                    {{ Carbon\Carbon::parse($transaction[0]->date_transfer)->format('d F Y') }}
                                </div>
                            @else
                                <form action="{{ route('carts.store', $series->slug) }}" method="POST">
                                    @csrf
                                    <x-button.button-save icon="shopping-cart" title="Belajar Sekarang" 
                                        class="btn btn-outline bg-blue text-white" />
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="col-5">
                        <img src="{{ $series->cover }}" class="img-fluid" />
                    </div>
                </div>
            </x-card.card-description>
        </div>

        <div class="col-12">
            <x-card.card title="Materi - {{ $series->name }}">
                <div class="list-group list-group-flush">
                    @foreach ($chapters as $chapter)
                        <div class="list-group-item">
                            <p class="font-weight-bold fs-3">Bab {{ $chapter->order }}: {{ $chapter->title }}</p>
                            <ul>
                                @foreach ($chapter->points->sortBy('order') as $point)
                                    <li>{{ $point->order }}: {{ $point->title }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </x-card.card>
        </div>
    </div>
</div>
@endsection
