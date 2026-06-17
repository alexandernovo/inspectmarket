<header class="app-header position-sticky top-0 w-100 header-footer-bg2">
    <nav class="navbar navbar-expand-lg ">
        <ul class="navbar-nav">
            @if (!in_array(Route::currentRouteName(), $excludedRoutes))
                <li class="nav-item">
                    <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                        <i class="ti ti-menu-2 text-white"></i>
                    </a>
                </li>
            @endif
            @if (in_array(Route::currentRouteName(), $excludedRoutes))
                <li class="nav-item dropdown">
                    <div class="brand-logo d-flex align-items-center justify-content-between ps-0">
                        <a href="#" class="text-nowrap logo-img d-flex align-items-center gap-2">
                            <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle"
                                width="" alt="" style="width: 43px; height: 43px" />
                            <span style="font-size: 28px; letter-spacing: 4px; font-weight: 600;"
                                class="text-white title-sidebar">
                                Tibiao MDRRMO Portal
                            </span>
                        </a>
                    </div>
                    <div class="dropdown-menu dropdown-menu-start dropdown-menu-animate-up" style="min-width: 350px;"
                        aria-labelledby="dropnotif">
                        <div class="d-flex align-items-center justify-content-between py-3 px-7">
                            <h5 class="mb-0 fs-5 fw-semibold">Notifications</h5>
                            <span class="badge text-bg-primary rounded-4 px-3 py-1 lh-sm d-none" style="font-size: 13px"
                                id="notification_count"></span>
                        </div>
                        <div class="pb-3 overflow-y-auto mt-2" id="notificationDiv" style="height: 400px;">

                        </div>
                        <div class="px-4 pb-3 border-top" style="height: 35px;">

                        </div>
                    </div>
                </li>
            @endif
        </ul>
        <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
            @if (!in_array(Route::currentRouteName(), $excludedRoutes))
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                    <div class="d-flex gap align-items-center">
                        <div class="d-flex flex-column justify-content-center align-items-end border-end pe-2">
                            <p class="mb-0 fw-semibold text-white" style="font-size: 13px; line-height: 17px">
                                Tibiao MDRRMO
                                {{ auth()->user()->usertype && auth()->user()->usertype == 'ADMIN' ? 'Admin' : 'Staff' }}
                            </p>
                        </div>
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-icon-hover px-2" href="javascript:void(0)" id="drop2"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <div class=" d-flex align-items-center overflow-hidden rounded-circle" style="width: 40px; height: 40px">
                                    @if (auth()->user() && auth()->user()->profile)
                                        <img src="{{ asset(auth()->user()->profile) }}" alt=""
                                            class="w-100 h-100 object-fit-cover">
                                    @else
                                        <i class="bi bi-person-circle text-white"
                                            style="font-size: calc(30px - 2px); fill: white"></i>
                                    @endif

                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up"
                                aria-labelledby="drop2">
                                <div class="message-body">
                                    <a href="{{ route('profile_view') }}"
                                        class="d-flex align-items-center gap-2 dropdown-item">
                                        <i class="ti ti-user fs-6"></i>
                                        <p class="mb-0 fs-3">My Profile</p>
                                    </a>
                                    <a class="btn btn-prime mx-3 mt-2 d-block logout-btn">Logout</a>
                                </div>
                            </div>
                        </li>
                    </div>
                </ul>
            @else
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end gap-4">
                    <li class="nav-item dropdown">
                        <a href="{{ route('home') }}"
                            class="nav-link nav-icon-hover px-2 cursor-pointer text-white fw-semibold"
                            style="font-size: 13px">
                            <i class="bi bi-house-fill me-1"></i>
                            Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="{{ route('contact') }}"
                            class="nav-link nav-icon-hover px-2 cursor-pointer text-white fw-semibold"
                            style="font-size: 13px">
                            <i class="bi bi-telephone-fill me-1"></i>
                            Contact
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <div class="d-flex gap align-items-center">
                            <div class="d-flex flex-column justify-content-center align-items-end border-end pe-2">
                                <p class="mb-0 fw-semibold text-white" style="font-size: 13px; line-height: 17px">
                                    Admin
                                </p>
                            </div>
                            <a class="nav-link nav-icon-hover px-2 cursor-pointer" data-bs-toggle="offcanvas"
                                data-bs-target="#staticBackdrop">
                                <div class=" d-flex align-items-center" style="width: 40px; height: 40px">
                                    <i class="bi bi-person-circle text-white"
                                        style="font-size: calc(30px - 2px); fill: white"></i>
                                </div>
                            </a>
                        </div>
                    </li>
                </ul>
            @endif
        </div>
    </nav>
</header>
