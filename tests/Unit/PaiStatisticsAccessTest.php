<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PaiStatisticsAccessTest extends TestCase
{
    public function test_provider_users_cannot_view_pai_statistics_or_settings(): void
    {
        $provider = new User(['usertype' => 2]);

        $this->assertFalse(Gate::forUser($provider)->allows('view-statistics'));
    }

    public function test_administrators_and_nutritionists_can_view_pai_statistics_and_settings(): void
    {
        $administrator = new User(['usertype' => 1]);
        $nutritionist = new User(['usertype' => 3]);

        $this->assertTrue(Gate::forUser($administrator)->allows('view-statistics'));
        $this->assertTrue(Gate::forUser($nutritionist)->allows('view-statistics'));
    }

    public function test_all_pai_statistics_and_settings_routes_require_the_statistics_permission(): void
    {
        $routeNames = [
            'afiliado.stats.dashboard',
            'afiliado.stats.dose.detail',
            'afiliado.stats.view',
            'afiliado.stats.charts.view',
            'afiliado.stats.charts.data',
            'afiliado.stats.settings.index',
            'afiliado.stats.references.index',
            'afiliado.stats.references.data',
            'afiliado.stats.references.store',
            'afiliado.stats.references.update',
            'afiliado.stats.references.destroy',
            'afiliado.stats.indicadores.index',
            'afiliado.stats.indicadores.data',
            'afiliado.stats.indicadores.store',
            'afiliado.stats.indicadores.import.programacion',
            'afiliado.stats.indicadores.update',
            'afiliado.stats.indicadores.destroy',
            'afiliado.stats.bimonthly.index',
            'afiliado.stats.bimonthly.data',
            'afiliado.stats.bimonthly.export',
        ];

        foreach ($routeNames as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "No se encontró la ruta {$routeName}.");
            $this->assertContains('can:view-statistics', $route->gatherMiddleware(), "La ruta {$routeName} no está protegida.");
        }
    }
}
