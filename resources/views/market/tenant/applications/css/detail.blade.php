<style>
    .tenant-application-detail-page { min-height: 100%; padding: 14px; background: #d3d3d3; }
    .tenant-application-detail-grid { display: grid; grid-template-columns: minmax(520px, 1fr) minmax(620px, 1.08fr); gap: 14px; width: 100%; min-height: 780px; padding: 12px; border: 6px solid #650006; border-radius: 8px; background: #fff; }
    .tenant-document-viewer { position: relative; display: grid; grid-template-rows: minmax(620px, 1fr) auto; min-width: 0; border: 1px solid #bcb4af; border-radius: 4px; background: #f3f2f1; overflow: hidden; }
    .tenant-detail-back { position: absolute; z-index: 5; top: 12px; left: 12px; display: grid; place-items: center; width: 42px; height: 42px; border-radius: 50%; color: #fff; background: #760008; }
    .tenant-document-stage { position: relative; min-height: 620px; }
    .tenant-document-frame { display: none; width: 100%; height: 100%; min-height: 620px; }
    .tenant-document-frame.active { display: grid; place-items: center; }
    .tenant-document-frame > img { width: 100%; height: 100%; max-height: 720px; padding: 14px; object-fit: contain; }
    .tenant-document-frame > iframe { width: 100%; height: 100%; border: 0; background: #fff; }
    .tenant-file-fallback { display: grid; place-items: center; align-content: center; gap: 12px; min-height: 620px; padding: 30px; text-align: center; }
    .tenant-file-fallback > i { color: #760008; font-size: 7rem; }
    .tenant-file-fallback > strong { max-width: 90%; color: #520004; font-size: 1.15rem; overflow-wrap: anywhere; }
    .tenant-file-fallback > span,
    .tenant-file-fallback > p { margin: 0; color: #766761; }
    .tenant-file-fallback .button { color: #fff; background: #a45c00; }
    .tenant-document-tabs { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 7px; padding: 9px; border-top: 1px solid #c9bfba; background: #fff; }
    .tenant-document-tab { display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: 8px; align-items: center; min-width: 0; padding: 9px; border: 1px solid #cfbfc0; border-radius: 4px; color: #650006; background: #fff; text-align: left; }
    .tenant-document-tab.active { color: #fff; border-color: #650006; background: #650006; }
    .tenant-document-tab > i { font-size: 1.25rem; }
    .tenant-document-tab span { display: grid; min-width: 0; overflow: hidden; font-size: .7rem; text-overflow: ellipsis; white-space: nowrap; }
    .tenant-document-tab small { opacity: .75; }
    .tenant-detail-form { min-width: 0; }
    .tenant-detail-form .application-form-banner { min-height: 110px; }
    .tenant-detail-form .application-form-banner > .status { position: absolute; top: 18px; right: 64px; }
    .tenant-detail-form .tenant-readonly-grid { gap: 9px 14px; padding: 14px 4px; }
    .tenant-detail-form .tenant-readonly-grid p strong { min-height: 38px; }
    .tenant-detail-wide { grid-column: 1 / -1; }
    .tenant-detail-actions { display: flex; justify-content: flex-end; padding: 12px 4px 2px; border-top: 1px solid #d2c8c2; }
    .tenant-detail-actions .button { color: #fff; background: #760008; }
    @media (max-width: 1180px) {
        .tenant-application-detail-grid { grid-template-columns: 1fr; }
        .tenant-document-viewer { min-height: 620px; }
    }
    @media (max-width: 620px) {
        .tenant-application-detail-page { padding: 6px; }
        .tenant-application-detail-grid { display: block; padding: 7px; border-width: 4px; }
        .tenant-document-viewer { min-height: 480px; }
        .tenant-document-stage,
        .tenant-document-frame,
        .tenant-file-fallback { min-height: 430px; }
        .tenant-document-frame > iframe { height: 500px; }
        .tenant-detail-form { margin-top: 10px; }
        .tenant-detail-form .tenant-readonly-grid { grid-template-columns: 1fr; }
        .tenant-detail-form .tenant-readonly-grid h3,
        .tenant-detail-wide { grid-column: auto; }
    }
</style>
