<div class="cv-date-toolbar {{ $wrapperClass ?? '' }}">
    <div class="cv-date-toolbar__main">
        <label class="mb-1 font-weight-bold">{{ $label ?? 'Rango de fecha' }}</label>
        <div id="{{ $pickerId ?? 'daterange' }}" class="form-control cv-date-toolbar__picker">
            <i class="far fa-calendar-alt"></i>
            <span class="ml-2"></span>
            <i class="fa fa-caret-down float-right mt-1"></i>
        </div>
        <div class="cv-date-toolbar__chips">
            <button type="button" class="btn btn-light btn-sm cv-range-chip" data-range="7d">7 dias</button>
            <button type="button" class="btn btn-light btn-sm cv-range-chip" data-range="30d">30 dias</button>
            <button type="button" class="btn btn-light btn-sm cv-range-chip" data-range="90d">90 dias</button>
            <button type="button" class="btn btn-light btn-sm cv-range-chip" data-range="120d">120 dias</button>
            <button type="button" class="btn btn-light btn-sm cv-range-chip" data-range="month">Este mes</button>
            <button type="button" class="btn btn-light btn-sm cv-range-chip" data-range="prev_month">Mes anterior</button>
            <button type="button" class="btn btn-light btn-sm cv-range-chip" data-range="year">Ano actual</button>
        </div>
    </div>

    @if (!empty($applyButtonId))
        <div class="cv-date-toolbar__action">
            <button id="{{ $applyButtonId }}" class="btn btn-primary {{ $applyButtonClass ?? '' }}">
                <i class="{{ $applyIcon ?? 'fas fa-sync' }}"></i> {{ $applyLabel ?? 'Aplicar' }}
            </button>
        </div>
    @endif

    @if (!empty($note))
        <div class="cv-date-toolbar__note {{ $noteClass ?? '' }}">
            {!! $note !!}
        </div>
    @endif
</div>
