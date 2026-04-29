<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\CanteenFeedback;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\UserCanteenBalance;
use App\Models\WalletDepositInquiry;
use App\Models\WalletLoadLog;
use App\Services\InAppNotificationFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index()
    {
        $user = auth()->user();
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
                ? $staffNames[0].', '.$staffNames[1].' +'.(count($staffNames) - 2).' more'
                : implode(', ', $staffNames);

            $canteens[] = [
                'name' => $canteenInfo['label'],
                'college' => $college,
                'dist' => $canteenInfo['dist'],
                'rating' => CanteenFeedback::averageRatingForCollege($college),
                'seats' => $available.'/'.$totalSeats,
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

        $totalSeats = 25;
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

        if (CanteenFeedback::query()->where('order_id', $order->id)->exists()) {
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

        CanteenFeedback::create([
            'user_id' => (int) auth()->id(),
            'order_id' => $order->id,
            'college' => $college,
            'rating' => (int) $validated['rating'],
            'comment' => $comment === '' ? null : $comment,
        ]);

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

        return view('student.orders.receipt', [
            'order' => $order,
            'canteenLabel' => $canteenLabel,
            'studentName' => auth()->user()->name,
        ]);
    }
    public function orders()
    {
        $labels = collect(config('canteens', []))->mapWithKeys(fn ($c, $k) => [$k => $c['label']]);

        $orders = Order::where('user_id', auth()->id())
            ->with(['items', 'feedback'])
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
                    'description' => 'Order '.$order->order_number,
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
        ]);
    }

    public function cartAdd(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);

        $hasSeat = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', auth()->id())
            ->exists();

        if (! $hasSeat) {
            $message = 'Reserve a seat at this canteen before adding items to your cart.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['cart' => $message]);
        }

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

        $hasSeat = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', auth()->id())
            ->exists();

        if (! $hasSeat) {
            return back()->withErrors(['checkout' => 'Reserve a seat before placing an order.']);
        }

        $lines = $this->getCartLines($collegeNorm);
        if ($lines === []) {
            return back()->withErrors(['checkout' => 'Your cart is empty.']);
        }

        try {
            /** @var Order $placedOrder One order for this canteen only; other canteen carts stay in session untouched. */
            $placedOrder = DB::transaction(function () use ($collegeNorm, $lines) {
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

                UserCanteenBalance::subtract((int) auth()->id(), $collegeNorm, $total);

                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_number' => null,
                    'status' => 'pending',
                    'total' => $total,
                    'canteen_id' => $collegeNorm,
                    'is_read' => false,
                ]);

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
}
