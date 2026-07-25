@extends('market.layouts.portal')

@section('content')
    @include('market.tenant.applications.css.applications')
    @include('market.tenant.applications.css.detail')

    <section class="tenant-application-page">
        <header class="tenant-page-title">
            <div>
                <i class="bi bi-shop-window"></i>
                <div><h1>APPLICATION</h1><p>Stall Rental Application Records</p></div>
            </div>
            <button type="button" class="button tenant-add-application" data-create-application-url="{{ route('tenant.applications.create') }}">
                <i class="bi bi-plus-circle-fill"></i> Add Application
            </button>
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
