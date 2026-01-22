<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        $actions = [
            AuditLog::ACTION_LOGIN,
            AuditLog::ACTION_LOGOUT,
            AuditLog::ACTION_CREATE,
            AuditLog::ACTION_UPDATE,
            AuditLog::ACTION_DELETE,
            AuditLog::ACTION_SUBMIT,
            AuditLog::ACTION_APPROVE,
            AuditLog::ACTION_REJECT,
            AuditLog::ACTION_PAYMENT,
            AuditLog::ACTION_FLAG,
        ];

        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.audit-logs.index', compact('logs', 'actions', 'users'));
    }

    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        
        return view('admin.audit-logs.show', compact('auditLog'));
    }
}
