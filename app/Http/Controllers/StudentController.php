<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\CanteenFeedback;
use App\Models\Connection;
use App\Models\ConnectionRequest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentQrSession;
use App\Models\User;
use App\Models\UserCanteenBalance;
use App\Models\WalletLoadLog;
use App\Models\WalletTransfer;
use App\Services\InAppNotificationFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        $connectionCount = Connection::query()
            ->where('user_id', $user->id)
            ->count();
        $connections = Connection::query()
            ->where('user_id', $user->id)
            ->with('friend:id,name,email,student_id')
            ->get()
            ->map(fn (Connection $connection) => $connection->friend)
            ->filter()
            ->values();
        $incomingConnectionRequests = ConnectionRequest::query()
            ->where('receiver_user_id', $user->id)
            ->where('status', ConnectionRequest::STATUS_PENDING)
            ->with('requester:id,name,email,student_id')
            ->latest()
            ->get();
        $outgoingConnectionRequests = ConnectionRequest::query()
            ->where('requester_user_id', $user->id)
            ->where('status', ConnectionRequest::STATUS_PENDING)
            ->with('receiver:id,name,email,student_id')
            ->latest()
            ->get();

        return view('student.dashboard', [
            'canteens' => $canteens,
            'activeOrdersCount' => $activeOrdersCount,
            'totalOrdersCount' => $totalOrdersCount,
            'activeOrder' => $activeOrder,
            'connectionCount' => $connectionCount,
            'connections' => $connections,
            'incomingConnectionRequests' => $incomingConnectionRequests,
            'outgoingConnectionRequests' => $outgoingConnectionRequests,
        ]);
    }

    public function showCanteen(Request $request, string $college)
    {
        $catalog = config('canteens', []);
        $collegeNorm = UserCanteenBalance::normalizedCollege($college);
        if (! array_key_exists($collegeNorm, $catalog)) {
            abort(404);
        }

        // Allow returning to mode chooser when user taps Back from canteen screen.
        if ($request->boolean('choose_mode')) {
            $this->clearSelectedServiceMode($collegeNorm);
        }

        $requestedMode = (string) $request->query('mode', '');
        if ($requestedMode !== '') {
            abort_unless(in_array($requestedMode, ['dine_in', 'takeout'], true), 404);
            $this->putSelectedServiceMode($collegeNorm, $requestedMode);
        }
        $selectedServiceMode = $this->getSelectedServiceMode($collegeNorm);
        if (! $selectedServiceMode) {
            return view('student.canteen.mode', [
                'college' => $collegeNorm,
                'canteenName' => $catalog[$collegeNorm]['label'],
            ]);
        }

        $totalSeats = 25;
        $occupiedCount = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->count();
        $availableSeats = max($totalSeats - $occupiedCount, 0);
        $reservationContext = $this->activeSeatReservationContext($collegeNorm, (int) auth()->id());
        $reservedSeat = $reservationContext?->seat_number;

        $menuItems = MenuItem::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('student.canteen.show', [
            'college' => $collegeNorm,
            'canteenName' => $catalog[$collegeNorm]['label'],
            'selectedServiceMode' => $selectedServiceMode,
            'totalSeats' => $totalSeats,
            'occupiedCount' => $occupiedCount,
            'availableSeats' => $availableSeats,
            'reservedSeat' => $reservedSeat,
            'hasReservedSeat' => $reservedSeat !== null,
            'activeReservationCode' => $reservationContext?->reservation_code,
            'menuItems' => $menuItems,
            'walletBalance' => UserCanteenBalance::balanceFor((int) auth()->id(), $collegeNorm),
            'cartCount' => $this->cartQuantitySum($collegeNorm),
            'cartAddUrl' => route('student.cart.add', ['college' => $collegeNorm]),
        ]);
    }

    public function setCanteenMode(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);
        $validated = $request->validate([
            'service_mode' => ['required', Rule::in(['dine_in', 'takeout'])],
        ]);

        $this->putSelectedServiceMode($collegeNorm, (string) $validated['service_mode']);

        $redirectTo = (string) $request->input('redirect_to', '');
        if ($redirectTo === 'cart') {
            return redirect()->route('student.cart', ['college' => $collegeNorm]);
        }

        return redirect()->route('student.canteen', ['college' => $collegeNorm]);
    }

    public function joinSeatReservation(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);
        $validated = $request->validate([
            'reservation_code' => ['required', 'string', 'max:20'],
        ]);

        $code = strtoupper(trim((string) $validated['reservation_code']));
        $reservation = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->whereRaw('UPPER(reservation_code) = ?', [$code])
            ->first();

        if (! $reservation) {
            return back()->withErrors(['reservation_code' => 'Reservation code not found for this canteen.']);
        }

        DB::transaction(function () use ($collegeNorm, $reservation, $request): void {
            DB::table('reservation_participants')
                ->where('user_id', $request->user()->id)
                ->whereIn('seat_reservation_id', function ($query) use ($collegeNorm): void {
                    $query->select('id')
                        ->from('seat_reservations')
                        ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm]);
                })
                ->delete();

            DB::table('reservation_participants')->updateOrInsert(
                [
                    'seat_reservation_id' => $reservation->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });

        return redirect()
            ->route('student.canteen', ['college' => $collegeNorm])
            ->with('status', 'Joined dine-in reservation successfully.');
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
        $order = Order::query()
            ->where('user_id', auth()->id())
            ->where(function ($q) use ($orderId) {
                $q->where('id', $orderId)->orWhere('order_number', $orderId);
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

        $studentName = (string) (auth()->user()->name ?? 'A student');
        $ratingText = (int) $validated['rating'].'/5';
        $commentSnippet = $comment === '' ? '' : ' Comment: "'.mb_strimwidth($comment, 0, 80, '...').'"';
        ActivityNotification::notifyStaffOfCollege(
            $college,
            ActivityNotification::TYPE_FEEDBACK_RECEIVED,
            'New student feedback received',
            $studentName.' rated '.$ratingText.' for order '.($order->order_number ?? 'ORD-'.$order->id).'.'.$commentSnippet,
            (int) $order->id
        );

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

    public function cancelPendingOrder(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        try {
            $refundAmount = DB::transaction(function () use ($order): float {
                /** @var Order|null $locked */
                $locked = Order::query()->whereKey($order->id)->lockForUpdate()->first();
                if (! $locked) {
                    throw new \RuntimeException('Order not found.');
                }
                if ($locked->status !== 'pending') {
                    throw new \RuntimeException('Only pending orders can be canceled.');
                }

                $canteen = UserCanteenBalance::normalizedCollege((string) $locked->canteen_id);
                $refundAmount = round((float) $locked->total, 2);
                UserCanteenBalance::add((int) $locked->user_id, $canteen, $refundAmount);

                $locked->status = 'cancelled';
                $locked->save();

                return $refundAmount;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['order' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['order' => 'Unable to cancel this order right now.']);
        }

        $collegeNorm = UserCanteenBalance::normalizedCollege((string) $order->canteen_id);
        $canteenLabel = config('canteens')[$collegeNorm]['label'] ?? strtoupper($collegeNorm);

        ActivityNotification::notifyStaffOfCollege(
            $collegeNorm,
            ActivityNotification::TYPE_ORDER_STATUS_UPDATED,
            'Order canceled by student',
            'Order '.($order->order_number ?? 'ORD-'.$order->id).' was canceled by the customer.'
        );

        ActivityNotification::notifyUser(
            (int) $request->user()->id,
            ActivityNotification::TYPE_ORDER_STATUS_UPDATED,
            'Order canceled',
            'Your pending order '.($order->order_number ?? 'ORD-'.$order->id).' at '.$canteenLabel.' was canceled. Refunded ₱'.number_format($refundAmount, 2).'.'
        );

        return redirect()->route('student.orders')
            ->with('status', 'Pending order canceled and refunded.');
    }

    public function wallet()
    {
        $user = auth()->user()->fresh();
        $catalog = config('canteens', []);
        $senderCollegeSlug = UserCanteenBalance::normalizedCollege((string) ($user->college ?? ''));
        $senderTransferCollege = isset($catalog[$senderCollegeSlug])
            ? [
                'slug' => $senderCollegeSlug,
                'label' => $catalog[$senderCollegeSlug]['label'],
            ]
            : null;

        $orders = Order::where('user_id', $user->id)->get();

        $totalSpent = $orders->sum('total');
        $totalOrders = $orders->count();

        $orderTransactions = $orders->sortByDesc('created_at')
            ->take(10)
            ->map(function ($order) {
                return [
                    'id' => 'order-'.$order->id,
                    'description' => 'Order '.$order->order_number,
                    'amount' => $order->total,
                    'type' => 'debit',
                    'channel' => 'order',
                    'date' => $order->created_at->format('M d, Y H:i'),
                    'timestamp' => $order->created_at?->getTimestamp() ?? 0,
                    'receipt_url' => route('student.orders.receipt', $order),
                    'receipt_label' => 'View order receipt',
                ];
            });

        $collegeLabel = null;
        if ($user->college && isset(config('canteens')[$user->college])) {
            $collegeLabel = config('canteens')[$user->college]['label'];
        }

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
            'recent_transactions' => [],
        ];

        $connections = Connection::query()
            ->where('user_id', $user->id)
            ->with('friend:id,name,email,student_id')
            ->get()
            ->map(function (Connection $connection) {
                return $connection->friend;
            })
            ->filter()
            ->values();

        $walletTransfers = WalletTransfer::query()
            ->where(function ($q) use ($user) {
                $q->where('sender_user_id', $user->id)
                    ->orWhere('receiver_user_id', $user->id);
            })
            ->with(['sender:id,name', 'receiver:id,name'])
            ->latest()
            ->take(100)
            ->get();

        $transferTransactions = $walletTransfers->map(function (WalletTransfer $transfer) use ($user) {
            $isSender = (int) $transfer->sender_user_id === (int) $user->id;
            $counterpartyName = $isSender
                ? ($transfer->receiver?->name ?? 'Connection')
                : ($transfer->sender?->name ?? 'Connection');

            return [
                'id' => 'transfer-'.$transfer->id,
                'description' => ($isSender ? 'Transfer to ' : 'Transfer from ').$counterpartyName,
                'amount' => (float) $transfer->amount,
                'type' => $isSender ? 'debit' : 'credit',
                'channel' => 'transfer',
                'date' => $transfer->created_at?->format('M d, Y H:i') ?? '',
                'timestamp' => $transfer->created_at?->getTimestamp() ?? 0,
                'receipt_url' => route('student.wallet.transfer.receipt', $transfer),
                'receipt_label' => 'View transfer receipt',
            ];
        });

        $recentTransactions = collect()
            ->merge($orderTransactions)
            ->merge($transferTransactions)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values()
            ->toArray();

        $wallet['recent_transactions'] = $recentTransactions;

        $incomingConnectionRequests = ConnectionRequest::query()
            ->where('receiver_user_id', $user->id)
            ->where('status', ConnectionRequest::STATUS_PENDING)
            ->with('requester:id,name,email,student_id,college')
            ->latest()
            ->get();

        $outgoingPendingRequestUserIds = ConnectionRequest::query()
            ->where('requester_user_id', $user->id)
            ->where('status', ConnectionRequest::STATUS_PENDING)
            ->pluck('receiver_user_id')
            ->all();

        $connectionDetails = $connections->map(function (User $friend) use ($walletTransfers, $user) {
            $history = $walletTransfers
                ->filter(function (WalletTransfer $transfer) use ($user, $friend) {
                    return ((int) $transfer->sender_user_id === (int) $user->id && (int) $transfer->receiver_user_id === (int) $friend->id)
                        || ((int) $transfer->sender_user_id === (int) $friend->id && (int) $transfer->receiver_user_id === (int) $user->id);
                })
                ->take(5)
                ->map(function (WalletTransfer $transfer) use ($user) {
                    $isSent = (int) $transfer->sender_user_id === (int) $user->id;

                    return [
                        'amount' => (float) $transfer->amount,
                        'type' => $isSent ? 'sent' : 'received',
                        'date' => optional($transfer->created_at)->format('M d, Y h:i A'),
                    ];
                })
                ->values()
                ->toArray();

            return [
                'id' => (int) $friend->id,
                'name' => (string) $friend->name,
                'email' => (string) ($friend->email ?? ''),
                'student_id' => (string) ($friend->student_id ?? ''),
                'history' => $history,
            ];
        })->values();

        return view('student.wallet', [
            'wallet' => $wallet,
            'topUpCanteens' => $this->topUpCanteenOptions(),
            'connections' => $connections,
            'walletTransfers' => $walletTransfers,
            'connectionDetails' => $connectionDetails,
            'incomingConnectionRequests' => $incomingConnectionRequests,
            'outgoingPendingRequestUserIds' => $outgoingPendingRequestUserIds,
            'senderTransferCollege' => $senderTransferCollege,
        ]);
    }

    public function addConnection(Request $request)
    {
        $validated = $request->validate([
            'friend_student_id' => ['nullable', 'string', 'max:255', 'required_without:friend_user_id'],
            'friend_user_id' => ['nullable', 'integer', Rule::exists('users', 'id'), 'required_without:friend_student_id'],
        ]);

        $me = $request->user();
        $friend = User::query()
            ->where('role', 'student')
            ->when(
                ! empty($validated['friend_user_id']),
                fn ($q) => $q->where('id', (int) $validated['friend_user_id']),
                fn ($q) => $q->where('student_id', trim((string) ($validated['friend_student_id'] ?? '')))
            )
            ->first();

        if (! $friend || $friend->id === $me->id) {
            return back()->withErrors(['connection' => 'Student account not found.']);
        }

        $connection = Connection::firstOrCreate([
            'user_id' => $me->id,
            'friend_user_id' => $friend->id,
        ]);
        Connection::firstOrCreate([
            'user_id' => $friend->id,
            'friend_user_id' => $me->id,
        ]);

        $statusText = $connection->wasRecentlyCreated
            ? 'Connection added successfully.'
            : 'You are already connected.';

        return back()->with('status', $statusText);
    }

    public function sendConnectionRequest(Request $request)
    {
        $validated = $request->validate([
            'friend_user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'student'))],
        ]);

        $me = $request->user();
        $friendId = (int) $validated['friend_user_id'];
        if ($friendId === (int) $me->id) {
            return back()->withErrors(['connection' => 'You cannot send a request to yourself.']);
        }

        $alreadyConnected = Connection::query()
            ->where('user_id', $me->id)
            ->where('friend_user_id', $friendId)
            ->exists();
        if ($alreadyConnected) {
            return back()->with('status', 'You are already connected.');
        }

        $existingInversePending = ConnectionRequest::query()
            ->where('requester_user_id', $friendId)
            ->where('receiver_user_id', $me->id)
            ->where('status', ConnectionRequest::STATUS_PENDING)
            ->first();
        if ($existingInversePending) {
            return back()->withErrors(['connection' => 'This student already sent you a request. Please accept it below.']);
        }

        $requestRow = ConnectionRequest::query()->firstOrCreate(
            [
                'requester_user_id' => $me->id,
                'receiver_user_id' => $friendId,
            ],
            [
                'status' => ConnectionRequest::STATUS_PENDING,
            ]
        );

        if ($requestRow->status !== ConnectionRequest::STATUS_PENDING) {
            $requestRow->status = ConnectionRequest::STATUS_PENDING;
            $requestRow->responded_at = null;
            $requestRow->save();
        }

        ActivityNotification::notifyUser(
            $friendId,
            'connection_request_received',
            'Connection request',
            $me->name.' sent you a wallet connection request.'
        );

        return back()->with('status', 'Connection request sent.');
    }

    public function respondConnectionRequest(Request $request, ConnectionRequest $connectionRequest)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['accept', 'reject'])],
        ]);

        $me = $request->user();
        abort_unless((int) $connectionRequest->receiver_user_id === (int) $me->id, 403);
        abort_unless($connectionRequest->status === ConnectionRequest::STATUS_PENDING, 422);

        $requesterId = (int) $connectionRequest->requester_user_id;
        if ($validated['action'] === 'accept') {
            DB::transaction(function () use ($connectionRequest, $requesterId, $me) {
                Connection::firstOrCreate([
                    'user_id' => $requesterId,
                    'friend_user_id' => $me->id,
                ]);
                Connection::firstOrCreate([
                    'user_id' => $me->id,
                    'friend_user_id' => $requesterId,
                ]);

                $connectionRequest->status = ConnectionRequest::STATUS_ACCEPTED;
                $connectionRequest->responded_at = now();
                $connectionRequest->save();
            });

            ActivityNotification::notifyUser(
                $requesterId,
                'connection_request_accepted',
                'Connection request accepted',
                $me->name.' accepted your connection request.'
            );

            return back()->with('status', 'Connection request accepted.');
        }

        $connectionRequest->status = ConnectionRequest::STATUS_REJECTED;
        $connectionRequest->responded_at = now();
        $connectionRequest->save();

        return back()->with('status', 'Connection request rejected.');
    }

    public function cancelConnectionRequest(Request $request, ConnectionRequest $connectionRequest)
    {
        $me = $request->user();
        abort_unless((int) $connectionRequest->requester_user_id === (int) $me->id, 403);
        abort_unless($connectionRequest->status === ConnectionRequest::STATUS_PENDING, 422);

        $connectionRequest->delete();

        return back()->with('status', 'Connection request canceled.');
    }

    public function removeConnection(Request $request, User $friend)
    {
        $me = $request->user();
        abort_unless($friend->role === 'student', 404);

        DB::transaction(function () use ($me, $friend) {
            Connection::query()
                ->where(function ($q) use ($me, $friend) {
                    $q->where('user_id', $me->id)->where('friend_user_id', $friend->id);
                })
                ->orWhere(function ($q) use ($me, $friend) {
                    $q->where('user_id', $friend->id)->where('friend_user_id', $me->id);
                })
                ->delete();
        });

        return back()->with('status', 'Connection removed.');
    }

    public function searchStudentsForConnection(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $me = $request->user();
        $keyword = trim((string) $validated['q']);
        $catalog = config('canteens', []);
        $connectedIds = Connection::query()
            ->where('user_id', $me->id)
            ->pluck('friend_user_id')
            ->all();
        $pendingSentIds = ConnectionRequest::query()
            ->where('requester_user_id', $me->id)
            ->where('status', ConnectionRequest::STATUS_PENDING)
            ->pluck('receiver_user_id')
            ->all();
        $pendingReceivedIds = ConnectionRequest::query()
            ->where('receiver_user_id', $me->id)
            ->where('status', ConnectionRequest::STATUS_PENDING)
            ->pluck('requester_user_id')
            ->all();

        $students = User::query()
            ->where('role', 'student')
            ->where('id', '!=', $me->id)
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('student_id', 'like', '%'.$keyword.'%');
            })
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'name', 'student_id', 'email', 'college', 'avatar_path'])
            ->map(function (User $student) use ($catalog, $connectedIds, $pendingSentIds, $pendingReceivedIds) {
                $collegeSlug = UserCanteenBalance::normalizedCollege((string) ($student->college ?? ''));
                $studentId = (int) $student->id;
                $relationStatus = in_array($studentId, $connectedIds, true)
                    ? 'connected'
                    : (in_array($studentId, $pendingSentIds, true)
                        ? 'pending_sent'
                        : (in_array($studentId, $pendingReceivedIds, true) ? 'pending_received' : 'none'));

                return [
                    'id' => $studentId,
                    'name' => (string) $student->name,
                    'student_id' => (string) ($student->student_id ?? ''),
                    'email' => (string) ($student->email ?? ''),
                    'college' => $catalog[$collegeSlug]['label'] ?? strtoupper($collegeSlug),
                    'avatar_url' => $student->avatarPublicUrl(),
                    'relation_status' => $relationStatus,
                ];
            })
            ->values();

        return response()->json([
            'students' => $students,
            'count' => $students->count(),
        ]);
    }

    public function transferWallet(Request $request)
    {
        $validated = $request->validate([
            'receiver_user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'student'))],
            'amount' => ['required', 'integer', 'min:1', 'max:999999'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $sender = $request->user();
        $receiverId = (int) $validated['receiver_user_id'];
        $college = UserCanteenBalance::normalizedCollege((string) ($sender->college ?? ''));
        if (! array_key_exists($college, config('canteens', []))) {
            return back()->withErrors(['transfer' => 'Set your college first before sending wallet transfers.']);
        }
        $amount = (float) ((int) $validated['amount']);

        if ($receiverId === (int) $sender->id) {
            return back()->withErrors(['transfer' => 'You cannot transfer to your own account.']);
        }

        $isConnected = Connection::query()
            ->where('user_id', $sender->id)
            ->where('friend_user_id', $receiverId)
            ->exists();
        if (! $isConnected) {
            return back()->withErrors(['transfer' => 'You can only transfer to your connections.']);
        }

        try {
            $transfer = DB::transaction(function () use ($sender, $receiverId, $college, $amount, $validated) {
                UserCanteenBalance::subtract((int) $sender->id, $college, $amount);
                UserCanteenBalance::add($receiverId, $college, $amount);

                return WalletTransfer::query()->create([
                    'sender_user_id' => $sender->id,
                    'receiver_user_id' => $receiverId,
                    'college' => $college,
                    'amount' => $amount,
                    'note' => $validated['note'] ?? null,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['transfer' => $e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['transfer' => 'Transfer failed. Please try again in a few moments.']);
        }

        $senderTotal = UserCanteenBalance::totalForUser((int) $sender->id);
        $receiver = User::find($receiverId);

        ActivityNotification::notifyUser(
            $sender->id,
            ActivityNotification::TYPE_WALLET_TRANSFER_SENT,
            'Wallet transfer sent',
            'You sent ₱'.number_format($amount, 2).' to '.($receiver->name ?? 'a friend').'. Total balance: ₱'.number_format($senderTotal, 2).'.'
        );
        ActivityNotification::notifyUser(
            $receiverId,
            ActivityNotification::TYPE_WALLET_TRANSFER_RECEIVED,
            'Wallet transfer received',
            $sender->name.' sent you ₱'.number_format($amount, 2).' to your '.$college.' wallet.'
        );

        return back()
            ->with('status', 'Transfer successful.')
            ->with('transfer_receipt_id', $transfer->id);
    }

    public function walletTransferReceipt(WalletTransfer $walletTransfer)
    {
        $userId = (int) auth()->id();
        $isParticipant = (int) $walletTransfer->sender_user_id === $userId
            || (int) $walletTransfer->receiver_user_id === $userId;

        abort_unless($isParticipant, 403);

        $walletTransfer->load(['sender:id,name,student_id', 'receiver:id,name,student_id']);
        $catalog = config('canteens', []);
        $collegeSlug = UserCanteenBalance::normalizedCollege((string) $walletTransfer->college);
        $collegeLabel = $catalog[$collegeSlug]['label'] ?? strtoupper($walletTransfer->college);

        return view('student.transfers.receipt', [
            'walletTransfer' => $walletTransfer,
            'collegeLabel' => $collegeLabel,
        ]);
    }

    public function downloadWalletTransferReceipt(WalletTransfer $walletTransfer)
    {
        $userId = (int) auth()->id();
        $isParticipant = (int) $walletTransfer->sender_user_id === $userId
            || (int) $walletTransfer->receiver_user_id === $userId;
        abort_unless($isParticipant, 403);

        $walletTransfer->load(['sender:id,name,student_id,email', 'receiver:id,name,student_id,email']);
        $catalog = config('canteens', []);
        $collegeSlug = UserCanteenBalance::normalizedCollege((string) $walletTransfer->college);
        $collegeLabel = $catalog[$collegeSlug]['label'] ?? strtoupper($walletTransfer->college);

        if (! function_exists('imagecreatetruecolor')) {
            return back()->withErrors(['receipt' => 'PNG receipt generation is unavailable on this server (GD extension missing).']);
        }

        $width = 1000;
        $height = 1320;
        $img = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 20, 20, 20);
        $muted = imagecolorallocate($img, 100, 116, 139);
        $emerald = imagecolorallocate($img, 6, 95, 70);
        $emeraldLight = imagecolorallocate($img, 209, 250, 229);
        $line = imagecolorallocate($img, 203, 213, 225);
        $totalBg = imagecolorallocate($img, 236, 253, 245);

        imagefilledrectangle($img, 0, 0, $width, $height, $white);
        imagefilledrectangle($img, 0, 0, $width, 170, $emerald);
        imagefilledrectangle($img, 40, 210, $width - 40, $height - 40, $white);
        imagerectangle($img, 40, 210, $width - 40, $height - 40, $line);

        $isSender = (int) $walletTransfer->sender_user_id === $userId;
        $counterparty = $isSender ? $walletTransfer->receiver : $walletTransfer->sender;

        imagestring($img, 5, 45, 34, 'COINMEAL', $emeraldLight);
        imagestring($img, 4, 45, 70, 'WALLET TRANSFER RECEIPT', $white);
        imagestring($img, 3, 45, 108, 'Reference: WT-'.str_pad((string) $walletTransfer->id, 8, '0', STR_PAD_LEFT), $emeraldLight);
        imagestring($img, 3, 45, 132, 'Status: PROCESSED', $emeraldLight);

        $y = 245;
        $lineGap = 44;
        $details = [
            ['Date & Time', optional($walletTransfer->created_at)->format('M d, Y h:i A')],
            ['Transfer Role', $isSender ? 'You sent this transfer' : 'You received this transfer'],
            [$isSender ? 'Sent To' : 'Received From', $counterparty?->name ?? 'Student'],
            ['Counterparty ID', $counterparty?->student_id ?? 'N/A'],
            ['Canteen Wallet', $collegeLabel],
            ['Note', trim((string) ($walletTransfer->note ?? '')) !== '' ? trim((string) $walletTransfer->note) : 'N/A'],
        ];

        foreach ($details as [$label, $value]) {
            imagestring($img, 3, 70, $y, strtoupper((string) $label), $muted);
            imagestring($img, 5, 330, $y - 2, (string) $value, $black);
            imageline($img, 70, $y + 28, $width - 70, $y + 28, $line);
            $y += $lineGap;
        }

        $totalTop = $y + 30;
        imagefilledrectangle($img, 70, $totalTop, $width - 70, $totalTop + 140, $totalBg);
        imagerectangle($img, 70, $totalTop, $width - 70, $totalTop + 140, $line);
        imagestring($img, 4, 95, $totalTop + 30, 'TRANSFER AMOUNT', $emerald);
        imagestring($img, 5, $width - 330, $totalTop + 72, 'PHP '.number_format((float) $walletTransfer->amount, 2), $emerald);

        imagestring($img, 3, 70, $height - 120, 'This receipt is system-generated by CoinMeal.', $muted);
        imagestring($img, 3, 70, $height - 95, 'Keep this file for transfer verification.', $muted);

        ob_start();
        imagepng($img);
        $pngData = (string) ob_get_clean();
        imagedestroy($img);

        $filename = 'wallet-transfer-receipt-'.$walletTransfer->id.'.png';

        return response($pngData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($pngData),
        ]);
    }

    public function createPaymentQr(Request $request)
    {
        abort_unless($request->user()?->role === 'student', 403);

        $validated = $request->validate([
            'college' => ['required', 'string', Rule::in(array_keys(config('canteens', [])))],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ]);

        $token = 'CM-'.Str::upper(Str::random(10));
        $expiresAt = now()->addMinutes(30);
        $college = UserCanteenBalance::normalizedCollege((string) $validated['college']);
        $amount = round((float) $validated['amount'], 2);

        $session = PaymentQrSession::query()->create([
            'user_id' => $request->user()->id,
            'college' => $college,
            'amount' => $amount,
            'token' => $token,
            'expires_at' => $expiresAt,
            'status' => 'pending',
        ]);

        return view('student.wallet-qr', [
            'qrSession' => $session,
            'qrPurpose' => 'payment',
        ]);
    }

    public function createWalletLoadQr(Request $request)
    {
        abort_unless($request->user()?->role === 'student', 403);

        $allowedSlugs = collect($this->topUpCanteenOptions())->pluck('slug')->all();
        $validated = $request->validate([
            'college' => ['required', 'string', Rule::in($allowedSlugs)],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999.99'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $college = UserCanteenBalance::normalizedCollege((string) $validated['college']);
        $amount = round((float) $validated['amount'], 2);
        $token = 'LW-'.Str::upper(Str::random(10));

        $session = PaymentQrSession::query()->create([
            'user_id' => $request->user()->id,
            'college' => $college,
            'amount' => $amount,
            'token' => $token,
            'expires_at' => now()->addMinutes(30),
            'status' => 'pending',
        ]);

        return view('student.wallet-qr', [
            'qrSession' => $session,
            'qrPurpose' => 'wallet_load',
            'walletLoadNote' => trim((string) ($validated['note'] ?? '')),
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
                'error' => 'This student is not assigned to your canteen.',
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
        ActivityNotification::notifyUser(
            (int) $user->id,
            ActivityNotification::TYPE_WALLET_LOAD_PROCESSED,
            'Wallet load processed',
            'You loaded ₱'.number_format($amount, 2).' to '.$student->name.' in '.$canteenLabel.'.',
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
        $selectedServiceMode = $this->getSelectedServiceMode($collegeNorm) ?? 'dine_in';
        $lines = $this->getCartLines($collegeNorm);
        $subtotal = (float) collect($lines)->sum(fn ($l) => (float) ($l['price'] ?? 0) * (int) ($l['qty'] ?? 0));
        $reservationContext = $this->activeSeatReservationContext($collegeNorm, (int) auth()->id());
        $hasSeat = $reservationContext !== null;

        return view('student.cart.show', [
            'college' => $collegeNorm,
            'canteenName' => $catalog[$collegeNorm]['label'],
            'lines' => $lines,
            'subtotal' => $subtotal,
            'walletBalance' => UserCanteenBalance::balanceFor((int) auth()->id(), $collegeNorm),
            'hasReservedSeat' => $hasSeat,
            'activeReservationCode' => $reservationContext?->reservation_code,
            'selectedServiceMode' => $selectedServiceMode,
        ]);
    }

    public function cartAdd(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);
        $serviceMode = $this->getSelectedServiceMode($collegeNorm) ?? 'dine_in';

        $hasSeat = $this->activeSeatReservationContext($collegeNorm, (int) auth()->id()) !== null;

        if ($serviceMode === 'dine_in' && ! $hasSeat) {
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
                'total_cart_count' => $this->totalCartQuantitySum(),
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

    public function cartBuyNow(Request $request, string $college)
    {
        $collegeNorm = $this->assertCatalogCollege($college);
        $validated = $request->validate([
            'menu_item_id' => ['required', 'integer', Rule::exists('menu_items', 'id')],
        ]);

        $menu = $this->findAvailableMenuItem((int) $validated['menu_item_id'], $collegeNorm);
        if (! $menu) {
            $message = 'That item is not available from this canteen.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->withErrors(['checkout' => $message]);
        }

        $lines = $this->getCartLines($collegeNorm);
        $found = false;
        foreach ($lines as &$line) {
            if ((int) ($line['menu_item_id'] ?? 0) === (int) $menu->id) {
                $line['qty'] = (int) ($line['qty'] ?? 0) + 1;
                $found = true;
                break;
            }
        }
        unset($line);

        if (! $found) {
            $lines[] = [
                'menu_item_id' => $menu->id,
                'name' => $menu->name,
                'price' => (float) $menu->price,
                'qty' => 1,
            ];
        }
        $this->putCartLines($collegeNorm, $lines);

        $redirectUrl = route('student.cart', ['college' => $collegeNorm]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'redirect_url' => $redirectUrl,
                'message' => 'Item added. Review your cart before placing order.',
                'total_cart_count' => $this->totalCartQuantitySum(),
            ]);
        }

        return redirect($redirectUrl)->with('status', 'Item added. Review your cart before placing order.');
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
        $validated = $request->validate([
            'service_mode' => ['required', Rule::in(['dine_in', 'takeout'])],
        ]);
        $serviceMode = $validated['service_mode'];

        $reservationContext = $this->activeSeatReservationContext($collegeNorm, (int) auth()->id());
        $reservedSeat = $reservationContext?->seat_number;
        $hasSeat = $reservedSeat !== null;

        if ($serviceMode === 'dine_in' && ! $hasSeat) {
            return back()->withErrors(['checkout' => 'Reserve a seat before placing an order.']);
        }

        $lines = $this->getCartLines($collegeNorm);
        if ($lines === []) {
            return back()->withErrors(['checkout' => 'Your cart is empty.']);
        }

        try {
            /** @var Order $placedOrder One order for this canteen only; other canteen carts stay in session untouched. */
            $placedOrder = DB::transaction(function () use ($collegeNorm, $lines, $serviceMode, $reservedSeat) {
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
                    'service_mode' => $serviceMode,
                    'seat_number' => $serviceMode === 'dine_in' ? (int) $reservedSeat : null,
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
        $modeLabel = $placedOrder->service_mode === 'takeout' ? 'Take out' : 'Dine in';
        $seatSuffix = $placedOrder->service_mode === 'dine_in' && $placedOrder->seat_number
            ? ' · Seat #'.$placedOrder->seat_number
            : '';

        ActivityNotification::notifyStaffOfCollege(
            $collegeNorm,
            ActivityNotification::TYPE_ORDER_PLACED,
            'New '.$modeLabel.' order arrival',
            'Order '.$placedOrder->order_number.' · '.$modeLabel.$seatSuffix.' at '.$canteenLabel.'.'
        );

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

    protected function totalCartQuantitySum(): int
    {
        $carts = session('student_carts', []);

        return (int) collect($carts)
            ->flatten(1)
            ->sum(fn ($l) => (int) ($l['qty'] ?? 0));
    }

    protected function getSelectedServiceMode(string $collegeNorm): ?string
    {
        $modes = session('student_service_modes', []);
        $mode = $modes[$collegeNorm] ?? null;

        return in_array($mode, ['dine_in', 'takeout'], true) ? $mode : null;
    }

    protected function putSelectedServiceMode(string $collegeNorm, string $mode): void
    {
        $modes = session('student_service_modes', []);
        $modes[$collegeNorm] = $mode;
        session(['student_service_modes' => $modes]);
    }

    protected function clearSelectedServiceMode(string $collegeNorm): void
    {
        $modes = session('student_service_modes', []);
        unset($modes[$collegeNorm]);
        session(['student_service_modes' => $modes]);
    }

    protected function findAvailableMenuItem(int $menuItemId, string $collegeNorm): ?MenuItem
    {
        return MenuItem::query()
            ->whereKey($menuItemId)
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('is_available', true)
            ->first();
    }

    protected function activeSeatReservationContext(string $collegeNorm, int $userId): ?object
    {
        $hostReservation = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeNorm])
            ->where('user_id', $userId)
            ->first();
        if ($hostReservation) {
            return $hostReservation;
        }

        return DB::table('seat_reservations as sr')
            ->join('reservation_participants as rp', 'rp.seat_reservation_id', '=', 'sr.id')
            ->whereRaw('LOWER(TRIM(sr.college)) = ?', [$collegeNorm])
            ->where('rp.user_id', $userId)
            ->select('sr.*')
            ->first();
    }
}
