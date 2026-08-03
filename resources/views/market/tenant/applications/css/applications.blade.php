<style>
    .tenant-application-page,
    .tenant-application-create { min-height: 100%; padding: 18px; background: #d3d3d3; }
    .tenant-page-title { display: flex; align-items: center; justify-content: space-between; gap: 16px; min-height: 76px; }
    .tenant-page-title > div { display: flex; align-items: center; gap: 12px; }
    .tenant-page-title > div > i { color: #650006; font-size: 2.25rem; }
    .tenant-page-title h1 { margin: 0; font-size: 1.75rem; font-weight: 500; }
    .tenant-page-title p { margin: 2px 0 0; }
    .tenant-add-application { color: #fff; background: #a45c00; }
    .tenant-table-card { padding: 12px; background: #fff; }
    .tenant-table-filters { display: flex; align-items: center; gap: 8px; min-height: 56px; padding: 9px; color: #fff; background: #650006; }
    .tenant-table-filters label { display: flex; align-items: center; gap: 6px; font-size: .78rem; }
    .tenant-table-filters input,
    .tenant-table-filters select { min-height: 34px; padding: 5px 7px; border: 0; border-radius: 3px; }
    .tenant-table-filters > select { min-height: 34px; padding: 5px; border: 2px solid #fff; border-radius: 3px; color: #fff; background: #650006; }
    .tenant-table-filters .button { min-height: 34px; padding: 5px 10px; border: 2px solid #fff; color: #fff; background: transparent; font-size: .78rem; }
    .tenant-table-search { margin-left: auto; }
    .tenant-table-search input { width: 190px; }
    .tenant-table-card .dt-length,
    .tenant-table-card .dt-search { display: none; }
    .tenant-server-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: .75rem; }
    .tenant-server-table th { padding: 9px 7px; color: #fff; background: #650006; text-align: center; }
    .tenant-server-table td { padding: 10px 7px; border: 1px solid #bdbdbd; text-align: center; overflow-wrap: anywhere; }
    .tenant-server-table th:nth-child(1) { width: 5%; }
    .tenant-server-table th:nth-child(2) { width: 12%; }
    .tenant-server-table th:nth-child(3) { width: 14%; }
    .tenant-server-table th:nth-child(4) { width: 19%; }
    .tenant-server-table th:nth-child(5) { width: 13%; }
    .tenant-server-table th:nth-child(6) { width: 11%; }
    .tenant-server-table th:nth-child(7) { width: 13%; }
    .tenant-server-table th:nth-child(8) { width: 13%; }
    .tenant-server-table .status { border-radius: 18px; }
    .tenant-server-table .table-action { display: inline-grid; margin: 2px; color: #fff; border: 0; background: #a45c00; }
    .tenant-server-table .table-action.reject { background: #760008; }
    .tenant-create-grid { display: grid; grid-template-columns: minmax(520px, 1fr) minmax(620px, 1.08fr); gap: 14px; width: 100%; max-width: 1780px; min-height: 780px; margin: auto; padding: 12px; border: 6px solid #650006; border-radius: 8px; background: #fff; overflow: hidden; }
    .tenant-upload-panel { display: flex; flex-direction: column; justify-content: center; min-height: 740px; padding: 24px; border-right: 1px solid #bdb5b0; }
    .tenant-upload-trigger { display: grid; place-items: center; gap: 12px; padding: 24px 6px; text-align: center; cursor: pointer; }
    .tenant-upload-trigger > i { color: #760008; font-size: 5rem; }
    .tenant-upload-trigger strong { color: #650006; font-size: 1.2rem; }
    .tenant-upload-trigger span { max-width: 280px; color: #705d54; line-height: 1.45; }
    .tenant-upload-trigger small { color: #98776a; }
    .tenant-upload-files { display: grid; gap: 9px; margin-top: 10px; }
    .tenant-upload-files article { display: grid; grid-template-columns: 28px minmax(0, 1fr) 30px; gap: 9px; align-items: center; padding: 10px; border: 1px solid #d2c4bd; border-radius: 5px; }
    .tenant-upload-files article > i { color: #760008; font-size: 1.3rem; }
    .tenant-upload-files article div { display: grid; min-width: 0; }
    .tenant-upload-files article strong { overflow: hidden; font-size: .72rem; text-overflow: ellipsis; white-space: nowrap; }
    .tenant-upload-files article span { color: #806f68; font-size: .65rem; }
    .tenant-upload-files button { width: 28px; height: 28px; border: 0; color: #760008; background: transparent; }
    .tenant-existing-files { display: grid; gap: 8px; margin-top: 12px; }
    .tenant-existing-files > strong { color: #650006; font-size: .8rem; text-align: center; }
    .tenant-existing-files > a { display: grid; grid-template-columns: 28px minmax(0, 1fr) 24px; gap: 8px; align-items: center; padding: 9px; border: 1px solid #d2c4bd; border-radius: 5px; color: #650006; text-decoration: none; }
    .tenant-existing-files > a > span { display: grid; min-width: 0; overflow: hidden; font-size: .7rem; text-overflow: ellipsis; white-space: nowrap; }
    .tenant-existing-files small { color: #806f68; font-size: .62rem; }
    .tenant-create-form { padding: 16px; }
    .tenant-create-form fieldset { margin: 14px 0 0; padding: 10px; border: 1px solid #cbbeb7; }
    .tenant-create-form legend { padding: 0 8px; color: #650006; font-size: .78rem; font-weight: 900; }
    .tenant-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 14px; }
    .tenant-form-grid label { display: grid; gap: 5px; color: #382a25; font-size: .72rem; font-weight: 800; }
    .tenant-form-grid input,
    .tenant-form-grid select { width: 100%; min-height: 36px; padding: 7px; border: 1px solid #b8ada7; border-radius: 3px; background: #fff; }
    .tenant-field-error { display: block; margin-top: 2px; color: #b00000; font-size: .68rem; font-weight: 700; line-height: 1.25; }
    .tenant-form-grid [aria-invalid="true"] { border-color: #b00000; }
    .tenant-upload-panel > .tenant-field-error { margin: 0 6px 8px; text-align: center; }
    .tenant-create-actions { display: flex; justify-content: flex-end; gap: 9px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #d2c8c2; }
    .tenant-submit-application { color: #fff; background: #a45c00; }
    .tenant-cancel-application { color: #fff; background: #760008; }
    .tenant-management-dialog { position: relative; width: 96vw; max-width: 1780px; max-height: 94vh; padding: 0; border: 0; border-radius: 9px; background: #fff; box-shadow: 0 24px 70px rgba(0,0,0,.48); overflow: auto; }
    .tenant-management-dialog::backdrop { background: rgba(25,0,0,.76); }
    .tenant-management-close { position: absolute; z-index: 20; top: 12px; right: 12px; width: 42px; height: 42px; border: 0; border-radius: 50%; color: #fff; background: transparent; font-size: 1.35rem; box-shadow: none; }
    .tenant-modal-content { min-height: 620px; padding: 10px; }
    .tenant-create-dialog .tenant-create-grid { max-width: none; min-height: 820px; border: 0; }
    .tenant-create-dialog .tenant-upload-panel { min-height: 796px; border: 1px solid #bcb4af; border-radius: 4px; background: #f3f2f1; }
    .tenant-create-dialog .tenant-create-form { padding: 0; }
    .tenant-view-content { padding: 10px; }
    .tenant-view-content .tenant-application-detail-grid { min-height: 820px; border: 0; }
    .tenant-dialog-loading,
    .tenant-dialog-error { display: grid; place-items: center; align-content: center; gap: 14px; min-height: 620px; color: #650006; background: #fff; }
    .tenant-dialog-loading i { font-size: 3rem; animation: tenant-dialog-spin 1s linear infinite; }
    .tenant-dialog-error i { font-size: 3rem; }
    @keyframes tenant-dialog-spin { to { transform: rotate(360deg); } }
    @media (max-width: 1180px) {
        .tenant-create-grid { grid-template-columns: 1fr; }
        .tenant-upload-panel { min-height: 300px; border-right: 0; border-bottom: 1px solid #bdb5b0; }
        .tenant-table-filters { flex-wrap: wrap; }
        .tenant-table-search { margin-left: 0; }
        .tenant-table-card .table-wrap { overflow-x: auto; }
        .tenant-server-table { width: 1100px; }
    }
    @media (max-width: 560px) {
        .tenant-page-title { align-items: flex-start; flex-direction: column; padding: 12px 0; }
        .tenant-form-grid { grid-template-columns: 1fr; }
        .tenant-table-filters label,
        .tenant-table-filters input,
        .tenant-table-filters select,
        .tenant-table-filters .button { width: 100%; }
        .tenant-create-actions { flex-direction: column; }
    }
</style>
