<style>
    #inventoryreportTable_wrapper .row .dt-length {
        display: flex;
    }

    #inventoryreportTable_wrapper .row .dt-length label {
        display: none;
    }

    #inventoryreportTable_wrapper thead tr th .dt-column-header .dt-column-title,
    #inventoryreportTable_wrapper td {
        font-size: 15px !important;
    }

    #inventoryreportTable.dataTable>thead>tr>th,
    #inventoryreportTable.dataTable>thead>tr>td {
        position: relative !important;
        text-wrap: nowrap !important;
    }

    #inventoryreportTable.dataTable>thead>tr>th:last-child {
        position: sticky !important;
        right: -3px !important;
        background: #3a0000 !important;
        z-index: 3;
        width: 150px;
    }

    #inventoryreportTable.dataTable th,
    #inventoryreportTable.dataTable td {
        white-space: nowrap !important;
        position: relative;
    }

    #inventoryreportTable_wrapper .dt-layout-table {
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

    #inventoryreportTable th[data-dt-column="6"] {
        padding: 0 !important;
    }

    th,
    td {
        font-size: 12px !important;
    }
</style>
