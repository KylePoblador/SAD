<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\CanteenFeedback;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use App\Models\UserCanteenBalance;
use App\Models\WalletDepositInquiry;
use App\Models\WalletLoadLog;
use App\Services\InAppNotificationFeed;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\Cache;
=======
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
>>>>>>> 980607e9b8e5596e4a05a6d50c45bece1dcc194e

class StudentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
<<<<<<< HEAD
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
=======
        $catalog = config('canteens', []);

        $staffByCollege = User::query()
            ->where('role', 'staff')
            ->whereNotNull('college')
            ->whereIn('college', array_keys($catalog))
            ->get()
            ->groupBy('college');

        $occupiedMap = DB::table('seat_reservations')
            ->select('college', DB::raw('COUNT(*) as occupied_count'))
            ->groupBy('college')
            ->pluck('occupied_count', 'college');

        $totalSeats = 25;
        $canteens = [];

        foreach ($catalog as $college => $canteenInfo) {
            $staffCollection = $staffByCollege[$college] ?? collect();
            if ($staffCollection->isEmpty()) {
                continue;
            }

            $occupied = (int) ($occupiedMap[$college] ?? 0);
            $available = max($totalSeats - $occupied, 0);

            $staffNames = $staffCollection->pluck('name')->filter()->values()->all();

            $staffLabel = count($staffNames) > 2
                ? $staffNames[0] . ', ' . $staffNames[1] . ' +' . (count($staffNames) - 2) . ' more'
                : implode(', ', $staffNames);

            $canteens[] = [
                'name' => $canteenInfo['label'],
                'college' => $college,
                'dist' => $canteenInfo['dist'],
                'rating' => CanteenFeedback::averageRatingForCollege($college),
                'seats' => $available . '/' . $totalSeats,
                'full' => $available === 0,
                'staff_names' => $staffLabel,
                'staff_count' => $staffCollection->count(),
            ];
        }

        $orders = Order::where('user_id', $user->id)->get();
        $activeStatuses = ['pending', 'preparing', 'ready'];
        $activeOrdersCount = $orders->whereIn('status', $activeStatuses)->count();
        $totalOrdersCount = $orders->count();

        $activeOrder = Order::where('user_id', $user->id)
            ->whereIn('status', $activeStatuses)
            ->orderByDesc('created_at')
            ->first();

        return view('student.dashboard', [
            'canteens' => $canteens,
            'activeOrdersCount' => $activeOrdersCount,
            'totalOrdersCount' => $totalOrdersCount,
            'activeOrder' => $activeOrder,
        ]);
    }

    public function showCanteen(string $college)
    {
        $catalog = config('canteens', []);
        if (! array_key_exists($college, $catalog)) {
            abort(404);
        }

        $totalSeats = 25;
        $occupiedCount = DB::table('seat_reservations')
            ->where('college', $college)
            ->count();
        $availableSeats = max($totalSeats - $occupiedCount, 0);
        $reservedSeat = DB::table('seat_reservations')
            ->where('college', $college)
            ->where('user_id', auth()->id())
            ->value('seat_number');

        $menuItems = MenuItem::query()
            ->where('college', $college)
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('student.canteen.show', [
            'college' => $college,
            'canteenName' => $catalog[$college]['label'],
            'totalSeats' => $totalSeats,
            'occupiedCount' => $occupiedCount,
            'availableSeats' => $availableSeats,
            'reservedSeat' => $reservedSeat,
            'hasReservedSeat' => $reservedSeat !== null,
            'menuItems' => $menuItems,
            'walletBalance' => UserCanteenBalance::balanceFor((int) auth()->id(), $college),
>>>>>>> 980607e9b8e5596e4a05a6d50c45bece1dcc194e
        ]);
    }

    public function profile()
    {
        return view('student.profile', [
            'canteenCatalog' => config('canteens', []),
        ]);
    }

    public function notifications()
    {
        return view('student.notification');
    }

    public function notificationData()
    {
        $userId = auth()->id();
        $notifications = InAppNotificationFeed::studentItems($userId);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => InAppNotificationFeed::studentUnreadCount($userId),
        ]);
    }

    public function notificationStream()
    {
        return response()->stream(function () {
            while (true) {
                $userId = auth()->id();
                $notifications = InAppNotificationFeed::studentItems($userId);
                $unreadCount = InAppNotificationFeed::studentUnreadCount($userId);

                echo 'data: '.json_encode([
                    'notifications' => $notifications,
                    'unread_count' => $unreadCount,
                ])."\n\n";

                ob_flush();
                flush();
                sleep(3);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }

    public function markNotificationRead(Request $request)
    {
        $validated = $request->validate([
            'nid' => ['required', 'string', 'regex:/^(o|a):[1-9][0-9]*$/'],
        ]);

        InAppNotificationFeed::markStudentRead(auth()->id(), $validated['nid']);

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead()
    {
        InAppNotificationFeed::markAllStudentRead((int) auth()->id());

        return response()->json([
            'success' => true,
            'notifications' => InAppNotificationFeed::studentItems((int) auth()->id()),
            'unread_count' => InAppNotificationFeed::studentUnreadCount((int) auth()->id()),
        ]);
    }

    public function clearAllNotifications()
    {
        InAppNotificationFeed::clearStudentNotificationFeed((int) auth()->id());

        return response()->json([
            'success' => true,
            'notifications' => InAppNotificationFeed::studentItems((int) auth()->id()),
            'unread_count' => InAppNotificationFeed::studentUnreadCount((int) auth()->id()),
        ]);
    }

    public function unreadNotificationCount()
    {
        return response()->json([
            'unread_count' => InAppNotificationFeed::studentUnreadCount(auth()->id()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        if ($request->input('college') === '') {
            $request->merge(['college' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'college' => ['nullable', 'string', Rule::in(array_keys(config('canteens', [])))],
            'avatar' => ['nullable', 'image', 'max:5000'],
        ]);

        $user = $request->user();
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'college' => $validated['college'],
        ];

        $avatarPath = $this->storePublicUserAvatar($request, $user);
        if ($avatarPath !== null) {
            $data['avatar_path'] = $avatarPath;
        }

        $user->update($data);

        return redirect()->route('student.profile')->with('status', 'profile-updated');
    }

<<<<<<< HEAD
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
        $studentId = $request->user()?->id;
        $studentName = $request->user()?->name ?? 'Student';
        $updated = false;

        foreach ($sharedEntries as $index => $entry) {
            $sameOrder = ($entry['order_id'] ?? null) === $orderId;
            $fromStudent = ($entry['from'] ?? null) === 'student';
            $idMatches = isset($entry['student_id']) && $studentId !== null && (int) $entry['student_id'] === $studentId;
            $nameMatches = ! empty($entry['student_name']) && $entry['student_name'] === $studentName;

            if ($sameOrder && $fromStudent && ($idMatches || $nameMatches)) {
                $sharedEntries[$index] = [
                    ...$entry,
                    'message' => $validated['feedback'],
                    'student_id' => $studentId,
                    'student_name' => $studentName,
                    'submitted_at' => now()->format('Y-m-d H:i'),
                    'staff_reply' => null,
                    'replied_at' => null,
                ];
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            $sharedEntries[] = [
                'order_id' => $orderId,
                'message' => $validated['feedback'],
                'from' => 'student',
                'student_id' => $studentId,
                'student_name' => $studentName,
                'submitted_at' => now()->format('Y-m-d H:i'),
            ];
        }
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
=======
    public function orders()
    {
        $labels = collect(config('canteens', []))->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);

        $orders = Order::where('user_id', auth()->id())
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) use ($labels) {
                $cid = $order->canteen_id;
                $order->canteen = $labels[$cid] ?? (is_string($cid) ? strtoupper($cid) : 'Canteen');

                return $order;
            });

        $walletBalance = UserCanteenBalance::totalForUser((int) auth()->id());

        return view('student.order', [
            'orders' => $orders,
            'walletBalance' => $walletBalance,
            'activeOrdersCount' => $orders->whereIn('status', ['pending', 'preparing', 'ready'])->count(),
            'totalOrdersCount' => $orders->count(),
        ]);
    }

    public function wallet()
    {
        $user = auth()->user()->fresh();

        $orders = Order::where('user_id', $user->id)->get();

        $totalSpent = $orders->sum('total');
        $totalOrders = $orders->count();

        $recentTransactions = $orders->sortByDesc('created_at')
            ->take(10)
            ->map(function ($order) {
                return [
                    'description' => 'Order ' . $order->order_number,
                    'amount' => $order->total,
                    'type' => 'debit',
                    'date' => $order->created_at->format('M d, Y H:i'),
                ];
            })
            ->values()
            ->toArray();

        $collegeLabel = null;
        if ($user->college && isset(config('canteens')[$user->college])) {
            $collegeLabel = config('canteens')[$user->college]['label'];
        }

        $catalog = config('canteens', []);
        $topUpSlugs = collect($this->topUpCanteenOptions())->pluck('slug')->all();
        $extraSlugs = UserCanteenBalance::query()
            ->where('user_id', $user->id)
            ->where('balance', '>', 0)
            ->pluck('college')
            ->all();
        $allSlugs = collect($topUpSlugs)->merge($extraSlugs)->unique()->sort()->values()->all();

        $canteenBalances = [];
        foreach ($allSlugs as $slug) {
            if (! isset($catalog[$slug])) {
                continue;
            }
            $canteenBalances[] = [
                'slug' => $slug,
                'label' => $catalog[$slug]['label'],
                'balance' => UserCanteenBalance::balanceFor((int) $user->id, $slug),
            ];
        }

        $totalBalance = UserCanteenBalance::totalForUser((int) $user->id);

        $wallet = [
            'balance' => $totalBalance,
            'college' => $collegeLabel,
            'canteen_balances' => $canteenBalances,
            'total_spent' => $totalSpent,
            'total_orders' => $totalOrders,
            'recent_transactions' => $recentTransactions,
        ];

        return view('student.wallet', [
            'wallet' => $wallet,
            'topUpCanteens' => $this->topUpCanteenOptions(),
        ]);
    }

    /**
     * Canteens that have at least one staff account (same rule as student browse list).
     *
     * @return array<int, array{slug: string, label: string, dist: string}>
     */
    protected function topUpCanteenOptions(): array
    {
        $catalog = config('canteens', []);
        $staffColleges = User::query()
            ->where('role', 'staff')
            ->whereNotNull('college')
            ->pluck('college')
            ->map(fn ($c) => strtolower(trim((string) $c)))
            ->unique()
            ->values()
            ->all();

        $options = [];
        foreach ($staffColleges as $slug) {
            if (! isset($catalog[$slug])) {
                continue;
            }
            $options[] = [
                'slug' => $slug,
                'label' => $catalog[$slug]['label'],
                'dist' => $catalog[$slug]['dist'] ?? '',
            ];
        }
        usort($options, fn ($a, $b) => strcmp($a['label'], $b['label']));

        return $options;
    }

    public function storeWalletDepositInquiry(Request $request)
    {
        $allowedSlugs = collect($this->topUpCanteenOptions())->pluck('slug')->all();

        $validated = $request->validate([
            'college' => ['required', 'string', Rule::in($allowedSlugs)],
            'intended_amount' => ['nullable', 'numeric', 'min:1', 'max:999999.99'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $inquiry = WalletDepositInquiry::create([
            'user_id' => $request->user()->id,
            'college' => strtolower($validated['college']),
            'intended_amount' => isset($validated['intended_amount']) ? $validated['intended_amount'] : null,
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        $label = config('canteens')[$validated['college']]['label'] ?? $validated['college'];
        $student = $request->user();

        $studentBody = 'Canteen: '.$label.'. ';
        if ($inquiry->intended_amount !== null) {
            $studentBody .= 'Planned amount: ₱'.number_format((float) $inquiry->intended_amount, 2).'. ';
        }
        $studentBody .= 'Go to the counter and pay cash — staff can see your inquiry.';

        ActivityNotification::notifyUser(
            $student->id,
            ActivityNotification::TYPE_DEPOSIT_INQUIRY_STUDENT,
            'Wallet top-up request sent',
            $studentBody,
            $inquiry->id
        );

        $staffBody = $student->name.' requested a wallet deposit.';
        if ($inquiry->intended_amount !== null) {
            $staffBody .= ' Planned: ₱'.number_format((float) $inquiry->intended_amount, 2).'.';
        }
        if (! empty($inquiry->note)) {
            $staffBody .= ' Note: '.$inquiry->note;
        }

        ActivityNotification::notifyStaffOfCollege(
            strtolower($validated['college']),
            ActivityNotification::TYPE_DEPOSIT_INQUIRY_STAFF,
            'New wallet deposit inquiry',
            $staffBody,
            $inquiry->id
        );

        return redirect()
            ->route('student.wallet')
            ->with('status', 'deposit-inquiry-sent')
            ->with('deposit_target', $label)
            ->with('deposit_college_slug', $validated['college']);
    }

    public function updateWalletBalance(Request $request, $studentId)
    {
        $user = auth()->user();

        if ($user->role !== 'staff') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = User::find($studentId);

        if (! $student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $staffCollege = $user->college ? strtolower(trim((string) $user->college)) : 'ceit';
        $studentCollege = strtolower(trim((string) ($student->college ?? '')));

        $assignedToCanteen = $studentCollege === $staffCollege;
        $hasPendingInquiry = WalletDepositInquiry::query()
            ->where('user_id', $student->id)
            ->whereRaw('LOWER(TRIM(college)) = ?', [$staffCollege])
            ->where('status', 'pending')
            ->exists();

        if (! $assignedToCanteen && ! $hasPendingInquiry) {
            return response()->json([
                'error' => 'This student has no pending deposit request for your canteen. They can submit a new request from Wallet.',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $amount = (float) $validated['amount'];

        $newCanteenBalance = UserCanteenBalance::add((int) $student->id, $staffCollege, $amount);
        $student->refresh();

        WalletLoadLog::query()->create([
            'student_user_id' => $student->id,
            'staff_user_id' => $user->id,
            'college' => $staffCollege,
            'amount' => $amount,
        ]);

        $closedCount = WalletDepositInquiry::query()
            ->where('user_id', $student->id)
            ->whereRaw('LOWER(TRIM(college)) = ?', [$staffCollege])
            ->where('status', 'pending')
            ->update(['status' => 'done']);

        $canteenLabel = config('canteens')[$staffCollege]['label'] ?? $staffCollege;
        $totalBalance = UserCanteenBalance::totalForUser((int) $student->id);

        if ($closedCount > 0) {
            ActivityNotification::notifyUser(
                $student->id,
                ActivityNotification::TYPE_DEPOSIT_INQUIRY_DONE,
                'Deposit inquiry completed',
                '₱'.number_format($amount, 2).' was added to your '.$canteenLabel.' wallet. Balance there: ₱'.number_format($newCanteenBalance, 2).'. Total all canteens: ₱'.number_format($totalBalance, 2).'.',
                null
            );
        }

        ActivityNotification::notifyUser(
            $student->id,
            ActivityNotification::TYPE_WALLET_LOADED,
            'Wallet loaded',
            $user->name.' added ₱'.number_format($amount, 2).' to your '.$canteenLabel.' wallet. '.$canteenLabel.': ₱'.number_format($newCanteenBalance, 2).' · Total: ₱'.number_format($totalBalance, 2).'.',
            null
        );

        return response()->json([
            'success' => true,
            'new_balance' => $totalBalance,
            'new_canteen_balance' => $newCanteenBalance,
            'canteen' => $staffCollege,
            'inquiries_closed' => $closedCount,
            'message' => 'Wallet updated successfully',
        ]);
    }
}
>>>>>>> 980607e9b8e5596e4a05a6d50c45bece1dcc194e
