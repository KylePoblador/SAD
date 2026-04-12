<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\CanteenFeedback;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\UserCanteenBalance;
use App\Models\WalletDepositInquiry;
use App\Models\WalletLoadLog;
use App\Services\InAppNotificationFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $collegeCode = $this->staffCollegeCode();
        $catalog = config('canteens', []);
        $staffCollegeName = $catalog[$collegeCode]['label'] ?? 'Assigned canteen';

        $totalSeats = 25;
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

    public function completeDepositInquiry(WalletDepositInquiry $walletDepositInquiry)
    {
        $user = Auth::user();

        if ($user->role !== 'staff') {
            abort(403);
        }

        $collegeCode = $user->college ? strtolower(trim((string) $user->college)) : 'ceit';
        $inquiryCollege = strtolower(trim((string) $walletDepositInquiry->college));

        if ($inquiryCollege !== $collegeCode) {
            abort(403);
        }

        if ($walletDepositInquiry->status !== 'pending') {
            return redirect()
                ->route('staff.wallet')
                ->with('status', 'deposit-inquiry-already-done');
        }

        $walletDepositInquiry->update(['status' => 'done']);

        $canteenLabel = config('canteens')[$inquiryCollege]['label'] ?? $walletDepositInquiry->college;

        ActivityNotification::notifyUser(
            $walletDepositInquiry->user_id,
            ActivityNotification::TYPE_DEPOSIT_INQUIRY_DONE,
            'Deposit inquiry completed',
            'Staff marked your top-up request for '.$canteenLabel.' as done.',
            $walletDepositInquiry->id
        );

        return redirect()
            ->route('staff.wallet')
            ->with('status', 'deposit-inquiry-completed');
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

        $depositInquiries = WalletDepositInquiry::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        $walletLoadHistory = WalletLoadLog::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->with(['student:id,name,email,student_id', 'staffMember:id,name'])
            ->latest()
            ->take(100)
            ->get();

        $pendingUserIds = WalletDepositInquiry::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->where('status', 'pending')
            ->pluck('user_id')
            ->unique();

        $lastDoneByUser = WalletDepositInquiry::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->where('status', 'done')
            ->selectRaw('user_id, MAX(updated_at) as last_done')
            ->groupBy('user_id')
            ->pluck('last_done', 'user_id');

        $inquiryUserIds = WalletDepositInquiry::query()
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->distinct()
            ->pluck('user_id');

        $collegeStudentIds = DB::table('users')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->where('role', 'student')
            ->pluck('id');

        $allIds = $collegeStudentIds->merge($inquiryUserIds)->unique()->sort()->values();

        $students = collect();
        foreach ($allIds as $userId) {
            $row = DB::table('users')
                ->where('id', $userId)
                ->where('role', 'student')
                ->select('id', 'name', 'email', 'college', 'student_id')
                ->first();
            if (! $row) {
                continue;
            }

            $assignedToCanteen = strtolower(trim((string) ($row->college ?? ''))) === $collegeCode;
            $hasPending = $pendingUserIds->contains($userId);
            $lastDone = $lastDoneByUser->get($userId);
            $inquiryHistoryOnly = ! $assignedToCanteen && ! $hasPending && $lastDone !== null;

            $students->push((object) [
                'id' => $row->id,
                'name' => $row->name,
                'email' => $row->email,
                'college' => $row->college,
                'student_id' => $row->student_id,
                'assigned_to_canteen' => $assignedToCanteen,
                'has_pending_inquiry' => $hasPending,
                'last_completed_inquiry_at' => $lastDone,
                'inquiry_history_only' => $inquiryHistoryOnly,
                'can_load_wallet' => $assignedToCanteen || $hasPending,
            ]);
        }

        $students = $students->sortBy(fn ($s) => strtolower($s->name))->values();

        return view('Staff.quickaction.wallet', [
            'canteenName' => $canteenName,
            'collegeCode' => strtoupper($collegeCode),
            'students' => $students,
            'depositInquiries' => $depositInquiries,
            'walletLoadHistory' => $walletLoadHistory,
        ]);
    }

    public function seats()
    {
        $collegeCode = $this->staffCollegeCode();
        $totalSeats = 25;

        $reservedSeats = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(seat_reservations.college)) = ?', [$collegeCode])
            ->join('users', 'seat_reservations.user_id', '=', 'users.id')
            ->select('seat_reservations.seat_number', 'users.name as student_name', 'seat_reservations.user_id')
            ->get()
            ->keyBy('seat_number');

        $seats = collect(range(1, $totalSeats))->map(function ($seatNumber) use ($reservedSeats) {
            $reservation = $reservedSeats->get($seatNumber);

            return [
                'number' => $seatNumber,
                'status' => $reservation ? 'occupied' : 'available',
                'student' => $reservation->student_name ?? null,
            ];
        });

        $rows = $seats->chunk(5)->values();

        return view('Staff.quickaction.seats', [
            'totalSeats' => $totalSeats,
            'availableSeats' => $seats->where('status', 'available')->count(),
            'occupiedSeats' => $seats->where('status', 'occupied')->count(),
            'seats' => $seats,
            'seatRows' => $rows,
            'canteenName' => config('canteens')[$collegeCode]['label'] ?? $collegeCode,
            'collegeCode' => $collegeCode,
        ]);
    }

    public function releaseSeat(Request $request)
    {
        $collegeCode = $this->staffCollegeCode();

        $validated = $request->validate([
            'seat_number' => ['required', 'integer', 'between:1,25'],
        ]);

        $reservation = DB::table('seat_reservations')
            ->whereRaw('LOWER(TRIM(college)) = ?', [$collegeCode])
            ->where('seat_number', $validated['seat_number'])
            ->first();

        if ($reservation) {
            $label = config('canteens')[$collegeCode]['label'] ?? $collegeCode;
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
            ->with('user')
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

    public function reports()   { return view('Staff.quickaction.reports'); }

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
