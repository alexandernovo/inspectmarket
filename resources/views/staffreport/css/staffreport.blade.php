<style>
    #staffreportTable_wrapper .row .dt-length {
        display: flex;
    }

    #staffreportTable_wrapper .row .dt-length label {
        display: none;
    }

    #staffreportTable_wrapper thead tr th .dt-column-header .dt-column-title,
    #staffreportTable_wrapper td {
        font-size: 15px !important;
    }

    #staffreportTable.dataTable>thead>tr>th,
    #staffreportTable.dataTable>thead>tr>td {
        position: relative !important;
        text-wrap: nowrap !important;
    }

    #staffreportTable.dataTable>thead>tr>th:last-child {
        position: sticky !important;
        right: -3px !important;
        background: #3a0000 !important;
        z-index: 3;
        width: 150px;
    }

    #staffreportTable.dataTable th,
    #staffreportTable.dataTable td {
        white-space: nowrap !important;
        position: relative;
    }

    #staffreportTable_wrapper .dt-layout-table {
        overflow-x: auto !important;
        overflow-y: hidden !important;
    }

    #staffreportTable th[data-dt-column="6"] {
        padding: 0 !important;
    }

    #archivedTable_wrapper .row .dt-length {
        display: flex;
    }

    #archivedTable_wrapper .row .dt-length label {
        display: none;
    }

    #archivedTable.dataTable>thead>tr>th,
    #archivedTable.dataTable>thead>tr>td {
        position: relative !important;
        text-wrap: nowrap !important;
    }

    #archivedTable.dataTable>thead>tr>th:last-child {
        position: sticky !important;
        right: -3px !important;
        background: #3a0000 !important;
        z-index: 3;
        width: 150px;
    }

    #archivedTable.dataTable th,
    #archivedTable.dataTable td {
        white-space: nowrap !important;
        position: relative;
    }

    #archivedTable_wrapper .dt-layout-table {
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

    #archivedTable th[data-dt-column="6"] {
        padding: 0 !important;
    }

    th,
    td {
        font-size: 12px !important;
    }
</style>
