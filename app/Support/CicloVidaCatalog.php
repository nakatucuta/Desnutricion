<?php

namespace App\Support;

use Illuminate\Support\Arr;
use RuntimeException;

class CicloVidaCatalog
{
    public static function courses(): array
    {
        return (array) config('ciclosvida.courses', []);
    }

    public static function courseBySlug(string $slug): array
    {
        foreach (self::courses() as $courseKey => $course) {
            if (($course['slug'] ?? null) === $slug) {
                return $course + ['key' => $courseKey];
            }
        }

        throw new RuntimeException("Curso no configurado: {$slug}");
    }

    public static function course(string $courseKey): array
    {
        $course = config("ciclosvida.courses.{$courseKey}");
        if (!is_array($course)) {
            throw new RuntimeException("Curso no configurado: {$courseKey}");
        }

        return $course + ['key' => $courseKey];
    }

    public static function module(string $courseKey, string $moduleKey): array
    {
        $module = config("ciclosvida.courses.{$courseKey}.modules.{$moduleKey}");
        if (!is_array($module)) {
            throw new RuntimeException("Modulo no configurado: {$courseKey}.{$moduleKey}");
        }

        return $module + ['key' => $moduleKey];
    }

    public static function menuGroups(string $courseKey): array
    {
        $course = self::course($courseKey);
        $modules = $course['modules'] ?? [];
        $courseSlug = $course['slug'] ?? $courseKey;

        return collect($course['groups'] ?? [])
            ->map(function (array $group) use ($modules, $courseSlug) {
                $group['items'] = collect($group['items'] ?? [])
                    ->map(function (string $moduleKey) use ($modules, $courseSlug) {
                        $item = Arr::get($modules, $moduleKey);
                        if (!is_array($item)) {
                            return null;
                        }

                        $item['key'] = $moduleKey;
                        $item['route'] = $item['route'] ?? 'ciclosvida.module.show';
                        $item['route_params'] = $item['route_params'] ?? [
                            'slug' => $courseSlug,
                            'moduleKey' => $moduleKey,
                        ];

                        return $item;
                    })
                    ->filter(fn ($item) => is_array($item) && isset($item['route']))
                    ->values()
                    ->all();

                return $group;
            })
            ->all();
    }

    public static function materializedModules(string $courseKey): array
    {
        return collect(config("ciclosvida.courses.{$courseKey}.modules", []))
            ->filter(fn (array $module) => !empty($module['materialized'])
                && (!empty($module['sql_file']) || !empty($module['source'])))
            ->keys()
            ->values()
            ->all();
    }
}
