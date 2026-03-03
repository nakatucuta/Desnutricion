<?php

namespace App\Http\Controllers;

use App\Exports\SivigilaAssignmentAuditExport;
use App\Models\SivigilaAssignmentAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SivigilaAuditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('Admin_sivigila');
    }

    public function index()
    {
        $users = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('sivigila.audit', compact('users'));
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
                    'sivigila_id' => $r->sivigila_id,
                    'fec_not' => $r->fec_not,
                    'num_ide_' => $r->num_ide_,
                    'paciente_nombre' => $r->paciente_nombre,
                    'nom_upgd' => $r->nom_upgd,
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

        $filename = 'auditoria_asignaciones_113_' . now()->format('Ymd_His') . '.' . $format;
        $export = new SivigilaAssignmentAuditExport($rows);

        if ($format === 'csv') {
            return Excel::download($export, $filename, ExcelFormat::CSV);
        }

        return Excel::download($export, $filename, ExcelFormat::XLSX);
    }

    private function baseQuery(Request $request)
    {
        $query = SivigilaAssignmentAudit::query()
            ->from('dbo.sivigila_assignment_audits as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.performed_by_user_id')
            ->select([
                'a.id',
                'a.created_at',
                'a.action_type',
                'a.sivigila_id',
                'a.fec_not',
                'a.num_ide_',
                'a.paciente_nombre',
                'a.nom_upgd',
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
        if ($request->filled('nom_upgd')) {
            $query->where('a.nom_upgd', 'like', '%' . trim((string) $request->get('nom_upgd')) . '%');
        }
        if ($request->filled('num_ide_')) {
            $query->where('a.num_ide_', 'like', '%' . trim((string) $request->get('num_ide_')) . '%');
        }

        return $query;
    }
}

