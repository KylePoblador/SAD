<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\CanteenFeedback;
use App\Models\MenuItem;
use App\Models\Order;

use App\Models\SeatLayout;
use App\Models\UserCanteenBalance;
use App\Models\WalletLoadLog;
use App\Models\WalletLoadQrToken;
use App\Services\InAppNotificationFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $role = strtolower(trim((string) (Auth::user()->role ?? 'student')));
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($role !== 'staff') {
            return redirect()->route('student.dashboard');
        }

        $collegeCode = $this->staffCollegeCode();
        $catalog = config('canteens', []);
        $staffCollegeName = $catalog[$collegeCode]['label'] ?? 'Assigned canteen';

        $seatCapacities = SeatLayout::getLayoutForCollege($collegeCode);
        $totalSeats = $seatCapacities->sum();
        $occupiedCount = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->count();
        $availableSeats = max($totalSeats - $occupiedCount, 0);

        $todayOrders = Order::query()
            ->whereRaw('LOWER(TRIM(canteen_id)) = ?', [$collegeCode])
            ->whereDate('created_at', today())
            ->count();

        $revenue = (float) Order::query()
            ->whereRaw('LOWER(TRIM(canteen_id)) = ?', [$collegeCode])
            ->whereDate('created_at', today())
            ->sum('total');

        $recentOrders = Order::query()
            ->whereRaw('LOWER(TRIM(canteen_id)) = ?', [$collegeCode])
            ->with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(fn ($order) => (object) [
                'customer_name' => $order->user->name ?? 'Customer',
                'status' => $order->status,
            ]);

        return view('staff.dashboard', [
            'staffCollegeName' => $staffCollegeName,
            'collegeCode' => strtoupper($collegeCode),
            'todayOrders' => $todayOrders,
            'revenue' => $revenue,
            'availableSeats' => $availableSeats,
            'totalSeats' => $totalSeats,
            'rating' => CanteenFeedback::averageRatingForCollege($collegeCode),
            'recentOrders' => $recentOrders,
        ]);
    }

    public function qrScanner()
    {
        if (strtolower(trim((string) (Auth::user()->role ?? ''))) !== 'staff') {
            abort(403);
        }

        return view('Staff.quickaction.qr-scanner');
    }



    public function walletLoadQrConfirm(string $token)
    {
        if (strtolower(trim((string) (Auth::user()->role ?? ''))) !== 'staff') {
            abort(403);
        }
        if (! Schema::hasTable('wallet_load_qr_tokens')) {
            abort(503, 'Wallet QR feature is not ready. Run migrations.');
        }

        $staffCollege = $this->staffCollegeCode();
        $entry = WalletLoadQrToken::query()
            ->where('token', $token)
            ->with('user')
            ->firstOrFail();

        $entryCollege = UserCanteenBalance::normalizedCollege((string) $entry->college);
        if ($entryCollege !== $staffCollege) {
            abort(403);
        }

        if ($entry->consumed_at !== null) {
            return redirect()->route('staff.qr.scanner')->with('status', 'Wallet QR already used.');
        }
        if ($entry->expires_at->isPast()) {
            return redirect()->route('staff.qr.scanner')->with('error', 'Wallet QR expired.');
        }

        $canteenLabel = config('canteens')[$staffCollege]['label'] ?? strtoupper($staffCollege);

        return view('Staff.quickaction.wallet-load-confirm', [
            'entry' => $entry,
            'student' => $entry->user,
            'canteenLabel' => $canteenLabel,
        ]);
    }

    public function walletLoadQrConsume(Request $request, string $token)
    {
        if (strtolower(trim((string) (Auth::user()->role ?? ''))) !== 'staff') {
            abort(403);
        }
        if (! Schema::hasTable('wallet_load_qr_tokens')) {
            abort(503, 'Wallet QR feature is not ready. Run migrations.');
        }

        $staffCollege = $this->staffCollegeCode();
        $staff = Auth::user();

        $entry = WalletLoadQrToken::query()
            ->where('token', $token)
            ->with('user')
            ->firstOrFail();

        $entryCollege = UserCanteenBalance::normalizedCollege((string) $entry->college);
        if ($entryCollege !== $staffCollege) {
            abort(403);
        }

        if ($entry->consumed_at !== null) {
            return redirect()->route('staff.qr.scanner')->with('status', 'Wallet QR already used.');
        }
        if ($entry->expires_at->isPast()) {
            return redirect()->route('staff.qr.scanner')->with('error', 'Wallet QR expired.');
        }

        $student = $entry->user;
        if (! $student) {
            abort(404);
        }

        $amount = (float) $entry->amount;

        DB::transaction(function () use ($entry, $staffCollege, $amount, $student, $staff) {
            $entry->update([
                'consumed_at' => now(),
                'consumed_by_user_id' => $staff->id,
            ]);

            $newCanteenBalance = UserCanteenBalance::add((int) $student->id, $staffCollege, $amount);

            WalletLoadLog::query()->create([
                'student_user_id' => $student->id,
                'staff_user_id' => $staff->id,
                'college' => $staffCollege,
                'amount' => $amount,
            ]);

            $canteenLabel = config('canteens')[$staffCollege]['label'] ?? $staffCollege;
            $totalBalance = UserCanteenBalance::totalForUser((int) $student->id);

            ActivityNotification::notifyUser(
                (int) $student->id,
                ActivityNotification::TYPE_WALLET_LOADED,
                'Wallet loaded',
                $staff->name.' confirmed ₱'.number_format($amount, 2).' to your '.$canteenLabel.' wallet. '.$canteenLabel.': ₱'.number_format($newCanteenBalance, 2).' · Total: ₱'.number_format($totalBalance, 2).'.',
                null
            );
        });

        return redirect()->route('staff.qr.scanner')->with('status', 'Wallet load verified and completed.');
    }

    public function profile()
    {
        $code = Auth::user()->college;
        $catalog = config('canteens', []);

        return view('staff.profile', [
            'staffCanteenName' => $code && isset($catalog[$code]) ? $catalog[$code]['label'] : null,
            'staffCollegeCode' => $code ? strtoupper($code) : null,
        ]);
    }

    public function notification()
    {
        return view('Staff.notification');
    }

    public function notificationData()
    {
        $user = Auth::user();
        $collegeCode = $user->college ? strtolower((string) $user->college) : 'ceit';

        $notifications = InAppNotificationFeed::staffItems((int) $user->id, $collegeCode);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => InAppNotificationFeed::staffUnreadCount((int) $user->id),
        ]);
    }

    public function markStaffNotificationRead(Request $request)
    {
        $validated = $request->validate([
            'nid' => ['required', 'string', 'regex:/^(a|o):[1-9][0-9]*$/'],
        ]);

        InAppNotificationFeed::markStaffRead((int) $request->user()->id, $validated['nid']);

        return response()->json(['success' => true]);
    }

    public function markAllStaffNotificationsRead(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'staff') {
            abort(403);
        }

        InAppNotificationFeed::markAllStaffRead((int) $user->id);

        $collegeCode = $user->college ? strtolower((string) $user->college) : 'ceit';

        return response()->json([
            'success' => true,
            'notifications' => InAppNotificationFeed::staffItems((int) $user->id, $collegeCode),
            'unread_count' => InAppNotificationFeed::staffUnreadCount((int) $user->id),
        ]);
    }

    public function clearAllStaffNotifications(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'staff') {
            abort(403);
        }

        InAppNotificationFeed::clearStaffNotificationFeed((int) $user->id);

        $collegeCode = $user->college ? strtolower((string) $user->college) : 'ceit';

        return response()->json([
            'success' => true,
            'notifications' => InAppNotificationFeed::staffItems((int) $user->id, $collegeCode),
            'unread_count' => InAppNotificationFeed::staffUnreadCount((int) $user->id),
        ]);
    }

    public function unreadStaffNotificationCount(Request $request)
    {
        return response()->json([
            'unread_count' => InAppNotificationFeed::staffUnreadCount((int) $request->user()->id),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:5000'],
        ]);

        $user = $request->user();
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        $avatarPath = $this->storePublicUserAvatar($request, $user);
        if ($avatarPath !== null) {
            $data['avatar_path'] = $avatarPath;
        }

        $user->update($data);

        return redirect()->route('staff.profile')->with('status', 'profile-updated');
    }

    public function orders(Request $request)
    {
        $allowed = ['pending', 'preparing', 'ready', 'completed'];
        $status = in_array($request->query('status'), $allowed, true) ? $request->query('status') : 'pending';
        $collegeCode = $this->staffCollegeCode();

        $canteenName = config('canteens')[$collegeCode]['label'] ?? 'Canteen';

        $statusCounts = Order::query()
            ->whereRaw('LOWER(TRIM(canteen_id)) = ?', [$collegeCode])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $counts = [];
        foreach ($allowed as $s) {
            $counts[$s] = (int) ($statusCounts[$s] ?? 0);
        }

        $orders = Order::query()
            ->where('status', $status)
            ->whereRaw('LOWER(TRIM(canteen_id)) = ?', [$collegeCode])
            ->with('user', 'items')
            ->orderByDesc('created_at')
            ->get();

        return view('Staff.quickaction.orders', [
            'orders' => $orders,
            'status' => $status,
            'canteenName' => $canteenName,
            'statusCounts' => $counts,
        ]);
    }

    public function orderDetail(Order $order)
    {
        $this->authorizeStaffOrder($order);
        $order->load('user', 'items');

        return view('Staff.quickaction.order', [
            'order' => $order,
            'canteenName' => config('canteens')[$this->staffCollegeCode()]['label'] ?? 'Canteen',
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $this->authorizeStaffOrder($order);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'preparing', 'ready', 'completed'])],
        ]);

        $rank = ['pending' => 0, 'preparing' => 1, 'ready' => 2, 'completed' => 3];
        $current = $order->status;
        $new = $validated['status'];

        if (! isset($rank[$current], $rank[$new])) {
            return redirect()->back()->withErrors(['status' => 'Invalid order state.']);
        }

        if ($rank[$new] < $rank[$current]) {
            return redirect()->back()->withErrors(['status' => 'You cannot move an order backward in the workflow.']);
        }

        if ($new === $current) {
            return redirect()->back();
        }

        $order->update(['status' => $new]);

        if ($request->input('from') === 'detail') {
            return redirect()
                ->route('staff.order.detail', $order)
                ->with('status', 'order-updated');
        }

        return redirect()
            ->route('staff.orders', ['status' => $new])
            ->with('status', 'order-updated');
    }

    public function menu()
    {
        $collegeCode = $this->staffCollegeCode();
        $items = MenuItem::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('Staff.quickaction.menu', [
            'menuItems' => $items,
            'collegeCode' => $collegeCode,
        ]);
    }

    public function storeMenuItem(Request $request)
    {
        $collegeCode = $this->staffCollegeCode();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:64'],
            'photo' => ['required', 'image', 'max:5000'],
        ]);

        $imagePath = $request->file('photo')->store('menu-items/'.$collegeCode, 'public');

        MenuItem::create([
            'college' => $collegeCode,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'category' => $validated['category'] ?: 'Meals',
            'image_path' => $imagePath,
            'is_available' => true,
            'sort_order' => (MenuItem::query()->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('staff.menu')->with('status', 'menu-added');
    }

    public function updateMenuItem(Request $request, MenuItem $menuItem)
    {
        $collegeCode = $this->staffCollegeCode();
        if (UserCanteenBalance::normalizedCollege((string) $menuItem->college) !== $collegeCode) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:64'],
            'photo' => ['nullable', 'image', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('staff.menu')
                ->withErrors($validator)
                ->withInput($request->except('photo'));
        }

        $validated = $validator->validated();

        $data = [
            'name' => $validated['name'],
            'price' => $validated['price'],
            'category' => $validated['category'] ?: 'Meals',
        ];

        if ($request->hasFile('photo')) {
            if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
                Storage::disk('public')->delete($menuItem->image_path);
            }
            $data['image_path'] = $request->file('photo')->store('menu-items/'.$collegeCode, 'public');
        }

        $menuItem->update($data);

        return redirect()->route('staff.menu')->with('status', 'menu-edited');
    }

    public function destroyMenuItem(MenuItem $menuItem)
    {
        $collegeCode = $this->staffCollegeCode();
        if (UserCanteenBalance::normalizedCollege((string) $menuItem->college) !== $collegeCode) {
            abort(403);
        }
        if ($menuItem->image_path && Storage::disk('public')->exists($menuItem->image_path)) {
            Storage::disk('public')->delete($menuItem->image_path);
        }
        $menuItem->delete();

        return redirect()->route('staff.menu')->with('status', 'menu-removed');
    }

    public function toggleMenuItem(MenuItem $menuItem)
    {
        $collegeCode = $this->staffCollegeCode();
        if (UserCanteenBalance::normalizedCollege((string) $menuItem->college) !== $collegeCode) {
            abort(403);
        }
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return redirect()->route('staff.menu')->with('status', 'menu-updated');
    }

    public function wallet()
    {
        $collegeCode = Auth::user()->college ? strtolower((string) Auth::user()->college) : 'ceit';

        $canteenName = config('canteens')[$collegeCode]['label'] ?? 'Canteen';

        $walletLoadHistory = WalletLoadLog::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->with(['student:id,name,email,student_id', 'staffMember:id,name'])
            ->latest()
            ->take(100)
            ->get();

        return view('Staff.quickaction.wallet', [
            'canteenName' => $canteenName,
            'collegeCode' => strtoupper($collegeCode),
            'walletLoadHistory' => $walletLoadHistory,
        ]);
    }

    public function seats()
    {
        $collegeCode = $this->staffCollegeCode();
        $seatCount = 25;
        $seatCapacities = SeatLayout::getLayoutForCollege($collegeCode, $seatCount);

        $reservedSeats = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(seat_reservations.college)) = ?', [$collegeCode])
            ->join('users', 'seat_reservations.user_id', '=', 'users.id')
            ->select('seat_reservations.seat_number', 'users.name as student_name')
            ->get()
            ->groupBy('seat_number')
            ->map(fn ($group) => $group->first());

        $seatCounts = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->select('seat_number', DB::raw('COUNT(*) as count'))
            ->groupBy('seat_number')
            ->pluck('count', 'seat_number');

        $totalReservations = $seatCounts->sum();
        $totalCapacity = $seatCapacities->sum();

        $seats = collect(range(1, $seatCount))->map(function ($seatNumber) use ($reservedSeats, $seatCounts, $seatCapacities) {
            $current = (int) ($seatCounts[$seatNumber] ?? 0);
            $capacity = (int) ($seatCapacities[$seatNumber] ?? 1);
            $status = $current === 0
                ? 'available'
                : ($current < $capacity ? 'partially-occupied' : 'occupied');

            return [
                'number' => $seatNumber,
                'status' => $status,
                'student' => $reservedSeats[$seatNumber]->student_name ?? null,
                'current' => $current,
                'capacity' => $capacity,
            ];
        });

        $rows = $seats->chunk(5)->values();

        return view('Staff.quickaction.seats', [
            'totalSeats' => $totalCapacity,
            'availableSeats' => max($totalCapacity - $totalReservations, 0),
            'occupiedSeats' => $totalReservations,
            'seats' => $seats,
            'seatRows' => $rows,
            'canteenName' => config('canteens')[$collegeCode]['label'] ?? $collegeCode,
            'collegeCode' => $collegeCode,
            'seatCapacities' => $seatCapacities,
        ]);
    }

    public function updateSeatCapacities(Request $request)
    {
        $collegeCode = $this->staffCollegeCode();
        $seatCount = 25;

        if (! Schema::hasTable('seat_layouts')) {
            Schema::create('seat_layouts', function (Blueprint $table) {
                $table->id();
                $table->string('college');
                $table->unsignedTinyInteger('seat_number');
                $table->unsignedTinyInteger('capacity')->default(1);
                $table->timestamps();
                $table->unique(['college', 'seat_number']);
            });
        }

        $validated = $request->validate([
            'capacity' => ['required', 'array'],
            'capacity.*' => ['required', 'integer', 'between:1,10'],
        ]);

        foreach (range(1, $seatCount) as $seatNumber) {
            $capacityValue = (int) ($validated['capacity'][$seatNumber] ?? 1);
            SeatLayout::updateOrCreate([
                'college' => $collegeCode,
                'seat_number' => $seatNumber,
            ], [
                'capacity' => $capacityValue,
            ]);
        }

        return redirect()->route('staff.seats')
            ->with('status', 'seat-capacities-saved');
    }

    public function releaseSeat(Request $request)
    {
        $collegeCode = $this->staffCollegeCode();

        $validated = $request->validate([
            'seat_number' => ['required', 'integer', 'between:1,25'],
        ]);

        $reservations = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->where('seat_number', $validated['seat_number'])
            ->get();

        $label = config('canteens')[$collegeCode]['label'] ?? $collegeCode;
        foreach ($reservations as $reservation) {
            ActivityNotification::notifyUser(
                (int) $reservation->user_id,
                ActivityNotification::TYPE_SEAT_RELEASED,
                'Seat reservation released',
                'Staff released seat #'.$validated['seat_number'].' at '.$label.'.',
                null
            );
        }

        DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->where('seat_number', $validated['seat_number'])
            ->delete();

        return redirect()->route('staff.seats')
            ->with('status', 'seat-released');
    }

    /**
     * Releases every seat reservation for this staff canteen (same data as “occupied” on the map).
     */
    public function releaseAllSeats(Request $request)
    {
        $request->validate([
            'scope' => ['required', Rule::in(['all_occupied', 'all_reserved'])],
        ]);

        $collegeCode = $this->staffCollegeCode();
        $label = config('canteens')[$collegeCode]['label'] ?? $collegeCode;

        $reservations = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->get();

        foreach ($reservations as $reservation) {
            ActivityNotification::notifyUser(
                (int) $reservation->user_id,
                ActivityNotification::TYPE_SEAT_RELEASED,
                'Seat reservation released',
                'All seats were cleared by staff at '.$label.'. Your reserved seat was released.',
                null
            );
        }

        DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->delete();

        return redirect()->route('staff.seats')
            ->with('status', 'all-seats-released')
            ->with('bulk_scope', $request->input('scope'));
    }

    public function feedbacks()
    {
        $collegeCode = $this->staffCollegeCode();
        $catalog = config('canteens', []);
        $canteenName = $catalog[$collegeCode]['label'] ?? 'Your canteen';

        $feedbacks = CanteenFeedback::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->with(['user', 'staffReplier', 'order'])
            ->latest()
            ->get();

        $averageRating = CanteenFeedback::averageRatingForCollege($collegeCode);

        return view('Staff.quickaction.feedbacks', [
            'canteenName' => $canteenName,
            'collegeCode' => $collegeCode,
            'feedbacks' => $feedbacks,
            'averageRating' => $averageRating,
        ]);
    }

    public function replyFeedback(Request $request, CanteenFeedback $feedback)
    {
        $this->authorizeStaffFeedback($feedback);

        $validated = $request->validate([
            'reply' => ['required', 'string', 'min:1', 'max:1000'],
        ]);

        $feedback->update([
            'staff_reply' => $validated['reply'],
            'staff_reply_at' => now(),
            'staff_reply_user_id' => Auth::id(),
        ]);

        return redirect()->route('staff.feedbacks')
            ->with('status', 'Reply saved.');
    }

    public function reports(Request $request)
    {
        return view('Staff.quickaction.reports', $this->buildReportData($request));
    }

    public function reportsPrint(Request $request)
    {
        return view('Staff.quickaction.reports-print', $this->buildReportData($request));
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildReportData(Request $request): array
    {
        $collegeCode = $this->staffCollegeCode();
        $catalog = config('canteens', []);
        $canteenName = $catalog[$collegeCode]['label'] ?? 'Your canteen';

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $baseOrders = Order::query()
            ->whereRaw('LOWER(TRIM(canteen_id)) = ?', [$collegeCode]);

        if (! empty($validated['from'])) {
            $baseOrders->whereDate('created_at', '>=', $validated['from']);
        }
        if (! empty($validated['to'])) {
            $baseOrders->whereDate('created_at', '<=', $validated['to']);
        }

        $completedStatuses = ['completed', 'success', 'successful'];
        $cancelledStatuses = ['cancelled', 'canceled', 'cancel'];

        $totalOrders = (clone $baseOrders)->count();
        $successfulOrdersCount = (clone $baseOrders)->whereIn('status', $completedStatuses)->count();
        $cancelledOrdersCount = (clone $baseOrders)->whereIn('status', $cancelledStatuses)->count();

        $totalIncome = (float) (clone $baseOrders)->whereIn('status', $completedStatuses)->sum('total');
        $cancelledAmount = (float) (clone $baseOrders)->whereIn('status', $cancelledStatuses)->sum('total');

        $successfulOrders = (clone $baseOrders)
            ->whereIn('status', $completedStatuses)
            ->with(['user', 'items'])
            ->orderByDesc('created_at')
            ->get();

        $cancelledOrders = (clone $baseOrders)
            ->whereIn('status', $cancelledStatuses)
            ->with(['user', 'items'])
            ->orderByDesc('created_at')
            ->get();

        $topItemsQuery = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereRaw('LOWER(TRIM(orders.canteen_id)) = ?', [$collegeCode])
            ->whereIn('orders.status', $completedStatuses);

        if (! empty($validated['from'])) {
            $topItemsQuery->whereDate('orders.created_at', '>=', $validated['from']);
        }
        if (! empty($validated['to'])) {
            $topItemsQuery->whereDate('orders.created_at', '<=', $validated['to']);
        }

        $topItems = $topItemsQuery
            ->selectRaw('order_items.name as name, SUM(order_items.qty) as sold, SUM(order_items.qty * order_items.price) as total')
            ->groupBy('order_items.name')
            ->orderByDesc('sold')
            ->limit(10)
            ->get();

        return [
            'canteenName' => $canteenName,
            'collegeCode' => strtoupper($collegeCode),
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'totalOrders' => $totalOrders,
            'successfulOrdersCount' => $successfulOrdersCount,
            'cancelledOrdersCount' => $cancelledOrdersCount,
            'totalIncome' => $totalIncome,
            'cancelledAmount' => $cancelledAmount,
            'successfulOrders' => $successfulOrders,
            'cancelledOrders' => $cancelledOrders,
            'topItems' => $topItems,
        ];
    }

    protected function authorizeStaffFeedback(CanteenFeedback $feedback): void
    {
        $mine = UserCanteenBalance::normalizedCollege($this->staffCollegeCode());
        $fbCollege = UserCanteenBalance::normalizedCollege((string) $feedback->college);
        if ($fbCollege !== $mine) {
            abort(403);
        }
    }

    protected function staffCollegeCode(): string
    {
        $u = Auth::user();

        return $u && $u->college ? UserCanteenBalance::normalizedCollege((string) $u->college) : 'ceit';
    }

    protected function authorizeStaffOrder(Order $order): void
    {
        $mine = $this->staffCollegeCode();
        $cid = UserCanteenBalance::normalizedCollege((string) ($order->canteen_id ?? ''));

        if ($cid !== $mine) {
            abort(403, 'This order belongs to another canteen.');
        }
    }
}
