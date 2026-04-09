<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StudentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orders = $this->sampleOrders();
        $allowedStatuses = ['pending', 'preparing', 'ready', 'completed'];

        // Normalize old status values (e.g. queued -> pending).
        foreach ($orders as &$order) {
            $status = strtolower((string) ($order['status'] ?? 'pending'));
            if ($status === 'queued') {
                $status = 'pending';
            }
            if (! in_array($status, $allowedStatuses, true)) {
                $status = 'pending';
            }
            $order['status'] = $status;
        }
        unset($order);

        // Ensure at least one completed order so feedback/reply UI is visible.
        if (! collect($orders)->contains(fn ($order) => ($order['status'] ?? null) === 'completed') && isset($orders[0])) {
            $orders[0]['status'] = 'completed';
            Cache::forever('shared_order_statuses', $orders);
        }

        // Keep one order as preparing for visible in-progress status.
        if (! collect($orders)->contains(fn ($order) => ($order['status'] ?? null) === 'preparing') && isset($orders[1])) {
            $orders[1]['status'] = 'preparing';
            Cache::forever('shared_order_statuses', $orders);
        }

        // Ensure at least one ready status for visible workflow.
        if (! collect($orders)->contains(fn ($order) => ($order['status'] ?? null) === 'ready') && isset($orders[2])) {
            $orders[2]['status'] = 'ready';
            Cache::forever('shared_order_statuses', $orders);
        }

        return view('student.dashboard', [
            'orders' => $orders,
            'feedbacks' => session('student_order_feedbacks', []),
            'staffReplies' => $this->staffRepliesForStudent(
                $user?->id,
                $user?->name ?? null,
            ),
        ]);
    }

    public function profile()
    {
        return view('student.profile');
    }

    public function updateProfile(Request $request)
    {
        $request->user()->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('student.profile')->with('status', 'profile-updated');
    }

    public function submitFeedback(Request $request, string $orderId)
    {
        $order = collect($this->sampleOrders())->firstWhere('id', $orderId);

        if (! $order || $order['status'] !== 'completed') {
            return redirect()->route('student.dashboard')
                ->with('error', 'Only completed orders can submit feedback.');
        }

        $validated = $request->validate([
            'feedback' => ['required', 'string', 'min:3', 'max:300'],
        ]);

        $feedbacks = $request->session()->get('student_order_feedbacks', []);
        $feedbacks[$orderId] = $validated['feedback'];
        $request->session()->put('student_order_feedbacks', $feedbacks);

        // Shared feedback entries visible to staff dashboard.
        $sharedEntries = Cache::get('order_feedback_entries', []);
        $sharedEntries[] = [
            'order_id' => $orderId,
            'message' => $validated['feedback'],
            'from' => 'student',
            'student_id' => $request->user()?->id,
            'student_name' => $request->user()?->name ?? 'Student',
            'submitted_at' => now()->format('Y-m-d H:i'),
        ];
        Cache::forever('order_feedback_entries', $sharedEntries);

        return redirect()->route('student.dashboard')
            ->with('status', 'Feedback submitted successfully.');
    }

    private function sampleOrders(): array
    {
        return Cache::get('shared_order_statuses', [
            ['id' => 'ORD-1738828499001', 'status' => 'completed', 'canteen' => 'CEIT Main Canteen'],
            ['id' => 'ORD-1738828500234', 'status' => 'preparing', 'canteen' => 'CASS Food Hub'],
            ['id' => 'ORD-1738828500450', 'status' => 'ready', 'canteen' => 'CHEFS Dining'],
        ]);
    }

    private function staffRepliesForStudent(?int $studentId, ?string $studentName): array
    {
        $entries = Cache::get('order_feedback_entries', []);
        $replies = [];

        foreach ($entries as $entry) {
            $fromStudent = ($entry['from'] ?? null) === 'student';
            $hasReply = ! empty($entry['staff_reply']);
            $idMatches = isset($entry['student_id']) && $studentId !== null && (int) $entry['student_id'] === $studentId;
            $nameMatches = ! empty($entry['student_name']) && $studentName !== null && $entry['student_name'] === $studentName;

            if ($fromStudent && $hasReply && ($idMatches || $nameMatches)) {
                $replies[$entry['order_id']] = [
                    'message' => $entry['staff_reply'],
                    'replied_at' => $entry['replied_at'] ?? null,
                ];
            }
        }

        return $replies;
    }
}