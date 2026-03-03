<?php

namespace App\Http\Controllers;

use App\Exports\Cargue412AssignmentAuditExport;
use App\Models\Cargue412AssignmentAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Yajra\DataTables\Facades\DataTables;

class Cargue412AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('Admin_seguimiento');
    }

    public function index()
    {
        $users = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('new_412.audit', compact('users'));
    }

    public function data(Request $request)
    {
        $query = $this->baseQuery($request);

        return DataTables::of($query)
            ->skipTotalRecords()
            ->toJson();
    }

    public function export(Request $request)
    {
        $format = strtolower((string) $request->get('format', 'xlsx'));
        if (!in_array($format, ['xlsx', 'csv'])) {
            $format = 'xlsx';
        }

        $rows = $this->baseQuery($request)
            ->orderByDesc('a.id')
            ->limit(100000)
            ->get()
            ->map(function ($r) {
                return [
                    'created_at' => $r->created_at,
                    'action_type' => $r->action_type,
                    'cargue412_id' => $r->cargue412_id,
                    'numero_identificacion' => $r->numero_identificacion,
                    'paciente_nombre' => $r->paciente_nombre,
                    'municipio' => $r->municipio,
                    'fecha_captacion' => $r->fecha_captacion,
                    'old_assigned_user_id' => $r->old_assigned_user_id,
                    'old_assigned_name' => $r->old_assigned_name,
                    'old_assigned_code' => $r->old_assigned_code,
                    'new_assigned_user_id' => $r->new_assigned_user_id,
                    'new_assigned_name' => $r->new_assigned_name,
                    'new_assigned_code' => $r->new_assigned_code,
                    'performed_by_name' => $r->performed_by_name,
                    'ip_address' => $r->ip_address,
                ];
            });

        $filename = 'auditoria_asignaciones_412_' . now()->format('Ymd_His') . '.' . $format;
        $export = new Cargue412AssignmentAuditExport($rows);

        if ($format === 'csv') {
            return Excel::download($export, $filename, ExcelFormat::CSV);
        }

        return Excel::download($export, $filename, ExcelFormat::XLSX);
    }

    private function baseQuery(Request $request)
    {
        $query = Cargue412AssignmentAudit::query()
            ->from('dbo.cargue412_assignment_audits as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.performed_by_user_id')
            ->select([
                'a.id',
                'a.created_at',
                'a.action_type',
                'a.cargue412_id',
                'a.numero_identificacion',
                'a.paciente_nombre',
                'a.municipio',
                'a.fecha_captacion',
                'a.old_assigned_user_id',
                'a.old_assigned_name',
                'a.old_assigned_code',
                'a.new_assigned_user_id',
                'a.new_assigned_name',
                'a.new_assigned_code',
                'a.ip_address',
                'u.name as performed_by_name',
            ]);

        if ($request->filled('from')) {
            $query->whereDate('a.created_at', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('a.created_at', '<=', $request->get('to'));
        }
        if ($request->filled('performed_by_user_id')) {
            $query->where('a.performed_by_user_id', (int) $request->get('performed_by_user_id'));
        }
        if ($request->filled('new_assigned_user_id')) {
            $query->where('a.new_assigned_user_id', (int) $request->get('new_assigned_user_id'));
        }
        if ($request->filled('action_type')) {
            $query->where('a.action_type', $request->get('action_type'));
        }
        if ($request->filled('municipio')) {
            $query->where('a.municipio', 'like', '%' . trim((string) $request->get('municipio')) . '%');
        }
        if ($request->filled('numero_identificacion')) {
            $query->where('a.numero_identificacion', 'like', '%' . trim((string) $request->get('numero_identificacion')) . '%');
        }

        return $query;
    }
}
