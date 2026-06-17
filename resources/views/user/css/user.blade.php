<style>
    #userTable_wrapper .row .dt-length {
        display: flex;
    }

    #userTable_wrapper .row .dt-length label {
        display: none;
    }

    #userTable_wrapper thead tr th .dt-column-header .dt-column-title,
    #userTable_wrapper td {
        font-size: 15px !important;
    }

    #userTable.dataTable>thead>tr>th,
    #userTable.dataTable>thead>tr>td {
        position: relative !important;
        text-wrap: nowrap !important;
    }

    #userTable.dataTable>thead>tr>th:last-child {
        position: sticky !important;
        right: -3px !important;
        background: #3a0000 !important;
        z-index: 3;
        width: 150px;
    }

    #userTable.dataTable th,
    #userTable.dataTable td {
        white-space: nowrap !important;
        position: relative;
    }

    #userTable_wrapper .dt-layout-table {
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

    #userTable th[data-dt-column="6"] {
        padding: 0 !important;
    }

    th,
    td {
        font-size: 12px !important;
    }
</style>
