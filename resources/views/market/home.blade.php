@extends('market.layouts.public')

@section('title', 'E-Inspect | Pandan Public Market')

@section('content')
    @php
        $marketName = $settings['market_name'] ?? 'Pandan Public Market';
        $marketTagline = $settings['market_tagline'] ?? 'An organized public market committed to food safety, fair trade, and sustainable economic development within the community';
        $developerCredit = $settings['developer_credit'] ?? 'KAJS CODERS INVADER';
    @endphp

    @include('market.public.header', ['active' => 'home', 'announcementCount' => $announcements->count(), 'headerClass' => 'homepage-header'])

    <main class="hero wireframe-home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal" class="hero-seal">
            <p>Welcome To</p>
            <h1>{{ str($marketName)->upper() }}</h1>
            <p class="hero-copy">&ldquo;{{ $marketTagline }}&rdquo;</p>
            <div class="hero-actions">
                <a href="{{ route('public.roles', 'register') }}" class="button button-primary">Sign in</a>
                <a href="{{ route('public.roles', 'login') }}" class="button button-primary">Log in</a>
            </div>
        </div>
    </main>

    <footer class="public-footer homepage-footer">&copy; Copyright {{ date('Y') }}. Developed by {{ $developerCredit }}. All Rights Reserved</footer>
@endsection
