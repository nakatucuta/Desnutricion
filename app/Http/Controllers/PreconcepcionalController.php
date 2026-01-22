<?php

namespace App\Http\Controllers;

use App\Imports\PreconcepcionalImport;
use App\Models\Preconcepcional;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Exports\PreconcepcionalExport;
use App\Models\PreconcepcionalImportBatch;
use Illuminate\Support\Facades\Auth;


class PreconcepcionalController extends Controller
{
 public function index()
    {
        // ✅ Ya NO uses paginate aquí. DataTables hará todo por AJAX.
        return view('preconcepcional.index');
    }

    // ✅ AJAX JSON para DataTables
    public function data(Request $request)
    {
        $q = Preconcepcional::query()
            ->select([
                'id',
                'tipo_documento',
                'numero_identificacion',
                'nombre_1',
                'nombre_2',
                'apellido_1',
                'apellido_2',
                'municipio_residencia',
                'riesgo_preconcepcional',
            ]);

        return DataTables::of($q)
            ->addColumn('nombres', function ($r) {
                return trim(($r->nombre_1 ?? '') . ' ' . ($r->nombre_2 ?? ''));
            })
            ->addColumn('apellidos', function ($r) {
                return trim(($r->apellido_1 ?? '') . ' ' . ($r->apellido_2 ?? ''));
            })
            ->addColumn('acciones', function ($r) {
                $url = route('preconcepcional.show', $r->id);
                return '
                    <a class="btn btn-sm btn-primary" href="'.$url.'" title="Ver">
                        <i class="fas fa-eye"></i>
                    </a>
                ';
            })
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function create()
    {
        return view('preconcepcional.import');
    }
public function store(Request $request)
{
    Log::channel('single')->emergency('✅ ENTRO AL STORE PRECONCEPCIONAL', [
        'time'     => now()->format('Y-m-d H:i:s'),
        'user_id'  => auth()->id(),
        'files'    => array_keys($request->allFiles()),
        'has_file' => $request->hasFile('file'),
    ]);

    $validator = Validator::make($request->all(), [
        'file' => 'required|file|mimes:xlsx,xls,csv'
    ]);

    if ($validator->fails()) {
        Log::channel('single')->error('❌ VALIDACION REQUEST FALLO (NO LLEGA EL ARCHIVO)', [
            'errors' => $validator->errors()->toArray(),
            'files'  => $request->allFiles(),
        ]);

        return back()
            ->with('warning', 'No está llegando el archivo al backend. Revisa name="file" y enctype.')
            ->withErrors($validator)
            ->withInput();
    }

    $file = $request->file('file');

    // ✅ Datos del archivo
    $path = $file->getRealPath();
    $hash = $path ? hash_file('sha256', $path) : null;
    $size = $file->getSize();

    // ✅ FECHA SIN MILISEGUNDOS (SQL Server datetime)
    $now = now()->format('Y-m-d H:i:s');

    // ✅ Crea lote SIN timestamps automáticos (evita .952)
    $batch = PreconcepcionalImportBatch::withoutTimestamps(function () use ($file, $hash, $size, $now) {
        return PreconcepcionalImportBatch::create([
            // 👇 usa estos nombres porque tu migración tiene original_name/file_size
            'original_name' => $file->getClientOriginalName(),
            'user_id'       => auth()->id(),
            'file_hash'     => $hash,
            'file_size'     => $size,

            // 👇 timestamps manuales sin milisegundos
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);
    });

    Log::channel('single')->info('📦 Batch creado', [
        'batch_id' => $batch->id,
        'file'     => $batch->original_name,
        'hash'     => $batch->file_hash,
        'size'     => $batch->file_size,
    ]);

    $t0 = microtime(true);

    // ✅ Import con batchId
    $import = new PreconcepcionalImport($batch->id);
    Excel::import($import, $file);

    $seconds = round(microtime(true) - $t0, 2);

    // ✅ Update también sin timestamps automáticos (por seguridad)
    PreconcepcionalImportBatch::withoutTimestamps(function () use ($batch, $import, $seconds, $now) {
        $batch->update(array_merge(
            $import->getCounters(),
            [
                'duration_seconds' => $seconds,
                'updated_at'       => $now,
            ]
        ));
    });

    return redirect()
        ->route('preconcepcional.batches')
        ->with('success', "✅ Importación lista. Lote #{$batch->id} | Tiempo: {$seconds}s");
}


    public function show(Preconcepcional $preconcepcional)
    {
        return view('preconcepcional.show', compact('preconcepcional'));
    }


   public function export(Request $request)
{
    $format = $request->get('format', 'csv'); // csv | xlsx

    // 🔎 Filtro de búsqueda global (DataTables)
    $search = $request->input('search.value');

    // 🧭 Orden (DataTables)
    $orderColIndex = (int) $request->input('order.0.column', 0);
    $orderDir      = $request->input('order.0.dir', 'desc');

    // Mapa de columnas (las visibles en tu DataTable)
    $columnsMap = [
        0 => 'id',
        1 => 'tipo_documento',
        2 => 'numero_identificacion',
        3 => 'nombre_1',
        4 => 'apellido_1',
        5 => 'municipio_residencia',
        6 => 'riesgo_preconcepcional',
    ];

    $orderBy = $columnsMap[$orderColIndex] ?? 'id';

    $q = Preconcepcional::query();

    if ($search) {
        $q->where(function($qq) use ($search){
            $qq->where('tipo_documento', 'like', "%{$search}%")
               ->orWhere('numero_identificacion', 'like', "%{$search}%")
               ->orWhere('nombre_1', 'like', "%{$search}%")
               ->orWhere('nombre_2', 'like', "%{$search}%")
               ->orWhere('apellido_1', 'like', "%{$search}%")
               ->orWhere('apellido_2', 'like', "%{$search}%")
               ->orWhere('municipio_residencia', 'like', "%{$search}%")
               ->orWhere('riesgo_preconcepcional', 'like', "%{$search}%");
        });
    }

    $q->orderBy($orderBy, $orderDir);

    // ✅ LIMPIO: trae TODO (todas las columnas reales de tu tabla)
    $rows = $q->select('*')->get();

    $fileBase = 'preconcepcional_full_' . now()->format('Ymd_His');

    // -----------------------
    // ✅ CSV FULL (sin librerías extra)
    // -----------------------
    if ($format === 'csv') {

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileBase}.csv\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');

            // BOM para Excel
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados = columnas del primer registro
            if ($rows->count() > 0) {
                fputcsv($out, array_keys($rows->first()->getAttributes()));
            } else {
                fputcsv($out, ['Sin datos']);
                fclose($out);
                return;
            }

            foreach ($rows as $r) {
                // getAttributes() devuelve el array con todas las columnas
                $data = $r->getAttributes();

                // fuerza fechas a string (por si vienen como Carbon)
                foreach ($data as $k => $v) {
                    if ($v instanceof \DateTimeInterface) {
                        $data[$k] = $v->format('Y-m-d H:i:s');
                    }
                }

                fputcsv($out, array_values($data));
            }

            fclose($out);
        };

        return Response::stream($callback, 200, $headers);
    }

    // -----------------------
    // ✅ XLSX FULL (Maatwebsite)
    // -----------------------
    return Excel::download(new PreconcepcionalExport($rows), "{$fileBase}.xlsx");
}


public function batches()
{
    $userId = Auth::id();

    $batches = PreconcepcionalImportBatch::with('user')
        ->where('user_id', $userId) // ✅ SOLO LOTES DEL USUARIO ACTIVO
        ->orderByDesc('id')
        ->paginate(20);

    return view('preconcepcional.batches', compact('batches'));
}

public function batchShow(PreconcepcionalImportBatch $batch)
{
    abort_unless((int)$batch->user_id === (int)auth()->id(), 403, 'No tienes permiso para ver este lote.');

    $batch->load('user');

    $registros = Preconcepcional::where('created_batch_id', $batch->id)
        ->orderByDesc('id')
        ->paginate(25);

    return view('preconcepcional.batch_show', compact('batch', 'registros'));
}


public function destroyBatch(PreconcepcionalImportBatch $batch)
{
    abort_unless((int)$batch->user_id === (int)auth()->id(), 403, 'No tienes permiso para eliminar este lote.');

    $deleted = Preconcepcional::where('created_batch_id', $batch->id)->delete();
    $batch->delete();

    return redirect()
        ->route('preconcepcional.batches')
        ->with('success', "🗑️ Lote #{$batch->id} eliminado. Registros borrados: {$deleted}");
}


    
}
