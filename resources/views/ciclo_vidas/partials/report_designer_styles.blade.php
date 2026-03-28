<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
@include('ciclo_vidas.partials.date_range_shared_styles')
<style>
    .content-wrapper, .content, .container-fluid { background: #f4f7fb !important; }
    .cv-report-hero {
        display: grid;
        grid-template-columns: 1.7fr 1fr;
        gap: 1rem;
        padding: 1.5rem 1.75rem;
        border-radius: 26px;
        background:
            radial-gradient(circle at top left, rgba(96,165,250,.24), transparent 32%),
            radial-gradient(circle at bottom right, rgba(244,63,94,.18), transparent 28%),
            linear-gradient(135deg, #0f172a, #1d4ed8 55%, #be123c);
        color: #fff;
        box-shadow: 0 18px 48px rgba(15, 23, 42, .22);
    }
    .cv-report-chip {
        display: inline-flex;
        align-items: center;
        padding: .35rem .75rem;
        border-radius: 999px;
        background: rgba(255,255,255,.14);
        font-size: .82rem;
        letter-spacing: .03em;
        margin-bottom: .9rem;
    }
    .cv-report-hero h1 { font-size: 2.15rem; font-weight: 800; color: #fff; }
    .cv-report-hero p { color: rgba(255,255,255,.84); }
    .cv-report-hero-side {
        display: grid;
        gap: .85rem;
        align-content: center;
    }
    .cv-report-brand,
    .cv-report-side-card {
        padding: 1rem 1.1rem;
        border-radius: 18px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.12);
    }
    .cv-report-brand {
        display: flex;
        align-items: center;
        gap: .85rem;
    }
    .cv-report-brand img {
        width: 58px;
        height: 58px;
        object-fit: contain;
        border-radius: 16px;
        background: rgba(255,255,255,.9);
        padding: .35rem;
    }
    .cv-report-brand small,
    .cv-report-side-card small {
        display: block;
        color: rgba(255,255,255,.74);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: .2rem;
    }
    .cv-template-card {
        width: 100%;
        min-height: 160px;
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        border: 0;
        border-radius: 22px;
        padding: 1.25rem 1.25rem 1.1rem;
        color: #fff;
        text-align: left;
        box-shadow: 0 14px 32px rgba(15, 23, 42, .12);
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .cv-template-card:hover,
    .cv-template-card.is-active {
        transform: translateY(-3px);
        box-shadow: 0 18px 36px rgba(15, 23, 42, .16);
    }
    .cv-template-card__icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: rgba(255,255,255,.16);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex: 0 0 54px;
    }
    .grad-indigo { background: linear-gradient(135deg, #4338ca, #2563eb); }
    .grad-emerald { background: linear-gradient(135deg, #0f766e, #16a34a); }
    .grad-cyan { background: linear-gradient(135deg, #0f766e, #06b6d4); }
    .grad-rose { background: linear-gradient(135deg, #be123c, #f43f5e); }
    .cv-report-note {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .7rem 1rem;
        border-radius: 999px;
        background: #eef6ff;
        color: #0f3f74;
        font-size: .92rem;
    }
    .cv-report-filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .cv-report-age-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }
    .cv-report-panel {
        border-radius: 22px;
        border: 1px solid #e6edf7;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
        padding: 1.2rem;
    }
    .cv-report-actions-inline { display: flex; flex-wrap: wrap; gap: .5rem; }
    .cv-field-groups { display: grid; gap: 1rem; }
    .cv-field-group { border: 1px solid #e6edf7; border-radius: 18px; overflow: hidden; }
    .cv-field-group__header { padding: .85rem 1rem; background: #f8fbff; border-bottom: 1px solid #e6edf7; }
    .cv-field-group__body { padding: 1rem; }
    .cv-field-option {
        width: 100%;
        display: flex;
        gap: .75rem;
        align-items: flex-start;
        padding: .7rem .8rem;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e6edf7;
        margin: 0;
        cursor: pointer;
    }
    .cv-field-option input { margin-top: .2rem; }
    .cv-field-option strong { display: block; color: #0f172a; font-size: .95rem; }
    .cv-field-option small { color: #64748b; }
    .cv-selected-fields {
        min-height: 220px;
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        padding: .9rem;
        border-radius: 18px;
        border: 1px dashed #cbd5e1;
        background: #f8fbff;
        margin-bottom: 1rem;
    }
    .cv-selected-chip {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .65rem .8rem;
        border-radius: 999px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #0f3f74;
        font-weight: 600;
        cursor: grab;
    }
    .cv-selected-chip button { border: 0; background: transparent; color: #2563eb; padding: 0; line-height: 1; }
    .cv-report-user-card {
        border-radius: 18px;
        padding: 1rem;
        background: linear-gradient(135deg, #0f172a, #1d4ed8);
        color: #fff;
        margin-bottom: 1rem;
    }
    .cv-report-user-card small,
    .cv-report-user-card span { display: block; color: rgba(255,255,255,.78); }
    .cv-report-user-card strong { display: block; font-size: 1.05rem; margin: .2rem 0 .5rem; }
    .cv-report-actions-stack { display: grid; gap: .75rem; }
    .cv-report-guide {
        height: 100%;
        border-radius: 20px;
        border: 1px solid #e6edf7;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        box-shadow: 0 10px 26px rgba(15, 23, 42, .05);
        padding: 1.1rem 1.15rem;
    }
    .cv-report-guide i {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        color: #fff;
        margin-bottom: .85rem;
    }
    .cv-preview-sheet {
        border-radius: 20px;
        border: 1px solid #dbe6f2;
        background: linear-gradient(180deg, #f8fbff, #ffffff);
        padding: 1rem 1.1rem;
    }
    .cv-preview-sheet__meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: .85rem;
    }
    .cv-preview-sheet__meta > div {
        padding: .95rem 1rem;
        border-radius: 18px;
        background: linear-gradient(180deg, #ffffff, #eff6ff);
        border: 1px solid #d7e6f5;
    }
    .cv-preview-sheet__meta small { display: block; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .2rem; }
    .cv-preview-sheet__meta strong { color: #0f172a; font-weight: 800; }
    .cv-preview-sheet__filters {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
        color: #475569;
        font-size: .92rem;
    }
    .cv-preview-filter-chip {
        display: inline-flex;
        flex-direction: column;
        gap: .15rem;
        padding: .7rem .85rem;
        border-radius: 16px;
        background: #eef6ff;
        border: 1px solid #cfe1f5;
        min-width: 150px;
    }
    .cv-preview-filter-chip small {
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin: 0;
    }
    .cv-preview-filter-chip strong {
        color: #0f3f74;
        font-size: .95rem;
        line-height: 1.3;
        word-break: break-word;
    }
    #reportPreviewTable th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #eff6ff;
        color: #244266;
        font-weight: 800;
        white-space: nowrap;
    }
    #reportPreviewTable td {
        vertical-align: top;
        color: #1e293b;
        min-width: 140px;
    }
    #reportPreviewTable td,
    #reportPreviewTable th {
        padding: .85rem .9rem;
    }
    .cv-report-loading {
        position: fixed;
        inset: 0;
        z-index: 3100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
    }
    .cv-report-loading.is-visible { display: flex; }
    .cv-report-loading__backdrop {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at top left, rgba(59,130,246,.28), transparent 30%),
            radial-gradient(circle at bottom right, rgba(244,63,94,.22), transparent 32%),
            rgba(15, 23, 42, .74);
        backdrop-filter: blur(12px);
    }
    .cv-report-loading__panel {
        position: relative;
        width: min(540px, 100%);
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid rgba(255,255,255,.14);
        background: linear-gradient(145deg, rgba(15, 23, 42, .96), rgba(30, 41, 59, .92));
        box-shadow: 0 30px 80px rgba(15, 23, 42, .42);
        padding: 2rem 1.8rem 1.7rem;
        text-align: center;
        color: #fff;
    }
    .cv-report-loading__panel::before {
        content: '';
        position: absolute;
        inset: -30% auto auto -10%;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(59,130,246,.38), transparent 70%);
    }
    .cv-report-loading__panel::after {
        content: '';
        position: absolute;
        right: -70px;
        bottom: -70px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(244,63,94,.28), transparent 72%);
    }
    .cv-report-loading__grid {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(148, 163, 184, .08) 1px, transparent 1px),
            linear-gradient(90deg, rgba(148, 163, 184, .08) 1px, transparent 1px);
        background-size: 28px 28px;
        mask-image: linear-gradient(180deg, transparent, rgba(255,255,255,.75), transparent);
    }
    .cv-report-loading__orb {
        position: relative;
        width: 104px;
        height: 104px;
        margin: 0 auto 1rem;
    }
    .cv-report-loading__orb span {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 2px solid transparent;
        border-top-color: rgba(96, 165, 250, .95);
        border-right-color: rgba(244, 114, 182, .75);
        animation: cvReportSpin 1.45s linear infinite;
    }
    .cv-report-loading__orb span:nth-child(2) {
        inset: 12px;
        border-top-color: rgba(34, 211, 238, .9);
        border-right-color: rgba(251, 191, 36, .7);
        animation-duration: 1.05s;
        animation-direction: reverse;
    }
    .cv-report-loading__orb span:nth-child(3) {
        inset: 26px;
        border-top-color: rgba(244, 114, 182, .95);
        border-right-color: rgba(96, 165, 250, .72);
        animation-duration: .9s;
    }
    .cv-report-loading__brand img {
        width: 48px;
        height: 48px;
        object-fit: contain;
        border-radius: 14px;
        background: rgba(255,255,255,.92);
        padding: .3rem;
        margin-bottom: .8rem;
    }
    .cv-report-loading__panel h3 { color: #fff; font-size: 1.5rem; font-weight: 800; }
    .cv-report-loading__panel p { color: rgba(226, 232, 240, .86); max-width: 360px; margin: 0 auto 1rem; }
    .cv-report-loading__status {
        display: inline-flex;
        align-items: center;
        gap: .65rem;
        padding: .7rem 1rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, .45);
        border: 1px solid rgba(148, 163, 184, .18);
        color: #e2e8f0;
        font-weight: 600;
    }
    .cv-report-loading__dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #22d3ee;
        box-shadow: 0 0 0 0 rgba(34, 211, 238, .7);
        animation: cvReportPulse 1.5s ease-out infinite;
    }
    body.cv-report-loading-lock { overflow: hidden; }
    @keyframes cvReportSpin { to { transform: rotate(360deg); } }
    @keyframes cvReportPulse {
        0% { box-shadow: 0 0 0 0 rgba(34, 211, 238, .7); }
        70% { box-shadow: 0 0 0 14px rgba(34, 211, 238, 0); }
        100% { box-shadow: 0 0 0 0 rgba(34, 211, 238, 0); }
    }
    @media (max-width: 1199px) {
        .cv-report-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 991px) {
        .cv-report-hero { grid-template-columns: 1fr; }
        .cv-preview-sheet__meta { grid-template-columns: 1fr; }
    }
    @media (max-width: 575px) {
        .cv-report-filter-grid,
        .cv-report-age-grid { grid-template-columns: 1fr; }
    }
</style>
