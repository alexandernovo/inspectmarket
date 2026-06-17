<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digital Incident Reporting System</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logoico.png') }}" />
    <link rel="stylesheet" href="{{ asset('template_assets/css/icons/tabler-icons/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('template_assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/font/bootstrap-icons.css') }}">
</head>

<body>
    <div id="main-wrapper">
        <div
            class="position-relative overflow-hidden min-vh-100 w-100 d-flex align-items-center justify-content-center">
            <div class="d-flex align-items-center justify-content-center w-100">
                <div class="row justify-content-center w-100">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <img src="{{ asset('assets/images/404.svg') }}" alt="modernize-img" class="img-fluid"
                                width="500">
                            <h1 class="fw-semibold mb-7 fs-9">Opps!!!</h1>
                            <h4 class="fw-semibold mb-7">This page you are looking for could not be found.</h4>
                            <a class="btn btn-primary" href="{{ route('dashboard') }}" role="button">Go Back to Home</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/js/bootstrap5.3.js') }}"></script>
</body>

</html>
