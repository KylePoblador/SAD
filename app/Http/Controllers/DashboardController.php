<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        return view('staff.dashboard', [
            'todayOrders'    => 3,
            'revenue'        => 285,
            'availableSeats' => 7,
            'totalSeats'     => 50,
            'rating'         => 4.5,
            'recentOrders'   => [],
        ]);
    }

    public function profile()
    {
        return view('staff.profile');
    }

    public function updateProfile(Request $request)
    {
        $request->user()->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('staff.profile')->with('status', 'profile-updated');
    }

    public function orders()    { return view('staff.orders'); }
    public function menu()      { return view('staff.menu'); }
    public function wallet()    { return view('staff.wallet'); }
    public function seats()     { return view('staff.seats'); }
    public function feedbacks()
    {
        $sharedEntries = Cache::get('order_feedback_entries', []);

        return view('Staff.feedbacks', [
            'feedbacks' => ! empty($sharedEntries) ? $sharedEntries : [
                ['order_id' => 'ORD-1018', 'message' => 'Fast serving and food was hot. Thank you!', 'from' => 'student', 'student_name' => 'Student', 'submitted_at' => now()->format('Y-m-d H:i')],
                ['order_id' => 'ORD-1027', 'message' => 'Good portion size. Please add more spoon stocks.', 'from' => 'student', 'student_name' => 'Student', 'submitted_at' => now()->format('Y-m-d H:i')],
                ['order_id' => 'ORD-1030', 'message' => 'Great taste and clean packaging.', 'from' => 'student', 'student_name' => 'Student', 'submitted_at' => now()->format('Y-m-d H:i')],
            ],
        ]);
    }

    public function replyFeedback(Request $request, int $feedbackIndex)
    {
        $entries = Cache::get('order_feedback_entries', []);

        if (! isset($entries[$feedbackIndex])) {
            return redirect()->route('staff.feedbacks')
                ->with('error', 'Feedback entry not found.');
        }

        if (($entries[$feedbackIndex]['from'] ?? 'student') !== 'student') {
            return redirect()->route('staff.feedbacks')
                ->with('error', 'You can only reply to student feedback.');
        }

        $validated = $request->validate([
            'reply' => ['required', 'string', 'min:2', 'max:300'],
        ]);

        $entries[$feedbackIndex] = [
            ...$entries[$feedbackIndex],
            'staff_reply' => $validated['reply'],
            'replied_at' => now()->format('Y-m-d H:i'),
        ];
        Cache::forever('order_feedback_entries', $entries);

        return redirect()->route('staff.feedbacks')->with('status', 'Reply sent to student feedback.');
    }

    public function reports()   { return view('staff.reports'); }
}