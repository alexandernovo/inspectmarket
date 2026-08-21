@extends('market.layouts.portal')

@section('content')
    <section class="administrator-settings-page">
        <header class="administrator-page-heading">
            <i class="bi bi-gear-fill"></i>
            <div><h1>SETTINGS</h1><p>Dashboard | Settings</p></div>
        </header>

        <form action="{{ route('administrator.settings.update') }}" method="POST" class="administrator-settings-card">
            @csrf
            @method('PUT')

            <div class="administrator-settings-hero">
                <i class="bi bi-sliders"></i>
                <div>
                    <h2>SYSTEM SETTINGS</h2>
                    <p>PUBLIC MARKET PANDAN, ANTIQUE</p>
                </div>
            </div>

            <div class="administrator-settings-grid">
                <label>Market Name
                    <span><i class="bi bi-shop"></i><input name="market_name" value="{{ old('market_name', $settings['market_name'] ?? 'Pandan Public Market') }}" required></span>
                    @error('market_name')<small>{{ $message }}</small>@enderror
                </label>
                <label>Market Address
                    <span><i class="bi bi-geo-alt-fill"></i><input name="market_address" value="{{ old('market_address', $settings['market_address'] ?? 'Pandan, Antique') }}" required></span>
                    @error('market_address')<small>{{ $message }}</small>@enderror
                </label>
                <label>Market Email
                    <span><i class="bi bi-envelope-fill"></i><input type="email" name="market_email" value="{{ old('market_email', $settings['market_email'] ?? '') }}"></span>
                    @error('market_email')<small>{{ $message }}</small>@enderror
                </label>
                <label>Market Phone
                    <span><i class="bi bi-telephone-fill"></i><input name="market_phone" value="{{ old('market_phone', $settings['market_phone'] ?? '') }}"></span>
                    @error('market_phone')<small>{{ $message }}</small>@enderror
                </label>
                <label>Cash Ticket Value
                    <span><i class="bi bi-ticket-perforated-fill"></i><input type="number" step="0.01" min="0" name="cash_ticket_value" value="{{ old('cash_ticket_value', $settings['cash_ticket_value'] ?? 100) }}" required></span>
                    @error('cash_ticket_value')<small>{{ $message }}</small>@enderror
                </label>
                <label>Minimum Inspection Lead Days
                    <span><i class="bi bi-calendar-check-fill"></i><input type="number" min="0" max="60" name="inspection_lead_days" value="{{ old('inspection_lead_days', $settings['inspection_lead_days'] ?? 1) }}" required></span>
                    @error('inspection_lead_days')<small>{{ $message }}</small>@enderror
                </label>
            </div>

            <footer class="administrator-settings-actions">
                <button class="button button-primary">Save Settings</button>
            </footer>
        </form>
    </section>
@endsection
