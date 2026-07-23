<?php

namespace Tests\Unit;

use App\Services\PaiGestationClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PaiGestationClassifierTest extends TestCase
{
    #[DataProvider('conditions')]
    public function test_it_does_not_confuse_negative_conditions_with_pregnancy($condition, $weeks, bool $expected): void
    {
        $this->assertSame($expected, (new PaiGestationClassifier)->isGestante($condition, $weeks));
    }

    public static function conditions(): array
    {
        return [
            'explicit gestante' => ['GESTANTE', null, true],
            'affirmative gestante' => ['SI GESTANTE', null, true],
            'not gestante' => ['NO GESTANTE', null, false],
            'not a gestante' => ['NO ES GESTANTE', 12, false],
            'not applicable' => ['NO APLICA', null, false],
            'weeks without condition' => [null, 20, true],
            'zero weeks' => [null, 0, false],
        ];
    }
}
