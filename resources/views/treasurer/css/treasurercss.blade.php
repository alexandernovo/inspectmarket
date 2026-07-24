<style>
    .bidding-card {
        background: white;
        max-width: 900px;
        margin: auto;
        color: black;
    }

    .custom-input {
        border: 2px solid #555;
        border-radius: 7px;
        height: 35px;
    }

    label {
        font-size: 18px;
    }

    .bottom-field {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .bottom-field span {
        width: 100px;
        font-style: italic;
        font-weight: bold;
    }

    .line-text {
        width: 220px;
        border-bottom: 1px solid #000;
        min-height: 24px;
        padding-left: 5px;
    }

    .line-input {
        border: none;
        border-bottom: 1px solid #777;
        background: transparent;
        outline: none;
        width: 180px;
    }

    .signature-name {
        font-size: 16px;
        color: black;
        font-weight: 700;
    }

    .signature-line {
        width: 260px;
        border-top: 1px solid #000;
        margin: 0 auto 4px;
    }

    .signature-label {
        font-size: 1rem;
    }

    .btn-warning {
        background: #b26a00;
        border-color: #b26a00;
        color: white;
    }

    .btn-danger {
        background: #800000;
        border-color: #800000;
    }

    #biddingTable thead th {
        background: #a46400 !important;
        color: white;
        text-align: center;
        vertical-align: middle;
        font-weight: 500;
        border: 1px solid black
    }

    #biddingTable tbody td {
        vertical-align: middle;
        border: 1px solid black;
        color: black
    }

    .btn-view-details {
        background: #555;
        color: white;
        font-size: 12px;
    }

    .btn-view-details:hover {
        background: #444;
        color: white;
    }

    div.dt-container div.dt-search label {
        font-size: 13px !important
    }

    .dt-length label {
        display: none !important;
    }

    input {
        height: 39px !important
    }

    .bg-secondary,
    .btn-secondary {
        background-color: #535353 !important;
        border: 1px solid #535353 !important;
    }

    .stall-card {
        border: none;
    }

    .section-block {
        margin-bottom: 30px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #9b1616;
        font-size: 20px;
    }

    .btn-success {
        background-color: #066308 !important;
    }

    .section-title img {
        width: 55px;
    }

    .section-line {
        border-bottom: 2px solid #b83535;
        margin-bottom: 12px;
    }

    .stall-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .stall {
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 7px;
        color: white;
        font-weight: 600;
        transition: .2s;
    }

    .stall.available {
        background: #066308;
    }

    .stall.occupied {
        background: #a10c0c;
    }

    .stall:hover {
        transform: scale(1.08);
    }
</style>
