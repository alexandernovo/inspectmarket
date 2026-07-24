@extends('market.layouts.public')

@section('title', 'E-Inspect | Pandan Public Market')

@section('content')
    @include('market.public.header', ['active' => 'home', 'announcementCount' => $announcements->count(), 'headerClass' => 'homepage-header'])

    <main class="hero wireframe-home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <img src="{{ asset('assets/einspect/HOMEPAGE/Logo.png') }}" alt="Municipality of Pandan seal" class="hero-seal">
            <p>Welcome To</p>
            <h1>PANDAN PUBLIC MARKET</h1>
            <p class="hero-copy">&ldquo;An organized public market committed to food safety, fair trade, and sustainable<br>economic development within the community&rdquo;</p>
            <div class="hero-actions">
                <a href="{{ route('public.roles', 'register') }}" class="button button-primary">Sign in</a>
                <a href="{{ route('public.roles', 'login') }}" class="button button-primary">Log in</a>
            </div>
        </div>
    </main>

    <footer class="public-footer homepage-footer">&copy; Copyright {{ date('Y') }}. Developed by KAJS CODERS INVADER. All Rights Reserved</footer>
@endsection
