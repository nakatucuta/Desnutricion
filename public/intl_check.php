<?php
header('Content-Type: text/plain; charset=UTF-8');

echo "intl loaded: " . (extension_loaded('intl') ? 'yes' : 'no') . PHP_EOL;
echo "ICU version: " . (defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : 'n/a') . PHP_EOL;
echo "default locale: " . Locale::getDefault() . PHP_EOL;

$currencyFormatter = new NumberFormatter('es_CO', NumberFormatter::CURRENCY);
echo "currency sample (es_CO): " . $currencyFormatter->formatCurrency(12345.67, 'COP') . PHP_EOL;

$dateFormatter = new IntlDateFormatter(
    'es_CO',
    IntlDateFormatter::FULL,
    IntlDateFormatter::NONE,
    'America/Bogota'
);
echo "date sample (es_CO): " . $dateFormatter->format(new DateTime('2026-04-23')) . PHP_EOL;
