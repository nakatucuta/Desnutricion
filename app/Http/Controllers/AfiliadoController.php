<?php

namespace App\Http\Controllers;

use App\Models\afiliado;
use Illuminate\Http\Request;
use App\Models\vacuna;
use App\Models\batch_verifications;
use App\Imports\AfiliadoImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;


class AfiliadoController extends Controller
{
    /**
     * Muestra la vista principal con los datos de los afiliados.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // $sivigilas1 = DB::connection('sqlsrv_1')
        // ->table('users')  // Sin alias
        // ->select('*') 
        // ->get();
        // $authenticatedUser = auth()->user()->name;
        // $userInQuery = $sivigilas1->firstWhere('name', $authenticatedUser->name);


        // $sivigilas = DB::table('afiliados as b')

        //     ->select('b.id', 'b.primer_nombre', 'b.segundo_nombre', 'b.primer_apellido', 'b.segundo_apellido', 'b.numero_identificacion')
        //     ->get();


        $sivigilas = DB::table(DB::raw('afiliados AS b'))
            ->select(
                'b.id',
                'b.primer_nombre',
                'b.segundo_nombre',
                'b.primer_apellido',
                'b.segundo_apellido',
                'b.numero_identificacion',
                'c.batch_verifications_id'
            )
            ->join(DB::raw('vacunas AS c'), 'b.id', '=', 'c.afiliado_id')
            ->get(); // Usamos get() en lugar de paginate() porque DataTables manejará la paginación.
    
           
           
            $sivigilas_usernormal = DB::table('afiliados as b')

            ->select('b.id', 'b.primer_nombre', 'b.segundo_nombre', 'b.primer_apellido', 'b.segundo_apellido', 
            'b.numero_identificacion','c.batch_verifications_id')
            ->join(DB::raw('vacunas AS c'), 'b.id', '=', 'c.afiliado_id')

           ->get();
             // Usamos get() en lugar de paginate() porque DataTables manejará la paginación.
        return view('livewire.afiliado', compact('sivigilas','sivigilas_usernormal'));
    }
  
      /**
     * Muestra el formulario para importar archivos Excel.
     *
     * @return \Illuminate\View\View
     */
    public function showImportForm()
    {
        // Puedes añadir lógica aquí si necesitas un formulario separado para la importación.
        return view('livewire.import-excel');
    }


    /**
     * Importa los datos desde un archivo Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        if ($file) {
            Excel::import(new AfiliadoImport, $file);
            return redirect()->route('afiliado')->with('success', 'Datos importados correctamente');
        } else {
            return redirect()->route('afiliado')->with('error', 'Por favor, sube un archivo Excel.');
        }
    }

    /**
     * Obtiene las vacunas asociadas a un afiliado específico.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVacunas($id)
    {
        $vacunas = DB::table('vacunas as a')
            ->join('afiliados as b', 'a.afiliado_id', '=', 'b.id')
            ->where('b.id', $id)
            ->select('a.nombre as nombre_vacuna')
            ->get();

        return response()->json($vacunas);
    }

    /**
     * Elimina un registro de Batch_verification y sus afiliados y vacunas asociados.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $batchVerification = batch_verifications::findOrFail($id);
            $afiliados = afiliado::where('batch_verifications_id', $batchVerification->id)->get();

            foreach ($afiliados as $afiliado) {
                $vacunas = vacuna::where('afiliado_id', $afiliado->id)
                                 ->where('batch_verifications_id', $batchVerification->id)
                                 ->get();

                if ($vacunas->isNotEmpty()) {
                    foreach ($vacunas as $vacuna) {
                        $vacuna->delete();
                    }
                    $afiliado->delete();
                }
            }

            vacuna::where('batch_verifications_id', $batchVerification->id)->delete();
            $batchVerification->delete();

            DB::commit();

            return redirect()->route('afiliado')->with('success', 'Los registros fueron eliminados correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('afiliado')->with('error', 'Hubo un problema al eliminar los registros: ' . $e->getMessage());
        }
    }
}
