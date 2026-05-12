<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\CicloVida\CicloVidaCoverageAnalyzer;
use Illuminate\Http\Request;

$analyzer = app(CicloVidaCoverageAnalyzer::class);
$ranges = [
 ['d'=>'2026-04-12','h'=>'2026-05-11'],
 ['d'=>'2025-05-11','h'=>'2026-05-11'],
 ['d'=>'2024-01-01','h'=>'2026-05-11'],
];
foreach($ranges as $r){
 $req = Request::create('/','GET',['desde'=>$r['d'],'hasta'=>$r['h']]);
 $res = $analyzer->analyze($req);
 echo $r['d']."..".$r['h']." => realized=".data_get($res,'summary.total_realized',-1)."\n";
}
