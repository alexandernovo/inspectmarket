<style>
    #progressreportTable_wrapper .row .dt-length {
        display: flex;
    }

    #progressreportTable_wrapper .row .dt-length label {
        display: none;
    }

    #progressreportTable_wrapper thead tr th .dt-column-header .dt-column-title,
    #progressreportTable_wrapper td {
        font-size: 15px !important;
    }

    #progressreportTable.dataTable>thead>tr>th,
    #progressreportTable.dataTable>thead>tr>td {
        position: relative !important;
        text-wrap: nowrap !important;
    }

    #progressreportTable.dataTable>thead>tr>th:last-child {
        position: sticky !important;
        right: -3px !important;
        background: #3a0000 !important;
        z-index: 3;
        width: 150px;
    }

    #progressreportTable.dataTable th,
    #progressreportTable.dataTable td {
        white-space: nowrap !important;
        position: relative;
    }

    #progressreportTable_wrapper .dt-layout-table {
        overflow-x: auto !important;
        overflow-y: hidden !important;
    }

    .sticky-action {
        position: sticky !important;
        right: -3px !important;
        background: white !important;
        z-index: 3;
        width: 80px !important;
    }

    #progressreportTable th[data-dt-column="6"] {
        padding: 0 !important;
    }

    th,
    td {
        font-size: 12px !important;
    }
</style>
