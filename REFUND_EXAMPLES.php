<?php

/**
 * REFUND SYSTEM - PRACTICAL EXAMPLES
 *
 * This file demonstrates how to use the refund system in your Laravel app
 */

// ============================================================================
// EXAMPLE 1: ISSUE REFUND VIA CONTROLLER (HTTP)
// ============================================================================

// Staff makes POST request to /staff/refunds
$request = [
    'student_user_id' => 123,
    'amount' => 500.00,
    'reason' => 'Student was charged twice for wallet load',
    'related_transaction_type' => 'WalletLoadLog',
    'related_transaction_id' => 789,
];

// Response:
$response = [
    'message' => 'Refund processed successfully',
    'refund' => [
        'id' => 1,
        'staff_user_id' => 2,
        'student_user_id' => 123,
        'amount' => '500.00',
        'reason' => 'Student was charged twice for wallet load',
        'refunded_at' => '2026-05-04T23:26:29Z',
    ],
    'student_new_balance' => '1500.00',  // Updated wallet
];

// ============================================================================
// EXAMPLE 2: PROGRAMMATIC REFUND (IN CODE)
// ============================================================================

use App\Services\RefundService;
use App\Models\User;

$refundService = app(RefundService::class);

$staff = User::where('role', 'staff')->first();
$student = User::find(123);

$refund = $refundService->issueRefund(
    staff: $staff,
    student: $student,
    amount: 500.00,
    reason: 'Refunding double charge',
    transactionType: 'WalletLoadLog',
    transactionId: 789
);

echo "Refund ID: " . $refund->id;
echo "Student new balance: " . $student->fresh()->wallet_balance;

// ============================================================================
// EXAMPLE 3: CHECKING REFUND HISTORY
// ============================================================================

// As a staff member - see all refunds you issued
$myRefunds = $refundService->getStaffRefundHistory($staff);

foreach ($myRefunds as $refund) {
    echo "Refunded " . $refund->student->name . " ₱" . $refund->amount
         . " for: " . $refund->reason;
}

// As a student - see all refunds you received
$myRefunds = $refundService->getStudentRefundHistory($student);

foreach ($myRefunds as $refund) {
    echo "Refunded by " . $refund->staff->name . " ₱" . $refund->amount
         . " - Reason: " . $refund->reason;
}

// ============================================================================
// EXAMPLE 4: AUDIT - CHECK TOTAL REFUNDED BY STAFF
// ============================================================================

$totalRefunded = $refundService->getTotalRefundsByStaff($staff);
echo "Staff has issued ₱" . $totalRefunded . " in total refunds";

// ============================================================================
// EXAMPLE 5: QUERY REFUNDS DIRECTLY
// ============================================================================

use App\Models\Refund;

// Get all refunds from a specific date
$refunds = Refund::where('created_at', '>=', now()->subDays(7))
    ->with(['staff', 'student'])
    ->get();

// Get refunds for a specific student
$studentRefunds = Refund::where('student_user_id', 123)
    ->orderByDesc('created_at')
    ->get();

// Get refunds linked to a specific transaction
$linkedRefunds = Refund::where('related_transaction_type', 'WalletLoadLog')
    ->where('related_transaction_id', 789)
    ->get();

// ============================================================================
// EXAMPLE 6: REAL-WORLD SCENARIO - RESOLVING DOUBLE CHARGE
// ============================================================================

/**
 * Scenario: Student reports being charged twice for ₱500 wallet load
 *
 * 1. Staff finds the duplicate wallet load logs
 * 2. Issues refund for one of them
 * 3. System records it for auditing
 */

// Find duplicate transactions
$duplicateLoads = \App\Models\WalletLoadLog::where('student_user_id', 123)
    ->where('amount', 500)
    ->orderByDesc('created_at')
    ->get();

if ($duplicateLoads->count() > 1) {
    // Issue refund for the duplicate
    $refund = $refundService->issueRefund(
        staff: auth()->user(),
        student: $student,
        amount: 500.00,
        reason: 'Refunding duplicate wallet load transaction',
        transactionType: 'WalletLoadLog',
        transactionId: $duplicateLoads->last()->id  // Reference first transaction
    );

    echo "Refund processed. Student balance updated to: " . $student->fresh()->wallet_balance;
}

// ============================================================================
// EXAMPLE 7: VALIDATION EXAMPLES (WHAT DOESN'T WORK)
// ============================================================================

try {
    // ❌ FAILS: Non-staff user trying to refund
    $student = User::where('role', 'student')->first();
    $refundService->issueRefund($student, $otherStudent, 100, 'reason');
    // Throws: InvalidArgumentException: Only staff members can issue refunds

} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
}

try {
    // ❌ FAILS: Invalid amount
    $refundService->issueRefund($staff, $student, -100, 'reason');
    // Throws: InvalidArgumentException: Refund amount must be greater than 0

} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
}

try {
    // ❌ FAILS: Zero amount
    $refundService->issueRefund($staff, $student, 0, 'reason');
    // Throws: InvalidArgumentException: Refund amount must be greater than 0

} catch (\InvalidArgumentException $e) {
    echo $e->getMessage();
}

// ============================================================================
// EXAMPLE 8: API ENDPOINT RESPONSES
// ============================================================================

// POST /staff/refunds - Success
// HTTP 201 Created
$successResponse = [
    'message' => 'Refund processed successfully',
    'refund' => [
        'id' => 1,
        'staff_user_id' => 2,
        'student_user_id' => 123,
        'amount' => '500.00',
        'reason' => 'Student charged twice',
        'refunded_at' => '2026-05-04T23:26:29Z',
        'created_at' => '2026-05-04T23:26:29Z',
        'updated_at' => '2026-05-04T23:26:29Z',
    ],
    'student_new_balance' => '1500.00',
];

// POST /staff/refunds - Validation Error
// HTTP 422 Unprocessable Entity
$validationErrorResponse = [
    'message' => 'Only staff members can issue refunds',
];

// GET /staff/refunds/history - Success
// HTTP 200 OK
$historyResponse = [
    'staff_id' => 2,
    'staff_name' => 'John Doe',
    'total_refunded' => '1500.00',
    'refund_count' => 3,
    'refunds' => [
        [
            'id' => 1,
            'student_user_id' => 123,
            'student' => ['id' => 123, 'name' => 'Jane Smith'],
            'amount' => '500.00',
            'reason' => 'Double charge',
            'refunded_at' => '2026-05-04T23:26:29Z',
        ],
    ],
];

// GET /student/refunds/history - Success
// HTTP 200 OK
$studentHistoryResponse = [
    'student_id' => 123,
    'student_name' => 'Jane Smith',
    'total_refunds' => '500.00',
    'refund_count' => 1,
    'refunds' => [
        [
            'id' => 1,
            'staff_user_id' => 2,
            'staff' => ['id' => 2, 'name' => 'John Doe'],
            'amount' => '500.00',
            'reason' => 'Double charge',
            'refunded_at' => '2026-05-04T23:26:29Z',
        ],
    ],
];

// ============================================================================
// EXAMPLE 9: TESTING THE REFUND SYSTEM
// ============================================================================

/**
 * In tests/Feature/RefundTest.php or similar:
 */

class RefundTest extends TestCase
{
    public function test_staff_can_issue_refund()
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $student = User::factory()->create(['role' => 'student', 'wallet_balance' => 1000]);

        $this->actingAs($staff)->post('/staff/refunds', [
            'student_user_id' => $student->id,
            'amount' => 500,
            'reason' => 'Test refund',
        ])->assertStatus(201);

        $this->assertEquals(1500, $student->fresh()->wallet_balance);
    }

    public function test_student_cannot_issue_refund()
    {
        $student = User::factory()->create(['role' => 'student']);
        $other = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)->post('/staff/refunds', [
            'student_user_id' => $other->id,
            'amount' => 500,
            'reason' => 'Unauthorized',
        ])->assertStatus(422);
    }
}

// ============================================================================
// EXAMPLE 10: MIGRATION ROLLBACK (IF NEEDED)
// ============================================================================

// To rollback the refund feature:
// php artisan migrate:rollback --step=1

// This will:
// - Drop the refunds table
// - Remove all refund records (be careful!)

?>
