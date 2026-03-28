<style>
    .cv-date-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: .95rem 1rem;
    }
    .cv-date-toolbar__main {
        min-width: min(100%, 420px);
        flex: 1 1 420px;
    }
    .cv-date-toolbar__picker {
        width: 100%;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        border-radius: 12px;
        cursor: pointer;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid #cbd5e1;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
    }
    .cv-date-toolbar__chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .75rem;
    }
    .cv-range-chip {
        border-radius: 999px;
        border: 1px solid #d7e1ee;
        background: #fff;
        color: #334155;
        font-weight: 600;
        padding: .35rem .8rem;
        transition: all .15s ease;
    }
    .cv-range-chip:hover,
    .cv-range-chip:focus {
        border-color: #60a5fa;
        color: #1d4ed8;
        background: #eff6ff;
    }
    .cv-range-chip.is-active {
        border-color: #1d4ed8;
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        color: #1e3a8a;
        box-shadow: 0 8px 18px rgba(37, 99, 235, .14);
    }
    .cv-date-toolbar__action {
        flex: 0 0 auto;
    }
    .cv-date-toolbar__note {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        padding: .75rem 1rem;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        font-size: .92rem;
    }
    @media (max-width: 767px) {
        .cv-date-toolbar__note {
            margin-left: 0;
            width: 100%;
            border-radius: 14px;
        }
        .cv-date-toolbar__action {
            width: 100%;
        }
        .cv-date-toolbar__action .btn {
            width: 100%;
        }
    }
</style>
