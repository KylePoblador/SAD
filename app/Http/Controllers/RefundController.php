<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'canteen_id' => ['nullable', 'string', Rule::in(array_keys(config('canteens', [])))],
            'related_transaction_type' => 'nullable|string',
            'related_transaction_id' => 'nullable|integer',
        ]);

        try {
            $staff = auth()->user();
            $student = User::findOrFail($request->student_user_id);

            $refund = $this->refundService->issueRefund(
                $staff,
                $student,
                (float) $request->amount,
                $request->reason,
                $request->related_transaction_type,
                $request->related_transaction_id ? (int) $request->related_transaction_id : null,
                $request->canteen_id
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
        if ((int) auth()->id() !== (int) $student->id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $refunds = $this->refundService->getStudentRefundHistory($student);

        return response()->json([
            'student_id' => $student->id,
            'student_name' => $student->name,
            'total_refunds' => $refunds->where('status', Refund::STATUS_REFUNDED)->sum('amount'),
            'refund_count' => $refunds->count(),
            'refunds' => $refunds->load(['staff:id,name', 'processedBy:id,name', 'order:id,order_number']),
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
        $refunds = $this->refundService->getRefundsForStaffCanteen($staff);
        $totalRefunded = $this->refundService->getTotalRefundsByStaff($staff);
        $pendingCount = $this->refundService->countPendingForStaffCanteen($staff);

        return response()->json([
            'staff_id' => $staff->id,
            'staff_name' => $staff->name,
            'total_refunded' => $totalRefunded,
            'pending_count' => $pendingCount,
            'refund_count' => $refunds->count(),
            'refunds' => $refunds,
        ]);
    }

    public function process(Request $request, Refund $refund)
    {
        $request->validate([
            'decision' => ['required', Rule::in([Refund::STATUS_REFUNDED, Refund::STATUS_REJECTED])],
            'staff_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $processed = $this->refundService->processPendingRefund(
                $refund,
                auth()->user(),
                $request->decision,
                $request->staff_notes
            );

            $student = User::find($processed->student_user_id);

            return response()->json([
                'message' => $request->decision === Refund::STATUS_REFUNDED
                    ? 'Refund approved and credited to student wallet.'
                    : 'Refund request rejected.',
                'refund' => $processed,
                'student_new_balance' => $student?->fresh()->wallet_balance,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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
