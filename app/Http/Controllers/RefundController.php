<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    protected RefundService $refundService;

    public function __construct(RefundService $refundService)
    {
        $this->refundService = $refundService;
    }

    /**
     * Issue a refund to a student
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'related_transaction_type' => 'nullable|string',
            'related_transaction_id' => 'nullable|integer',
        ]);

        try {
            $staff = auth()->user();
            $student = User::findOrFail($request->student_user_id);

            $refund = $this->refundService->issueRefund(
                $staff,
                $student,
                $request->amount,
                $request->reason,
                $request->related_transaction_type,
                $request->related_transaction_id
            );

            return response()->json([
                'message' => 'Refund processed successfully',
                'refund' => $refund,
                'student_new_balance' => $student->fresh()->wallet_balance,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get refund history for a student
     */
    public function studentHistory($studentId)
    {
        $student = User::findOrFail($studentId);
        $refunds = $this->refundService->getStudentRefundHistory($student);

        return response()->json([
            'student_id' => $student->id,
            'student_name' => $student->name,
            'total_refunds' => $refunds->sum('amount'),
            'refund_count' => count($refunds),
            'refunds' => $refunds->load(['staff:id,name']),
        ]);
    }

    /**
     * Show refund history page for student
     */
    public function studentPage()
    {
        return view('student.refunds');
    }

    /**
     * Show refund management page for staff
     */
    public function staffPage()
    {
        return view('Staff.refunds_fixed');
    }

    /**
     * Get all students for autocomplete (API)
     */
    public function getStudents()
    {
        $students = User::where('role', 'student')
            ->select('id', 'name', 'email', 'student_id', 'college')
            ->orderBy('name')
            ->get();

        return response()->json($students);
    }

    /**
     * Get refunds issued by staff
     */
    public function staffHistory()
    {
        $staff = auth()->user();
        $refunds = $this->refundService->getStaffRefundHistory($staff);
        $totalRefunded = $this->refundService->getTotalRefundsByStaff($staff);

        return response()->json([
            'staff_id' => $staff->id,
            'staff_name' => $staff->name,
            'total_refunded' => $totalRefunded,
            'refund_count' => count($refunds),
            'refunds' => $refunds->load(['student:id,name']),
        ]);
    }

    /**
     * Get all refunds (admin only)
     */
    public function index()
    {
        $refunds = Refund::with(['staff:id,name', 'student:id,name'])
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json($refunds);
    }
}
