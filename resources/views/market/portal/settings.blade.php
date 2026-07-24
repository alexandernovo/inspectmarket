@extends('market.layouts.portal')

@section('content')
    <section class="panel settings-panel">
        <div class="panel-heading"><div><span class="eyebrow">Administrator</span><h2>System Settings</h2></div></div>
        <form action="{{ route('administrator.settings.update') }}" method="POST" class="profile-form">
            @csrf @method('PUT')
            <div class="form-grid">
                <label>Market name<input name="market_name" value="{{ old('market_name', $settings['market_name'] ?? 'Pandan Public Market') }}" required></label>
                <label>Market address<input name="market_address" value="{{ old('market_address', $settings['market_address'] ?? 'Pandan, Antique') }}" required></label>
                <label>Market email<input type="email" name="market_email" value="{{ old('market_email', $settings['market_email'] ?? '') }}"></label>
                <label>Market phone<input name="market_phone" value="{{ old('market_phone', $settings['market_phone'] ?? '') }}"></label>
                <label>Cash-ticket value<input type="number" step="0.01" min="0" name="cash_ticket_value" value="{{ old('cash_ticket_value', $settings['cash_ticket_value'] ?? 100) }}" required></label>
                <label>Minimum inspection lead days<input type="number" min="0" max="60" name="inspection_lead_days" value="{{ old('inspection_lead_days', $settings['inspection_lead_days'] ?? 1) }}" required></label>
            </div>
            <button class="button button-primary">Save settings</button>
        </form>
    </section>
@endsection
