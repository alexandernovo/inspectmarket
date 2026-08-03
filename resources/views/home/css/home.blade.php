<style>
    .bg-home {
        background: url("{{ asset('assets/images/background.png') }}");
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
    }

    .glass-card {
        background: rgba(45, 45, 45, 0.35);
        backdrop-filter: blur(2px);
        -webkit-backdrop-filter: blur(2px);

        border: 1px solid rgba(255, 255, 255, 0.15);

        border-radius: 18px;

        box-shadow:
            0 8px 32px rgba(0, 0, 0, 0.45);

        overflow: hidden;
    }
</style>
