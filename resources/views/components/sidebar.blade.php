<aside class="left-sidebar bg-prime">
    <div class="h-100">
        <div class="mt-3 mb-4">
            <div class="d-flex justify-content-center gap-2 align-items-center mb-2">
                <img src="{{ asset('assets/images/logo2.png') }}" class="bg-white rounded-circle" width=""
                    alt="" style="width: 78px; height: 78px" />
                <img src="{{ asset('assets/images/logo1.png') }}" class="bg-white rounded-circle" width=""
                    alt="" style="width: 78px; height: 78px" />
            </div>
            <p class="mb-0 text-center text-white fw-semibold" style="font-size: 17px;">TIBIAO MDRRMO PORTAL</p>
        </div>
        <nav class="sidebar-nav scroll-sidebar mt-1 position-relative pb-3 h-100">
            <ul id="sidebarnav">
                <li class="sidebar-item mb-1">
                    <a class="sidebar-link" href="{{ route('dashboard') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-microsoft"></i>
                        </span>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>
                <hr class="border-top border-white">
                <span class="hide-menu ms-2 text-white fw-semibold" style="font-size: 16px">DOCUMENT</span>
                <li class="sidebar-item mb-1 mt-1">
                    <a class="sidebar-link" href="{{ route('incidentreport_view') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-journals"></i>
                        </span>
                        <span class="hide-menu d-flex position-relative" id="incidentCountId">
                            Incident Report
                        </span>
                    </a>
                </li>
                <li class="sidebar-item mb-1">
                    <a class="sidebar-link" href="{{ route('situationalreport_view') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-journals"></i>
                        </span>
                        <span class="hide-menu d-flex position-relative" id="situationalCountId">
                            Situational Report
                        </span>
                    </a>
                </li>

                <li class="sidebar-item mb-1">
                    <a class="sidebar-link" href="{{ route('progressreport_view') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-journals"></i>
                        </span>
                        <span class="hide-menu d-flex position-relative" id="progressCountId">
                            Progress Report
                        </span>
                    </a>
                </li>
                @if (auth()->user() && auth()->user()->usertype == 'ADMIN')
                    <hr class="border-top border-white mb-2">
                    <li class="sidebar-item mb-1">
                        <span class="hide-menu ms-2 text-white fw-semibold" style="font-size: 16px">INVENTORY</span>
                        <a class="sidebar-link {{ in_array(Route::currentRouteName(), ['inventoryreport_view', 'inventoryreportPrint', 'inventoryreport_staff']) ? 'active' : '' }}"
                            href="{{ route('inventoryreport_view') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-box2-fill"></i>
                            </span>
                            <span class="hide-menu">Equipment</span>
                        </a>
                    </li>
                    <hr class="border-top border-white mt-2">
                    <span class="hide-menu ms-2 text-white fw-semibold" style="font-size: 16px">REPORT</span>
                    <li class="sidebar-item mb-1">
                        <a class="sidebar-link {{ in_array(Route::currentRouteName(), ['submitreportdashboardadmin', 'staffreport_view']) ? 'active' : '' }}"
                            href="{{ route('submitreportdashboardadmin') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-folder2-open"></i>
                            </span>
                            <span class="hide-menu">Submitted Report
                            </span>
                        </a>
                    </li>
                    <li class="sidebar-item mb-1">
                        <a class="sidebar-link {{ in_array(Route::currentRouteName(), ['incidentreportPrint', 'situationalreportPrint', 'progressreportPrint']) ? 'active' : '' }}" data-bs-toggle="modal" data-bs-target="#monthlyReportModal">
                            <span>
                                <i class="bi bi-folder2-open"></i>
                            </span>
                            <span class="hide-menu">Monthly Report</span>
                        </a>
                    </li>
                    {{-- <li class="sidebar-item mb-1">
                        <a class="sidebar-link {{ in_array(Route::currentRouteName(), ['incidentreportPrint', 'situationalreportPrint', 'progressreportPrint']) ? 'active' : '' }}"
                            href="{{ route('report_view') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-folder2-open"></i>
                            </span>
                            <span class="hide-menu">Monthly Report</span>
                        </a>
                    </li> --}}
                    <hr class="border-top border-white mt-2 mb-1">
                    <li class="sidebar-item mb-1">
                        <a class="sidebar-link" href="{{ route('user_view') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-people-fill"></i>
                            </span>
                            <span class="hide-menu">Staff</span>
                        </a>
                    </li>
                @else
                    {{-- <li class="sidebar-item mb-1">
                        <a class="sidebar-link {{ in_array(Route::currentRouteName(), ['incidentreport_staff', 'situationalreport_staff', 'progressreport_staff']) ? 'active' : '' }}"
                            href="{{ route('submitreportdashboard') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-journals"></i>
                            </span>
                            <span class="hide-menu">Add Report</span>
                        </a>
                    </li>
                    <li class="sidebar-item mb-1">
                        <a class="sidebar-link" href="{{ route('archive_view') }}" aria-expanded="false">
                            <span>
                                <i class="bi bi-folder-symlink-fill"></i>
                            </span>
                            <span class="hide-menu">Submitted Report</span>
                        </a>
                    </li> --}}
                @endif

                {{-- <li class="sidebar-item mb-1">
                    <a class="sidebar-link" href="{{ route('profile_view') }}" aria-expanded="false">
                        <span>
                            <i class="bi bi-person-circle"></i>
                        </span>
                        <span class="hide-menu">Profile</span>
                    </a>
                </li> --}}
            </ul>
        </nav>
    </div>
</aside>
