@php
    $excludedRoutes = ['home', 'login', 'signup', 'contact', 'forgot.password'];
@endphp

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TIBIAO MDRRMO Portal</title>

    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logo1.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/twitterbootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datatablesbootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('template_assets/css/icons/tabler-icons/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('template_assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/js/leaflet/leaflet.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/js/leaflet/geocoder.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style2.css') }}" />
</head>

<body class="position-relative" style="visibility: hidden">
    <div class="">
        <div class="page-wrapper flex-1" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
            data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
            @if (!in_array(Route::currentRouteName(), $excludedRoutes))
                @include('components.sidebar')
            @endif
            @if (!in_array(Route::currentRouteName(), $excludedRoutes))
                <div class="body-wrapper vh-100 d-flex flex-column justify-content-between">
                    @include('components.header2')
                    <div class="px-3 pb-3 mt-3 flex-1">
                        @yield('content')
                    </div>
                    @include('components.footer2')
                </div>
            @else
                <div class="vh-100 d-flex flex-column justify-content-between">
                    @include('components.header')
                    @yield('content')
                    @include('components.footer')
                </div>
            @endif
        </div>
    </div>
    <div class="toast-container position-fixed z-3 pb-2 pe-2" id="toast-container-global" style="right: 0; bottom: 0">
    </div>
    <div class="modal fade" id="fileModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">File Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe id="filePreview" src="" style="width:100%; height:80vh;" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    @include('layout/components/monthlyreport')
    <script src="{{ asset('assets/js/loader.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/datatables.js') }}"></script>
    <script src="{{ asset('assets/js/datatablesbootstrap.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap5.3.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('template_assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('template_assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('template_assets/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('template_assets/js/app.min.js') }}"></script>
    <script src="{{ asset('template_assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('template_assets/libs/simplebar/dist/simplebar.js') }}"></script>
    <script src="{{ asset('assets/js/select2.js') }}"></script>
    <script src="{{ asset('assets/js/jspdf.umd.min.js') }}"></script>
    <script src="{{ asset('assets/js/html2canvas.min.js') }}"></script>
    <script src="{{ asset('assets/js/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('assets/js/socket.io.js') }}"></script>
    <script src="{{ asset('assets/js/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('assets/js/leaflet/leaflet-src.js') }}"></script>
    <script src="{{ asset('assets/js/leaflet/geocoder.js') }}"></script>
    <script src="{{ asset('assets/js/leaflet/googlemutant.js') }}"></script>
    <script src="{{ asset('assets/js/html2pdf.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script>
        let isStaff = {{ auth()->check() && auth()->user()->usertype === 'STAFF' ? 'true' : 'false' }};
    </script>
    @include('layout.js.layoutjs')
    @include('layout/js/monthlyreport')
    @yield('js')
</body>

</html>
