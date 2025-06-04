<?php

namespace App\Http\Controllers;

use App\Models\Tamizaje;
use App\Models\TamizajePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\DB;

class TamizajePdfController extends Controller
{
    /**
     * Mostrar formulario de subida de ZIP
     */
    public function showUploadForm()
    {
        return view('tamizajes.upload_zip');
    }

    /**
     * Procesar el ZIP: extraer cada PDF, renombrar y asociar a tamizaje
     */
    public function handleZipUpload(Request $request)
    {
        $request->validate([
            'pdf_zip' => 'required|file|mimes:zip',
        ]);

        $zipFile = $request->file('pdf_zip');
        $tipo    = $request->input('tipo_identificacion');   // opcional, si quieres forzar 
        $numero  = $request->input('numero_identificacion'); // opcional

        // Guardamos el ZIP en temporal
        $tmpPath = $zipFile->getRealPath();

        $zip = new ZipArchive;
        if ($zip->open($tmpPath) === true) {
            $extractPath = storage_path('app/public/tamizajes/tmp_zip_'.uniqid());
            // Creamos carpeta temporal
            @mkdir($extractPath, 0755, true);

            // Extraemos todo
            $zip->extractTo($extractPath);
            $zip->close();

            // Recorremos recursivamente los archivos extraídos
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            DB::beginTransaction();
            try {
                foreach ($files as $file) {
                    if (!$file->isFile()) continue;

                    $origName = $file->getFilename();
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    if ($ext !== 'pdf') continue; // ignorar no-PDFs

                    // --- 1) Intentar extraer “tipo_identificacion” y “numero_identificacion” del nombre de archivo ---
                    // Ejemplo de convención de nombre: CC_12345678_historia_clinica.pdf
                    $parts = explode('_', pathinfo($origName, PATHINFO_FILENAME));
                    // Esperamos al menos 2 segmentos: [0]=>tipo, [1]=>numero
                    if (count($parts) < 2) {
                        // Si no cumple la convención, lo saltamos o lo enviamos a carpeta “desconocidos”
                        continue;
                    }
                    $fileTipo   = strtoupper($parts[0]);
                    $fileNumero = $parts[1];

                    // 2) Buscar el Tamizaje que coincida (tomamos el más reciente si hay varios)
                    $tamizaje = Tamizaje::where('tipo_identificacion', $fileTipo)
                                ->where('numero_identificacion', $fileNumero)
                                ->orderByDesc('id')
                                ->first();

                    // 3) Si no existe tamizaje, opcionalmente crear un registro en tamizaje_pdfs sin tamizaje_id
                    //    (dependiendo de tu lógica, aquí elegimos continuar, sin insertar)
                    //    Alternativamente: $tamizaje = null; — permitimos la asociación por persona solamente.

                    // 4) Renombrar y mover cada PDF a /storage/tamizajes/{tipo}/{numero}/
                    $targetDir = "tamizajes/{$fileTipo}/{$fileNumero}";
                    $uniqueName = sprintf(
                        '%s_%s_%s.%s',
                        $fileTipo,
                        $fileNumero,
                        now()->format('YmdHis').'_'.uniqid(),
                        'pdf'
                    );
                    // Guardamos en disco “public”
                    $storedPath = Storage::disk('public')
                        ->putFileAs($targetDir, $file->getRealPath(), $uniqueName);

                    // 5) Creamos el registro en tamizaje_pdfs
                    TamizajePdf::create([
                        'tamizaje_id'           => $tamizaje?->id,
                        'tipo_identificacion'   => $fileTipo,
                        'numero_identificacion' => $fileNumero,
                        'original_name'         => $origName,
                        'file_path'             => $storedPath,
                    ]);
                }

                DB::commit();
                // Limpiar carpeta temporal
                \File::deleteDirectory($extractPath);

                return back()->with('success', 'ZIP procesado y PDFs asociados satisfactoriamente.');
            } catch (\Exception $e) {
                DB::rollBack();
                \File::deleteDirectory($extractPath);
                return back()->with('error', 'Error al procesar ZIP: '.$e->getMessage());
            }
        } else {
            return back()->with('error', 'No se pudo abrir el archivo ZIP.');
        }
    }

    /**
     * Mostrar listade PDFs de un tamizaje al hacer clic en su número de identificación
     */
    public function showPdfsByPerson($numero)
    {
        // Obtenemos todos los PDFs de esa persona, ordenados por fecha
        $pdfs = TamizajePdf::where('numero_identificacion', $numero)
                    ->orderByDesc('created_at')
                    ->get();

        return view('tamizajes.show_pdfs', compact('pdfs', 'numero'));
    }
}
