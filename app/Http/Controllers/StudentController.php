<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\CanteenFeedback;
use App\Models\CoinTransfer;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;


use App\Models\SeatLayout;
use App\Models\SeatReservation;
use App\Models\User;
use App\Models\UserCanteenBalance;
use App\Models\WalletLoadLog;
use App\Models\WalletLoadQrToken;
use App\Services\InAppNotificationFeed;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = strtolower(trim((string) ($user->role ?? 'student')));
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role === 'staff') {
            return redirect()->route('staff.dashboard');
        }
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

        $canteens = [];

        foreach ($catalog as $college => $canteenInfo) {
            $staffCollection = $staffByCollege[$college] ?? collect();
            if ($staffCollection->isEmpty()) {
                continue;
            }

            $seatCapacities = SeatLayout::getLayoutForCollege($college);
            $occupied = (int) ($occupiedMap[$college] ?? 0);
            $available = max($seatCapacities->sum() - $occupied, 0);

            $staffNames = $staffCollection->pluck('name')->filter()->values()->all();

            $staffLabel = count($staffNames) > 2
                ? $staffNames[0].', '.$staffNames[1].' +'.(count($staffNames) - 2).' more'
                : implode(', ', $staffNames);

            $canteens[] = [
                'name' => $canteenInfo['label'],
                'college' => $college,
                'dist' => $canteenInfo['dist'],
                'rating' => CanteenFeedback::averageRatingForCollege($college),
                'seats' => $available.'/'.$seatCapacities->sum(),
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
        $collegeNorm = UserCanteenBalance::normalizedCollege($college);
        if (! array_key_exists($collegeNorm, $catalog)) {
            abort(404);
        }

        if (request()->has('change_mode')) {
            session()->forget('canteen_mode_' . $collegeNorm);
            return redirect()->route('student.canteen', $collegeNorm);
        }

        $orderMode = session('canteen_mode_' . $collegeNorm);
        if (!$orderMode) {
            return view('student.canteen.mode', [
                'college' => $collegeNorm,
                'canteenName' => $catalog[$collegeNorm]['label'],
            ]);
        }

        $seatCapacities = SeatLayout::getLayoutForCollege($collegeNorm);
        $totalSeats = $seatCapacities->sum();
        $occupiedCount = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->count();
        $availableSeats = max($totalSeats - $occupiedCount, 0);
        $reservedSeat = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', auth()->id())
            ->value('seat_number');

        $menuItems = MenuItem::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('student.canteen.show', [
            'college' => $collegeNorm,
            'canteenName' => $catalog[$collegeNorm]['label'],
            'totalSeats' => $totalSeats,
            'occupiedCount' => $occupiedCount,
            'availableSeats' => $availableSeats,
            'reservedSeat' => $reservedSeat,
            'hasReservedSeat' => $reservedSeat !== null,
            'menuItems' => $menuItems,
            'walletBalance' => UserCanteenBalance::balanceFor((int) auth()->id(), $collegeNorm),
            'cartCount' => $this->cartQuantitySum($collegeNorm),
            'cartAddUrl' => route('student.cart.add', ['college' => $collegeNorm]),
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

    public function submitFeedback(Request $request, string $orderId)
    {
        $order = Order::where('user_id', auth()->id())
            ->where(function ($q) use ($orderId) {
                $q->where('order_number', $orderId);
                if (ctype_digit((string) $orderId)) {
                    $q->orWhere('id', (int) $orderId);
                }
            })
            ->first();

        if (! $order || $order->status !== 'completed') {
            return redirect()->route('student.orders')
                ->with('error', 'Only completed orders can be rated.');
        }

        $hasOrderIdColumn = Schema::hasColumn('canteen_feedbacks', 'order_id');

        if ($hasOrderIdColumn && CanteenFeedback::query()->where('order_id', $order->id)->exists()) {
            return redirect()->route('student.orders')
                ->with('error', 'You already submitted feedback for this order.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $comment = isset($validated['comment']) ? trim((string) $validated['comment']) : '';
        if ($comment !== '' && strlen($comment) < 3) {
            return redirect()->route('student.orders')
                ->withErrors(['comment' => 'If you add a comment, use at least 3 characters.'])
                ->withInput();
        }

        $college = UserCanteenBalance::normalizedCollege((string) $order->canteen_id);

        $payload = [
            'user_id' => (int) auth()->id(),
            'college' => $college,
            'rating' => (int) $validated['rating'],
            'comment' => $comment === '' ? null : $comment,
        ];
        if ($hasOrderIdColumn) {
            $payload['order_id'] = $order->id;
        }

        CanteenFeedback::create($payload);

        return redirect()->route('student.orders')
            ->with('status', 'Thanks — your rating was submitted.');
    }

    public function orderReceipt(Order $order)
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }

        $order->load('items');
        $labels = collect(config('canteens', []))->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);
        $cid = UserCanteenBalance::normalizedCollege((string) $order->canteen_id);
        $canteenLabel = $labels[$cid] ?? strtoupper((string) $order->canteen_id);

        $seatReservation = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$cid])
            ->where('user_id', auth()->id())
            ->first();

        return view('student.orders.receipt', [
            'order'           => $order,
            'canteenLabel'    => $canteenLabel,
            'studentName'     => auth()->user()->name,
            'seatReservation' => $seatReservation,
        ]);
    }
    public function orders()
    {
        $labels = collect(config('canteens', []))->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);

        $ordersQuery = Order::where('user_id', auth()->id())
            ->with('items')
            ->orderBy('created_at', 'desc');
        if (Schema::hasColumn('canteen_feedbacks', 'order_id')) {
            $ordersQuery->with('feedback');
        }

        $hasOrderIdColumn = Schema::hasColumn('canteen_feedbacks', 'order_id');

        $orders = $ordersQuery
            ->get()
            ->map(function ($order) use ($labels, $hasOrderIdColumn) {
                $cid = $order->canteen_id;
                $order->canteen = $labels[$cid] ?? (is_string($cid) ? strtoupper($cid) : 'Canteen');
                if (! $hasOrderIdColumn) {
                    $order->setRelation('feedback', null);
                }

                return $order;
            });

        $walletBalance = UserCanteenBalance::totalForUser((int) auth()->id());

        // Fetch seat reservations keyed by college for quick lookup in the view
        $seatReservations = DB::table('seat_reservations')
            ->where('user_id', auth()->id())
            ->get()
            ->keyBy(fn ($r) => UserCanteenBalance::normalizedCollege((string) $r->college));

        return view('student.order', [
            'orders'            => $orders,
            'walletBalance'     => $walletBalance,
            'activeOrdersCount' => $orders->whereIn('status', ['pending', 'preparing', 'ready'])->count(),
            'totalOrdersCount'  => $orders->count(),
            'seatReservations'  => $seatReservations,
        ]);
    }

    public function wallet()
    {
        $user = auth()->user()->fresh();

        $orders = Order::where('user_id', $user->id)->get();

        $totalSpent = $orders->sum('total');
        $totalOrders = $orders->count();

        $recentTransactions = $orders->sortByDesc('created_at')
            ->take(20)
            ->map(function ($order) {
                return [
                    'description' => 'Order '.$order->order_number,
                    'amount' => $order->total,
                    'type' => 'debit',
                    'date' => $order->created_at->format('M d, Y H:i'),
                    'order_id' => $order->id,
                ];
            })
            ->values()
            ->toArray();

        $transferTransactions = [];
        if (Schema::hasTable('coin_transfers')) {
            $transferTransactions = CoinTransfer::query()
                ->where(function ($q) use ($user) {
                    $q->where('sender_user_id', $user->id)->orWhere('receiver_user_id', $user->id);
                })
                ->latest()
                ->take(20)
                ->get()
                ->map(function (CoinTransfer $transfer) use ($user) {
                    $isSender = (int) $transfer->sender_user_id === (int) $user->id;
                    $peer = $isSender ? $transfer->receiver : $transfer->sender;

                    return [
                        'description' => ($isSender ? 'Shared to ' : 'Received from ').($peer->name ?? 'user'),
                        'amount' => (float) $transfer->amount,
                        'type' => $isSender ? 'debit' : 'credit',
                        'date' => $transfer->created_at->format('M d, Y H:i'),
                    ];
                })
                ->toArray();
        }

        $walletLoadTransactions = [];
        if (Schema::hasTable('wallet_load_logs')) {
            $walletLoadTransactions = \App\Models\WalletLoadLog::query()
                ->where('student_user_id', $user->id)
                ->latest()
                ->take(20)
                ->get()
                ->map(function ($log) {
                    return [
                        'description' => 'Wallet loaded · '.strtoupper($log->college),
                        'amount' => (float) $log->amount,
                        'type' => 'credit',
                        'date' => $log->created_at->format('M d, Y H:i'),
                    ];
                })
                ->toArray();
        }

        $recentTransactions = collect($recentTransactions)
            ->merge($transferTransactions)
            ->merge($walletLoadTransactions)
            ->sortByDesc(fn ($row) => strtotime((string) $row['date']))
            ->take(5)
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

        $connectRecipients = $user->friends;

        $pendingFriendRequests = \App\Models\Friendship::with('user')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->get();

        return view('student.wallet', [
            'wallet' => $wallet,
            'topUpCanteens' => $this->topUpCanteenOptions(),
            'canSendCoinsFrom' => collect($wallet['canteen_balances'])->where('balance', '>', 0)->values()->all(),
            'connectRecipients' => $connectRecipients,
            'pendingFriendRequests' => $pendingFriendRequests,
        ]);
    }

    public function transactions(Request $request)
    {
        $user = auth()->user();
        $type = $request->query('type', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $labels = collect(config('canteens', []))->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);

        // Orders
        $orderQ = Order::where('user_id', $user->id)->with('items');
        if ($dateFrom) $orderQ->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $orderQ->whereDate('created_at', '<=', $dateTo);
        $orders = ($type === 'all' || $type === 'order') ? $orderQ->latest()->get() : collect();

        // Wallet loads
        $walletQ = \App\Models\WalletLoadLog::where('student_user_id', $user->id);
        if ($dateFrom) $walletQ->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $walletQ->whereDate('created_at', '<=', $dateTo);
        $walletLoads = ($type === 'all' || $type === 'wallet_load') && Schema::hasTable('wallet_load_logs')
            ? $walletQ->latest()->get() : collect();

        // Coin transfers
        $coinQ = Schema::hasTable('coin_transfers')
            ? CoinTransfer::where(function ($q) use ($user) {
                $q->where('sender_user_id', $user->id)->orWhere('receiver_user_id', $user->id);
              })->with(['sender', 'receiver'])
            : null;
        if ($coinQ && $dateFrom) $coinQ->whereDate('created_at', '>=', $dateFrom);
        if ($coinQ && $dateTo)   $coinQ->whereDate('created_at', '<=', $dateTo);
        $coinTransfers = ($type === 'all' || $type === 'coin_transfer') && $coinQ
            ? $coinQ->latest()->get() : collect();

        // Merge all into unified list
        $transactions = collect();

        foreach ($orders as $order) {
            $cid = UserCanteenBalance::normalizedCollege((string) $order->canteen_id);
            $transactions->push([
                'type'        => 'order',
                'type_label'  => 'Canteen Order',
                'badge'       => 'blue',
                'flow'        => 'debit',
                'description' => 'Order ' . ($order->order_number ?? '#' . $order->id),
                'sub'         => $labels[$cid] ?? strtoupper($cid),
                'amount'      => (float) $order->total,
                'date'        => $order->created_at,
                'receipt_url' => route('student.orders.receipt', $order->id),
            ]);
        }

        foreach ($walletLoads as $log) {
            $cid = UserCanteenBalance::normalizedCollege((string) $log->college);
            $transactions->push([
                'type'        => 'wallet_load',
                'type_label'  => 'Wallet Load',
                'badge'       => 'green',
                'flow'        => 'credit',
                'description' => 'Wallet loaded · ' . ($labels[$cid] ?? strtoupper($cid)),
                'sub'         => 'by staff',
                'amount'      => (float) $log->amount,
                'date'        => $log->created_at,
                'receipt_url' => route('student.transactions.wallet-load-receipt', $log->id),
            ]);
        }

        foreach ($coinTransfers as $transfer) {
            $isSender = (int) $transfer->sender_user_id === (int) $user->id;
            $peer = $isSender ? $transfer->receiver : $transfer->sender;
            $cid = UserCanteenBalance::normalizedCollege((string) ($transfer->college ?? ''));
            $transactions->push([
                'type'        => 'coin_transfer',
                'type_label'  => 'Coin Transfer',
                'badge'       => 'purple',
                'flow'        => $isSender ? 'debit' : 'credit',
                'description' => ($isSender ? 'Sent to ' : 'Received from ') . ($peer->name ?? 'user'),
                'sub'         => $transfer->note ?: ($labels[$cid] ?? strtoupper($cid)),
                'amount'      => (float) $transfer->amount,
                'date'        => $transfer->created_at,
                'receipt_url' => route('student.transactions.coin-transfer-receipt', $transfer->id),
            ]);
        }

        $transactions = $transactions->sortByDesc(fn ($r) => $r['date']->timestamp)->values();

        return view('student.transactions.index', [
            'transactions' => $transactions,
            'type'         => $type,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
        ]);
    }

    public function walletLoadReceipt(int $id)
    {
        $log = \App\Models\WalletLoadLog::findOrFail($id);
        if ((int) $log->student_user_id !== (int) auth()->id()) abort(403);

        $labels = collect(config('canteens', []))->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);
        $cid = UserCanteenBalance::normalizedCollege((string) $log->college);

        return view('student.transactions.wallet-load-receipt', [
            'log'          => $log,
            'canteenLabel' => $labels[$cid] ?? strtoupper($cid),
            'studentName'  => auth()->user()->name,
        ]);
    }

    public function coinTransferReceipt(int $id)
    {
        $transfer = CoinTransfer::with(['sender', 'receiver'])->findOrFail($id);
        if ((int) $transfer->sender_user_id !== (int) auth()->id()
            && (int) $transfer->receiver_user_id !== (int) auth()->id()) {
            abort(403);
        }

        $isSender = (int) $transfer->sender_user_id === (int) auth()->id();
        $labels = collect(config('canteens', []))->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);
        $cid = UserCanteenBalance::normalizedCollege((string) ($transfer->college ?? ''));

        return view('student.transactions.coin-transfer-receipt', [
            'transfer'     => $transfer,
            'isSender'     => $isSender,
            'canteenLabel' => $labels[$cid] ?? strtoupper($cid),
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

    public function generateWalletLoadQr(Request $request)
    {
        if (! Schema::hasTable('wallet_load_qr_tokens')) {
            return back()->withErrors(['wallet' => 'Wallet QR is not ready yet. Ask an administrator to run database migrations.']);
        }

        $allowedSlugs = collect($this->topUpCanteenOptions())->pluck('slug')->all();

        $validated = $request->validate([
            '_form' => ['required', 'string', Rule::in(['wallet-load-qr'])],
            'college' => ['required', 'string', Rule::in($allowedSlugs)],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $slug = strtolower($validated['college']);

        $tokenRow = WalletLoadQrToken::query()->create([
            'user_id' => $request->user()->id,
            'college' => $slug,
            'amount' => $validated['amount'],
            'token' => strtoupper(Str::random(12)),
            'expires_at' => now()->addMinutes(30),
        ]);

        return redirect()->route('student.wallet.load-qr.show', ['token' => $tokenRow->token]);
    }

    public function showWalletLoadQr(string $token)
    {
        if (! Schema::hasTable('wallet_load_qr_tokens')) {
            abort(503, 'Wallet QR is not ready. Run migrations.');
        }

        $entry = WalletLoadQrToken::query()
            ->where('token', $token)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($entry->consumed_at !== null) {
            return redirect()->route('student.wallet')->with('error', 'wallet-load-qr-used');
        }
        if ($entry->expires_at->isPast()) {
            return redirect()->route('student.wallet')->with('error', 'wallet-load-qr-expired');
        }

        $catalog = config('canteens', []);
        $cid = UserCanteenBalance::normalizedCollege((string) $entry->college);
        $canteenLabel = $catalog[$cid]['label'] ?? strtoupper((string) $entry->college);
        $staffScanUrl = url('/staff/wallet-load/'.$entry->token);

        return view('student.wallet.load-qr', [
            'entry' => $entry,
            'canteenLabel' => $canteenLabel,
            'studentName' => auth()->user()->name,
            'staffScanUrl' => $staffScanUrl,
        ]);
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

        if (! $assignedToCanteen) {
            return response()->json([
                'error' => 'Student profile college must match this canteen.',
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

        $canteenLabel = config('canteens')[$staffCollege]['label'] ?? $staffCollege;
        $totalBalance = UserCanteenBalance::totalForUser((int) $student->id);

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
            'message' => 'Wallet updated successfully',
        ]);
    }

    public function setOrderMode(Request $request, string $college)
    {
        $collegeNorm = UserCanteenBalance::normalizedCollege($college);
        
        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:dine_in,takeout'],
        ]);

        session(['canteen_mode_' . $collegeNorm => $validated['mode']]);

        return redirect()->route('student.canteen', $collegeNorm);
    }

    public function cartHub()
    {
        $catalog = config('canteens', []);
        $carts = session('student_carts', []);
        $cards = [];

        foreach ($carts as $slug => $lines) {
            if (! is_array($lines) || $lines === [] || ! isset($catalog[$slug])) {
                continue;
            }
            $subtotal = (float) collect($lines)->sum(fn ($l) => (float) ($l['price'] ?? 0) * (int) ($l['qty'] ?? 0));
            $count = (int) collect($lines)->sum(fn ($l) => (int) ($l['qty'] ?? 0));
            $cards[] = [
                'college' => $slug,
                'label' => $catalog[$slug]['label'],
                'count' => $count,
                'subtotal' => $subtotal,
            ];
        }

        if (count($cards) === 1) {
            return redirect()->route('student.cart', ['college' => $cards[0]['college']]);
        }

        return view('student.cart.hub', [
            'carts' => $cards,
        ]);
    }

    public function showCart(string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);
        $catalog = config('canteens', []);
        $lines = $this->getCartLines($collegeNorm);
        $subtotal = (float) collect($lines)->sum(fn ($l) => (float) ($l['price'] ?? 0) * (int) ($l['qty'] ?? 0));
        $hasSeat = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', auth()->id())
            ->exists();

        return view('student.cart.show', [
            'college' => $collegeNorm,
            'canteenName' => $catalog[$collegeNorm]['label'],
            'lines' => $lines,
            'subtotal' => $subtotal,
            'walletBalance' => UserCanteenBalance::balanceFor((int) auth()->id(), $collegeNorm),
            'hasReservedSeat' => $hasSeat,
            'orderMode' => session('canteen_mode_' . $collegeNorm) ?? 'dine_in',
        ]);
    }

    public function cartAdd(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);

        $hasSeat = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', auth()->id())
            ->exists();

        // Seat check is removed, since seat reservation is now at checkout.

        $validated = $request->validate([
            'menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')],
        ]);

        $item = $this->findAvailableMenuItem((int) $validated['menu_item_id'], $collegeNorm);

        if (! $item) {
            $message = 'That item is not available from this canteen.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['cart' => $message]);
        }

        $lines = $this->getCartLines($collegeNorm);
        $found = false;
        foreach ($lines as &$line) {
            if ((int) ($line['menu_item_id'] ?? 0) === (int) $item->id) {
                $line['qty'] = (int) ($line['qty'] ?? 0) + 1;
                $found = true;
                break;
            }
        }
        unset($line);

        if (! $found) {
            $lines[] = [
                'menu_item_id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->price,
                'qty' => 1,
            ];
        }

        $this->putCartLines($collegeNorm, $lines);
        $count = $this->cartQuantitySum($collegeNorm);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'cart_count' => $count,
            ]);
        }

        return back()->with('status', 'added-to-cart');
    }

    public function cartSetQty(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);
        $validated = $request->validate([
            'menu_item_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $newLines = [];
        foreach ($this->getCartLines($collegeNorm) as $line) {
            if ((int) ($line['menu_item_id'] ?? 0) === (int) $validated['menu_item_id']) {
                if ((int) $validated['qty'] > 0) {
                    $line['qty'] = (int) $validated['qty'];
                    $newLines[] = $line;
                }

                continue;
            }
            $newLines[] = $line;
        }

        $this->putCartLines($collegeNorm, $newLines);

        return redirect()->route('student.cart', ['college' => $collegeNorm]);
    }

    public function cartRemoveItem(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);
        $validated = $request->validate([
            'menu_item_id' => ['required', 'integer'],
        ]);

        $newLines = array_values(array_filter(
            $this->getCartLines($collegeNorm),
            fn ($l) => (int) ($l['menu_item_id'] ?? 0) !== (int) $validated['menu_item_id']
        ));

        $this->putCartLines($collegeNorm, $newLines);

        return redirect()->route('student.cart', ['college' => $collegeNorm]);
    }

    public function cartCheckout(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);

        $orderMode = session('canteen_mode_' . $collegeNorm) ?? 'dine_in';

        if ($orderMode === 'dine_in') {
            session(['checkout_coupon_' . $collegeNorm => $request->input('coupon_code')]);
            return redirect()->route('student.reserve', $collegeNorm);
        }

        $validatedRequest = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:32'],
        ]);

        $seatNumber = null;

        $lines = $this->getCartLines($collegeNorm);
        if ($lines === []) {
            return back()->withErrors(['checkout' => 'Your cart is empty.']);
        }

        try {
            /** @var Order $placedOrder One order for this canteen only; other canteen carts stay in session untouched. */
            $placedOrder = DB::transaction(function () use ($collegeNorm, $lines, $validatedRequest, $orderMode, $seatNumber) {
                $validatedLines = [];
                $total = 0.0;

                foreach ($lines as $line) {
                    $menu = $this->findAvailableMenuItem((int) ($line['menu_item_id'] ?? 0), $collegeNorm);
                    if (! $menu) {
                        throw new \RuntimeException('One or more items are no longer available. Open the menu and refresh your cart.');
                    }
                    $qty = max(1, (int) ($line['qty'] ?? 1));
                    $unit = (float) $menu->price;
                    $validatedLines[] = [
                        'menu' => $menu,
                        'qty' => $qty,
                        'unit' => $unit,
                    ];
                    $total += $unit * $qty;
                }

                $total = round($total, 2);
                if ($total <= 0 || $validatedLines === []) {
                    throw new \RuntimeException('Your cart is empty.');
                }

                $coupon = null;
                $discount = 0.0;
                if (! empty($validatedRequest['coupon_code']) && Schema::hasTable('coupons')) {
                    $coupon = Coupon::query()
                        ->where('code', strtoupper(trim((string) $validatedRequest['coupon_code'])))
                        ->where('is_active', true)
                        ->first();
                    if ($coupon) {
                        $now = now();
                        if (($coupon->starts_at && $now->lt($coupon->starts_at))
                            || ($coupon->ends_at && $now->gt($coupon->ends_at))
                            || ((float) $coupon->min_order_total > $total + 1e-6)
                            || ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit)) {
                            $coupon = null;
                        }
                    }
                    if ($coupon) {
                        $discount = $coupon->type === 'percent'
                            ? round($total * ((float) $coupon->value / 100), 2)
                            : round((float) $coupon->value, 2);
                        $discount = min($discount, $total);
                    }
                }
                $payable = max(round($total - $discount, 2), 0.0);

                if ($payable > 0) {
                    UserCanteenBalance::subtract((int) auth()->id(), $collegeNorm, $payable);
                }

                $orderPayload = [
                    'user_id' => auth()->id(),
                    'order_number' => null,
                    'status' => 'pending',
                    'total' => $total,
                    'canteen_id' => $collegeNorm,
                    'is_read' => false,
                ];
                if (Schema::hasColumn('orders', 'discount_amount')) {
                    $orderPayload['discount_amount'] = $discount;
                }
                if (Schema::hasColumn('orders', 'payable_total')) {
                    $orderPayload['payable_total'] = $payable;
                }
                if (Schema::hasColumn('orders', 'coupon_id')) {
                    $orderPayload['coupon_id'] = $coupon?->id;
                }
                if (Schema::hasColumn('orders', 'order_mode')) {
                    $orderPayload['order_mode'] = $orderMode;
                }
                if (Schema::hasColumn('orders', 'seat_number')) {
                    $orderPayload['seat_number'] = $seatNumber;
                }

                $order = Order::create($orderPayload);
                if ($coupon) {
                    $coupon->increment('used_count');
                }

                $order->update([
                    'order_number' => 'ORD-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                ]);

                foreach ($validatedLines as $row) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'name' => $row['menu']->name,
                        'price' => $row['unit'],
                        'qty' => $row['qty'],
                    ]);
                }

                $this->putCartLines($collegeNorm, []);

                return $order->fresh();
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }

        $catalog = config('canteens', []);
        $canteenLabel = $catalog[$collegeNorm]['label'] ?? strtoupper($collegeNorm);

        return redirect()->route('student.orders')
            ->with('status', 'order-placed')
            ->with('order_placed_canteen', $canteenLabel)
            ->with('order_placed_id', $placedOrder->id);
    }


    public function connectSearch(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        if (strlen($term) < 2) {
            return response()->json(['items' => []]);
        }

        $usersTable = (new User)->getTable();
        $hasUsername = Schema::hasColumn($usersTable, 'username');

        $user = auth()->user();
        $friendsIds = $user->friends->pluck('id')->toArray();

        $items = User::query()
            ->whereIn('id', $friendsIds)
            ->where(function ($q) use ($term, $hasUsername) {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('student_id', 'like', '%'.$term.'%');
                if ($hasUsername) {
                    $q->orWhere('username', 'like', '%'.$term.'%');
                }
            })
            ->select('id', 'name', 'email', 'student_id')
            ->when($hasUsername, fn ($q) => $q->addSelect('username'))
            ->limit(20)
            ->get();

        return response()->json(['items' => $items]);
    }

    public function sendCoins(Request $request)
    {
        if (! Schema::hasTable('coin_transfers')) {
            return back()->withErrors(['connect' => 'Connect feature is not ready. Run migrations first.']);
        }

        $validated = $request->validate([
            'receiver_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'college' => ['required', 'string', Rule::in(array_keys(config('canteens', [])))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        if ((int) $validated['receiver_user_id'] === (int) auth()->id()) {
            return back()->withErrors(['connect' => 'You cannot transfer coins to yourself.']);
        }

        $sender = auth()->user();
        $receiver = User::query()->findOrFail((int) $validated['receiver_user_id']);
        $senderCollege = UserCanteenBalance::normalizedCollege((string) ($sender->college ?? ''));
        $receiverCollege = UserCanteenBalance::normalizedCollege((string) ($receiver->college ?? ''));
        $transferCollege = UserCanteenBalance::normalizedCollege((string) $validated['college']);

        if ($transferCollege === 'ceit' && ($senderCollege !== 'ceit' || $receiverCollege !== 'ceit')) {
            return back()->withErrors([
                'connect' => 'CEIT coins can only be transferred between users from CEIT. Choose another wallet balance for non-CEIT users.',
            ]);
        }

        try {
            DB::transaction(function () use ($validated, $transferCollege) {
                UserCanteenBalance::subtract((int) auth()->id(), $transferCollege, (float) $validated['amount']);
                UserCanteenBalance::add((int) $validated['receiver_user_id'], $transferCollege, (float) $validated['amount']);

                CoinTransfer::query()->create([
                    'sender_user_id' => auth()->id(),
                    'receiver_user_id' => (int) $validated['receiver_user_id'],
                    'college' => $transferCollege,
                    'amount' => (float) $validated['amount'],
                    'note' => $validated['note'] ?? null,
                ]);

                \App\Models\ActivityNotification::notifyUser(
                    (int) $validated['receiver_user_id'],
                    \App\Models\ActivityNotification::TYPE_WALLET_TRANSFER,
                    'Coins Received',
                    auth()->user()->name . ' sent you ₱' . number_format((float) $validated['amount'], 2) . ' (' . strtoupper($transferCollege) . ').'
                );
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['connect' => $e->getMessage()]);
        }

        return back()->with('status', 'coins-shared');
    }


    protected function assertCatalogCollege(string $college): string
    {
        $norm = UserCanteenBalance::normalizedCollege($college);
        if (! array_key_exists($norm, config('canteens', []))) {
            abort(404);
        }

        return $norm;
    }

    /**
     * @return list<array{menu_item_id:int,name:string,price:float,qty:int}>
     */
    protected function getCartLines(string $collegeNorm): array
    {
        $carts = session('student_carts', []);

        return $carts[$collegeNorm] ?? [];
    }

    /**
     * @param  list<array{menu_item_id:int,name:string,price:float,qty:int}>  $lines
     */
    protected function putCartLines(string $collegeNorm, array $lines): void
    {
        $carts = session('student_carts', []);
        if ($lines === []) {
            unset($carts[$collegeNorm]);
        } else {
            $carts[$collegeNorm] = array_values($lines);
        }
        session(['student_carts' => $carts]);
    }

    protected function cartQuantitySum(string $collegeNorm): int
    {
        return (int) collect($this->getCartLines($collegeNorm))->sum(fn ($l) => (int) ($l['qty'] ?? 0));
    }

    protected function findAvailableMenuItem(int $menuItemId, string $collegeNorm): ?MenuItem
    {
        return MenuItem::query()
            ->whereKey($menuItemId)
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('is_available', true)
            ->first();
    }

    public function reserveSeatForm(string $college)
    {
        $collegeNorm = UserCanteenBalance::normalizedCollege((string) $college);
        if (! array_key_exists($collegeNorm, config('canteens', []))) {
            abort(404);
        }

        $seatCount = 25;
        $seatCapacities = SeatLayout::getLayoutForCollege($collegeNorm, $seatCount);
        $seatCounts = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->select('seat_number', DB::raw('COUNT(*) as count'))
            ->groupBy('seat_number')
            ->pluck('count', 'seat_number');

        $fullSeats = collect(range(1, $seatCount))
            ->filter(fn ($seatNumber) => ($seatCounts[$seatNumber] ?? 0) >= ($seatCapacities[$seatNumber] ?? 1))
            ->values()
            ->all();

        $myReservation = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', auth()->id())
            ->first();

        return view('student.reservation.reserve', [
            'college'        => $collegeNorm,
            'occupied'       => $fullSeats,
            'seatCapacities' => $seatCapacities,
            'seatCounts'     => $seatCounts,
            'totalSeats'     => $seatCapacities->sum(),
            'occupiedCount'  => $seatCounts->sum(),
            'availableCount' => max($seatCapacities->sum() - $seatCounts->sum(), 0),
            'myReservation'  => $myReservation,
        ]);
    }

    public function reserveSeatConfirm(Request $request)
    {
        $canteenKeys = array_keys(config('canteens', []));
        $request->merge([
            'college' => UserCanteenBalance::normalizedCollege((string) $request->input('college', '')),
        ]);

        $validated = $request->validate([
            'college' => ['required', 'string', Rule::in($canteenKeys)],
            'seat' => ['nullable', 'integer', 'between:1,25'],
            'share_code' => ['nullable', 'string', 'size:6'],
        ]);

        if (empty($validated['seat']) && empty($validated['share_code'])) {
            return back()->with('error', 'Please select a seat from the map or enter a Seat Code.');
        }

        $collegeNorm = $validated['college'];
        $seatNumber = $validated['seat'];
        $shareCode = null;

        // If a share code is provided, use it to find the seat
        if (!empty($validated['share_code'])) {
            $existingRes = SeatReservation::where('share_code', strtoupper($validated['share_code']))->first();
            if (!$existingRes) {
                return back()->with('error', 'Invalid Seat Code.');
            }
            if (strtolower(trim($existingRes->college)) !== $collegeNorm) {
                return back()->with('error', 'That Seat Code is for a different canteen.');
            }
            $seatNumber = $existingRes->seat_number;
            $shareCode = $existingRes->share_code;
        }

        $seatCapacities = SeatLayout::getLayoutForCollege($collegeNorm);
        $alreadyTaken = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('seat_number', $seatNumber)
            ->where('user_id', '!=', auth()->id())
            ->count();

        if ($alreadyTaken >= ($seatCapacities[$seatNumber] ?? 1)) {
            return back()->with('error', 'Seat is full. Please choose another seat.');
        }

        // Now process the checkout + seat reservation together
        $lines = $this->getCartLines($collegeNorm);
        if ($lines === []) {
            return redirect()->route('student.cart', $collegeNorm)->withErrors(['checkout' => 'Your cart is empty.']);
        }

        $couponCode = session('checkout_coupon_' . $collegeNorm);

        try {
            $placedOrder = DB::transaction(function () use ($collegeNorm, $lines, $couponCode, $seatNumber, &$shareCode) {
                $validatedLines = [];
                $total = 0.0;

                foreach ($lines as $line) {
                    $menu = $this->findAvailableMenuItem((int) ($line['menu_item_id'] ?? 0), $collegeNorm);
                    if (! $menu) {
                        throw new \RuntimeException('One or more items are no longer available. Open the menu and refresh your cart.');
                    }
                    $qty = max(1, (int) ($line['qty'] ?? 1));
                    $unit = (float) $menu->price;
                    $validatedLines[] = [
                        'menu' => $menu,
                        'qty' => $qty,
                        'unit' => $unit,
                    ];
                    $total += $unit * $qty;
                }

                $total = round($total, 2);
                if ($total <= 0 || $validatedLines === []) {
                    throw new \RuntimeException('Your cart is empty.');
                }

                $coupon = null;
                $discount = 0.0;
                if (! empty($couponCode) && \Illuminate\Support\Facades\Schema::hasTable('coupons')) {
                    $coupon = \App\Models\Coupon::query()
                        ->where('code', strtoupper(trim((string) $couponCode)))
                        ->where('is_active', true)
                        ->first();
                    if ($coupon) {
                        $now = now();
                        if (($coupon->starts_at && $now->lt($coupon->starts_at))
                            || ($coupon->ends_at && $now->gt($coupon->ends_at))
                            || ((float) $coupon->min_order_total > $total + 1e-6)
                            || ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit)) {
                            $coupon = null;
                        }
                    }
                    if ($coupon) {
                        $discount = $coupon->type === 'percent'
                            ? round($total * ((float) $coupon->value / 100), 2)
                            : round((float) $coupon->value, 2);
                        $discount = min($discount, $total);
                    }
                }
                $payable = max(round($total - $discount, 2), 0.0);

                if ($payable > 0) {
                    UserCanteenBalance::subtract((int) auth()->id(), $collegeNorm, $payable);
                }

                // Reserve the seat
                SeatReservation::where('user_id', auth()->id())
                    ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
                    ->delete();

                if (!$shareCode) {
                    $shareCode = strtoupper(\Illuminate\Support\Str::random(6));
                }

                SeatReservation::create([
                    'user_id' => auth()->id(),
                    'college' => $collegeNorm,
                    'seat_number' => $seatNumber,
                    'share_code' => $shareCode,
                ]);

                $orderPayload = [
                    'user_id' => auth()->id(),
                    'order_number' => null,
                    'status' => 'pending',
                    'total' => $total,
                    'canteen_id' => $collegeNorm,
                    'is_read' => false,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'discount_amount')) {
                    $orderPayload['discount_amount'] = $discount;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'payable_total')) {
                    $orderPayload['payable_total'] = $payable;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'coupon_id')) {
                    $orderPayload['coupon_id'] = $coupon?->id;
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'order_mode')) {
                    $orderPayload['order_mode'] = 'dine_in';
                }
                if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'seat_number')) {
                    $orderPayload['seat_number'] = $seatNumber;
                }

                $order = Order::create($orderPayload);
                if ($coupon) {
                    $coupon->increment('used_count');
                }

                $order->update([
                    'order_number' => 'ORD-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                ]);

                foreach ($validatedLines as $row) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'name' => $row['menu']->name,
                        'price' => $row['unit'],
                        'qty' => $row['qty'],
                    ]);
                }

                $this->putCartLines($collegeNorm, []);

                return $order->fresh();
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        session()->forget('checkout_coupon_' . $collegeNorm);

        $canteenLabel = config('canteens')[$collegeNorm]['label'] ?? $collegeNorm;
        $student = auth()->user();
        $itemSummary = collect($validatedLines ?? [])
            ->map(fn ($r) => $r['qty'] . '× ' . $r['menu']->name)
            ->join(', ');

        // Notify the student about their seat
        \App\Models\ActivityNotification::notifyUser(
            auth()->id(),
            \App\Models\ActivityNotification::TYPE_SEAT_RESERVED,
            'Seat reserved',
            'Seat #' . $seatNumber . ' at ' . $canteenLabel . '.',
            null
        );

        // Notify all staff of this canteen about the new order
        \App\Models\ActivityNotification::notifyStaffOfCollege(
            $collegeNorm,
            \App\Models\ActivityNotification::TYPE_NEW_ORDER,
            'New order — ' . ($placedOrder->order_number ?? '#' . $placedOrder->id),
            $student->name . ' · Seat #' . $seatNumber . ' · ₱' . number_format($placedOrder->total, 2) . ($itemSummary ? ' · ' . $itemSummary : ''),
            $placedOrder->id
        );

        return redirect()->route('student.orders')
            ->with('status', 'order-placed')
            ->with('order_placed_canteen', $canteenLabel)
            ->with('order_placed_id', $placedOrder->id);
    }
}
