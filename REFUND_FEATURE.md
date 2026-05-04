# Refund Feature Documentation

## Overview

The refund system allows staff members to issue refunds to students when mistakes occur or transactions need to be reversed. All refunds are recorded in the `refunds` table for auditing purposes.

## How It Works

### 1. **Issuing a Refund**

Staff members can issue refunds through the API endpoint:

```
POST /staff/refunds
```

**Request Body:**

```json
{
    "student_user_id": 123,
    "amount": 500.0,
    "reason": "Student charged twice for wallet load",
    "related_transaction_type": "WalletLoadLog",
    "related_transaction_id": 456
}
```

**Response:**

```json
{
    "message": "Refund processed successfully",
    "refund": {
        "id": 1,
        "staff_user_id": 2,
        "student_user_id": 123,
        "amount": 500.0,
        "reason": "Student charged twice for wallet load",
        "related_transaction_type": "WalletLoadLog",
        "related_transaction_id": 456,
        "refunded_at": "2026-05-04T23:26:29Z",
        "created_at": "2026-05-04T23:26:29Z"
    },
    "student_new_balance": 1500.0
}
```

### 2. **Refund Database Schema**

The `refunds` table stores:

- `id` - Unique refund identifier
- `staff_user_id` - Staff member who issued the refund
- `student_user_id` - Student receiving the refund
- `amount` - Refund amount
- `reason` - Reason for refund (required for documentation)
- `related_transaction_type` - Type of original transaction (e.g., WalletLoadLog, CoinTransfer)
- `related_transaction_id` - ID of the original transaction being refunded
- `refunded_at` - When the refund was processed
- `created_at`, `updated_at` - Timestamps

## API Endpoints

### Staff Endpoints

1. **Issue Refund**

    ```
    POST /staff/refunds
    ```

    - Only staff members can access
    - Parameters: student_user_id, amount, reason, related_transaction_type, related_transaction_id

2. **View Refund History**
    ```
    GET /staff/refunds/history
    ```

    - Shows all refunds issued by the authenticated staff member
    - Includes student names and details

### Student Endpoints

1. **View Refund History**
    ```
    GET /student/refunds/history
    ```

    - Shows all refunds received by the authenticated student
    - Includes staff names and refund details

### Admin Endpoints

1. **View All Refunds**
    ```
    GET /admin/refunds
    ```

    - Shows all refunds across the system (paginated)
    - For auditing purposes

## Transaction Flow

1. **Staff identifies error** (e.g., double charge, wrong amount)
2. **Staff calls refund endpoint** with:
    - Student ID
    - Amount to refund
    - Reason for refund
    - Reference to original transaction (optional)
3. **System processes refund**:
    - Creates refund record
    - Adds amount back to student's wallet_balance
4. **Refund is recorded** for audit trail

## Example Use Cases

### Case 1: Double Wallet Load

- Student was charged ₱500 twice for wallet load
- Staff issues ₱500 refund with reason "Duplicate wallet load"
- Student's wallet is credited back

### Case 2: Wrong Amount Charged

- Student was charged ₱1000 instead of ₱500
- Staff issues ₱500 refund with reason "Overcharge correction"
- Student's wallet is credited back

### Case 3: Cancelled Transaction

- Student requested cancellation
- Staff issues full refund with reason "Student requested cancellation"
- Student's wallet is credited back

## Security

- ✅ Only staff members (`role == 'staff'`) can issue refunds
- ✅ All refunds are recorded with staff member who issued them
- ✅ Amount validation (must be > 0)
- ✅ Related transaction tracking for accountability
- ✅ Audit trail through timestamps

## Installation & Migration

1. Run migrations:

    ```bash
    php artisan migrate
    ```

2. The refund table will be created with indexes on:
    - staff_user_id + created_at
    - student_user_id + created_at
    - related_transaction_type + related_transaction_id

## Models

### Refund Model

```php
use App\Models\Refund;

// Get a refund
$refund = Refund::find(1);

// Get related staff member
$staff = $refund->staff;

// Get related student
$student = $refund->student;

// List refunds for a student
$studentRefunds = Refund::where('student_user_id', $studentId)->get();

// List refunds by staff
$staffRefunds = Refund::where('staff_user_id', $staffId)->get();
```

## Service Class

Use `RefundService` for programmatic refunds:

```php
use App\Services\RefundService;

$refundService = app(RefundService::class);

// Issue refund
$refund = $refundService->issueRefund(
    $staff,           // User object with role='staff'
    $student,         // User object
    500.00,           // Amount
    'Reason here',    // Reason
    'WalletLoadLog',  // Optional: transaction type
    456               // Optional: transaction ID
);

// Get refund history
$studentHistory = $refundService->getStudentRefundHistory($student);
$staffHistory = $refundService->getStaffRefundHistory($staff);

// Get total refunded by staff
$total = $refundService->getTotalRefundsByStaff($staff);
```

## Notes

- Refunds are immediate and atomic (all-or-nothing)
- Student wallet balance is updated in real-time
- All refunds are permanent records (no deletion/reversal)
- If a refund needs to be corrected, create a new refund in opposite direction
