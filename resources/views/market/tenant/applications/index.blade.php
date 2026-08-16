@extends('market.layouts.portal')

@section('content')
    @php
        $tenantApplicationTitle = $tenantApplicationTitle ?? 'APPLICATION';
        $tenantApplicationBreadcrumb = $tenantApplicationBreadcrumb ?? 'Dashboard | Application';
        $tenantApplicationCanCreate = $tenantApplicationCanCreate ?? auth()->user()->isRole('TENANT');
        $tenantApplicationCreateUrl = $tenantApplicationCreateUrl ?? route('tenant.applications.create');
        $tenantApplicationDataTableRoute = $tenantApplicationDataTableRoute ?? 'tenant.datatable.applications';
        $tenantApplicationDataTableRouteParams = $tenantApplicationDataTableRouteParams ?? [];
    @endphp
    @include('market.tenant.applications.css.applications')
    @include('market.tenant.applications.css.detail')

    <section class="tenant-application-page">
        <header class="tenant-page-title">
            <div>
                <i class="bi bi-shop-window"></i>
                <div><h1>{{ $tenantApplicationTitle }}</h1><p>{{ $tenantApplicationBreadcrumb }}</p></div>
            </div>
            @if ($tenantApplicationCanCreate)
                <button type="button" class="button tenant-add-application" data-create-application-url="{{ $tenantApplicationCreateUrl }}">
                    <i class="bi bi-plus-circle-fill"></i> Add Application
                </button>
            @endif
        </header>

        <section class="tenant-table-card">
            <div class="tenant-application-toolbar"></div>
            <div class="table-wrap">
                <table id="tenantApplicationTable" class="tenant-server-table">
                    <thead>
                        <tr>
                            <th>NO.</th>
                            <th>TIN NUMBER</th>
                            <th>OWNER</th>
                            <th>ADDRESS</th>
                            <th>CELLPHONE NUMBER</th>
                            <th>STATUS</th>
                            <th>DATE SUBMITTED</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </section>
    </section>

    <dialog id="tenantApplicationCreateDialog" class="tenant-management-dialog tenant-create-dialog">
        <div class="tenant-dialog-loading"><i class="bi bi-arrow-clockwise"></i><span>Loading application form...</span></div>
    </dialog>

    <dialog id="tenantApplicationViewDialog" class="tenant-management-dialog tenant-view-dialog">
        <div class="tenant-dialog-loading"><i class="bi bi-arrow-clockwise"></i><span>Loading application details...</span></div>
    </dialog>
@endsection

@push('scripts')
    @include('market.tenant.applications.js.index')
    @include('market.tenant.applications.js.create')
    @include('market.tenant.applications.js.detail')
@endpush
