<?php

namespace App\Http\Controllers;

use App\Models\Tamizaje;
use App\Models\TamizajePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     * Procesar el ZIP: extraer cada PDF, validar y asociar a tamizaje
     */
    public function handleZipUpload(Request $request)
    {
        // 1) Validar que se suba un ZIP y opcionalmente tipos y números no vacíos
        $validator = Validator::make($request->all(), [
            'pdf_zip' => 'required|file|mimes:zip|max:5242880', // hasta 5 GB
            'tipo_identificacion'   => 'nullable|string', // no obligatorio
            'numero_identificacion' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $zipFile = $request->file('pdf_zip');
        $forcedTipo   = strtoupper($request->input('tipo_identificacion', ''));   // Opcional
        $forcedNumero = $request->input('numero_identificacion', '');

        // 2) Guardamos el ZIP en ruta temporal
        $tmpPath = $zipFile->getRealPath();

        $zip = new ZipArchive;
        if ($zip->open($tmpPath) !== true) {
            return back()->with('error', 'No se pudo abrir el archivo ZIP. Asegúrese de que sea un .zip válido.');
        }

        // 3) Creamos carpeta temporal para extraer
        $extractPath = storage_path('app/public/tamizajes/tmp_zip_'.uniqid());
        @mkdir($extractPath, 0755, true);
        $zip->extractTo($extractPath);
        $zip->close();

        // 4) Recorremos recursivamente los archivos extraídos
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $skippedFiles      = []; // PDFs ignorados
        $errors            = [];
        $processedCount    = 0;

        DB::beginTransaction();
        try {
            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $origName = $file->getFilename();
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if ($ext !== 'pdf') {
                    // Solo procesamos PDFs
                    $skippedFiles[] = $origName . ' (no es PDF)';
                    continue;
                }

                // 5) Validar convención de nombre: debe llevar al menos tipo_numero_*.pdf
                $baseName = pathinfo($origName, PATHINFO_FILENAME);
                $parts = explode('_', $baseName);

                if (count($parts) < 2) {
                    $skippedFiles[] = "$origName (nombre no sigue convención tipo_numero)";
                    continue;
                }

                $fileTipo   = strtoupper(trim($parts[0]));
                $fileNumero = trim($parts[1]);

                // 6) Validar que tipo_identificacion sea un string aceptable (e.g. CC, TI, CE, etc.)
                if (!preg_match('/^[A-Z]{2,3}$/', $fileTipo)) {
                    $skippedFiles[] = "$origName (tipo_identificación inválido: $fileTipo)";
                    continue;
                }

                // 7) Validar que numero_identificacion sea numérico
                if (!ctype_digit($fileNumero)) {
                    $skippedFiles[] = "$origName (número de identificación inválido: $fileNumero)";
                    continue;
                }

                // 8) Buscar el Tamizaje que coincida. Si el usuario forzó tipo/numero, debe coincidir:
                if ($forcedTipo && $forcedNumero) {
                    if ($fileTipo !== strtoupper($forcedTipo) || $fileNumero !== $forcedNumero) {
                        $skippedFiles[] = "$origName (no coincide con tipo o número forzado)";
                        continue;
                    }
                }

                $tamizaje = Tamizaje::where('tipo_identificacion', $fileTipo)
                            ->where('numero_identificacion', $fileNumero)
                            ->orderByDesc('id')
                            ->first();

                if (!$tamizaje) {
                    $skippedFiles[] = "$origName (no existe Tamizaje para $fileTipo-$fileNumero)";
                    continue;
                }

                // 9) Renombrar y mover PDF a /storage/tamizajes/{tipo}/{numero}/
                $targetDir = "tamizajes/{$fileTipo}/{$fileNumero}";
                $timestamp = now()->format('YmdHis').'_'.uniqid();
                $uniqueName = "{$fileTipo}_{$fileNumero}_{$timestamp}.pdf";

                // Realmente almacenamos el contenido del archivo
                $storedPath = Storage::disk('public')
                    ->putFileAs($targetDir, $file->getRealPath(), $uniqueName);

                if (!$storedPath) {
                    $errors[] = "$origName (error al guardar en disco)";
                    continue;
                }

                // 10) Insertar registro en tamizaje_pdfs
                TamizajePdf::create([
                    'tamizaje_id'           => $tamizaje->id,
                    'tipo_identificacion'   => $fileTipo,
                    'numero_identificacion' => $fileNumero,
                    'original_name'         => $origName,
                    'file_path'             => $storedPath,
                ]);

                $processedCount++;
            }

            DB::commit();

            // 11) Limpiar carpeta temporal (recursivamente)
            \File::deleteDirectory($extractPath);

            // 12) Preparar mensajes de feedback
            $message = "Procesados: $processedCount PDF(s).";
            if (!empty($skippedFiles)) {
                $message .= " Saltados: " . count($skippedFiles) . " archivo(s).";
            }
            if (!empty($errors)) {
                $message .= " Errores: " . count($errors) . ".";
            }

            return back()->with([
                'success'      => $message,
                'skippedFiles' => $skippedFiles,
                'errors'       => $errors,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \File::deleteDirectory($extractPath);
            return back()->with('error', 'Error al procesar ZIP: '.$e->getMessage());
        }
    }

    /**
     * Mostrar lista de PDFs de un tamizaje al hacer clic en su número de identificación
     */
    public function showPdfsByPerson($numero)
    {
        $pdfs = TamizajePdf::where('numero_identificacion', $numero)
                    ->orderByDesc('created_at')
                    ->get();

        return view('tamizajes.show_pdfs', compact('pdfs', 'numero'));
    }
}
