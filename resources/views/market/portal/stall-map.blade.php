@extends('market.layouts.portal')

@section('content')
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">Pandan Public Market</span><h2>Stall Map</h2></div><span class="legend"><i class="available"></i> Available <i class="occupied"></i> Occupied</span></div>
        <div class="market-map">
            @foreach (['FISH', 'PORK', 'POULTRY', 'BEEF', 'MIXED'] as $section)
                <section class="market-section market-section-{{ strtolower($section) }}">
                    <h3>{{ $section }} SECTION</h3>
                    <div class="stall-grid">
                        @forelse ($stalls->get($section, collect()) as $stall)
                            @if ($canEdit ?? false)
                                <button type="button" class="stall-button {{ strtolower($stall->status) }}" data-open-dialog="stallDialog{{ $stall->id }}" title="{{ $section }} stall {{ $stall->stall_number }} — {{ $stall->status }}">{{ $stall->stall_number }}</button>
                            @else
                                <span class="{{ strtolower($stall->status) }}" title="{{ $section }} stall {{ $stall->stall_number }} — {{ $stall->status }}">{{ $stall->stall_number }}</span>
                            @endif
                        @empty
                            <p>No stalls configured.</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>
    </section>
    @if ($canEdit ?? false)
        @foreach ($stalls->flatten() as $stall)
            <dialog id="stallDialog{{ $stall->id }}" class="market-dialog compact-dialog">
                                        <form action="{{ route(auth()->user()->isRole('ADMINISTRATOR') ? 'administrator.stall-map.update' : 'treasurer.stall-map.update', $stall) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="dialog-heading"><div><span>{{ $stall->section }} Section</span><h2>Stall #{{ $stall->stall_number }}</h2></div><button type="button" data-close-dialog>×</button></div>
                    <div class="form-grid dialog-form">
                        <label>Status<select name="status"><option @selected($stall->status === 'AVAILABLE')>AVAILABLE</option><option @selected($stall->status === 'OCCUPIED')>OCCUPIED</option><option @selected($stall->status === 'MAINTENANCE')>MAINTENANCE</option></select></label>
                        <label>Monthly rate<input type="number" step="0.01" min="0" name="monthly_rate" value="{{ $stall->monthly_rate }}" required></label>
                    </div>
                    <div class="dialog-actions"><button type="button" class="button button-muted" data-close-dialog>Cancel</button><button class="button button-primary">Save stall</button></div>
                </form>
            </dialog>
        @endforeach
    @endif
@endsection
