<?php

namespace App\Services;

use App\Models\ActivityNotification;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class InAppNotificationFeed
{
    public static function studentItems(int $userId, int $limit = 40): array
    {
        $since = User::query()->whereKey($userId)->value('notification_feed_cleared_at');

        $orderItems = Order::query()
            ->where('user_id', $userId)
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Order $order) => self::serializeStudentOrder($order));

        $activityItems = ActivityNotification::query()
            ->where('user_id', $userId)
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ActivityNotification $n) => self::serializeActivity($n, false));

        $merged = self::mergeAndStrip($orderItems->concat($activityItems), $limit);

        if ($merged->isEmpty()) {
            return [self::emptyStudentRow()];
        }

        return $merged->values()->all();
    }

    public static function staffItems(int $staffUserId, string $collegeCode, int $limit = 40): array
    {
        $since = User::query()->whereKey($staffUserId)->value('notification_feed_cleared_at');

        $orderItems = Order::query()
            ->where('canteen_id', $collegeCode)
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Order $order) => self::serializeStaffOrder($order));

        $activityItems = ActivityNotification::query()
            ->where('user_id', $staffUserId)
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (ActivityNotification $n) => self::serializeActivity($n, true));

        $merged = self::mergeAndStrip($orderItems->concat($activityItems), $limit);

        if ($merged->isEmpty()) {
            return [self::emptyStaffRow()];
        }

        return $merged->values()->all();
    }

    public static function studentUnreadCount(int $userId): int
    {
        $since = User::query()->whereKey($userId)->value('notification_feed_cleared_at');

        $orders = Order::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->count();

        $activities = ActivityNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->count();

        return $orders + $activities;
    }

    public static function staffUnreadCount(int $staffUserId): int
    {
        $since = User::query()->whereKey($staffUserId)->value('notification_feed_cleared_at');

        return ActivityNotification::query()
            ->where('user_id', $staffUserId)
            ->whereNull('read_at')
            ->when($since, fn ($q) => $q->where('created_at', '>', $since))
            ->count();
    }

    public static function markStudentRead(int $userId, string $nid): void
    {
        $parsed = self::parseNid($nid);
        if ($parsed === null) {
            return;
        }

        [$kind, $id] = $parsed;

        if ($kind === 'o') {
            Order::query()
                ->where('id', $id)
                ->where('user_id', $userId)
                ->update(['is_read' => true]);

            return;
        }

        if ($kind === 'a') {
            ActivityNotification::query()
                ->where('id', $id)
                ->where('user_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public static function markStaffRead(int $staffUserId, string $nid): void
    {
        $parsed = self::parseNid($nid);
        if ($parsed === null) {
            return;
        }

        [$kind, $id] = $parsed;

        if ($kind === 'o') {
            return;
        }

        if ($kind === 'a') {
            ActivityNotification::query()
                ->where('id', $id)
                ->where('user_id', $staffUserId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
    }

    public static function markAllStudentRead(int $userId): void
    {
        ActivityNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        Order::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public static function clearStudentNotificationFeed(int $userId): void
    {
        User::query()->whereKey($userId)->update(['notification_feed_cleared_at' => now()]);

        ActivityNotification::query()
            ->where('user_id', $userId)
            ->delete();
    }

    public static function markAllStaffRead(int $staffUserId): void
    {
        ActivityNotification::query()
            ->where('user_id', $staffUserId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function clearStaffNotificationFeed(int $staffUserId): void
    {
        User::query()->whereKey($staffUserId)->update(['notification_feed_cleared_at' => now()]);

        ActivityNotification::query()
            ->where('user_id', $staffUserId)
            ->delete();
    }

    /**
     * @return array{0: string, 1: int}|null
     */
    private static function parseNid(string $nid): ?array
    {
        if (! preg_match('/^(o|a):(\d+)$/', $nid, $m)) {
            return null;
        }

        return [$m[1], (int) $m[2]];
    }

    private static function mergeAndStrip(Collection $rows, int $limit): Collection
    {
        return $rows
            ->sortByDesc(fn (array $row) => $row['_ts'])
            ->take($limit)
            ->map(fn (array $row) => collect($row)->except('_ts')->all());
    }

    private static function serializeStudentOrder(Order $order): array
    {
        $statusText = match ($order->status) {
            'pending' => 'is pending confirmation',
            'preparing' => 'is being prepared',
            'ready' => 'is ready for pickup',
            'completed' => 'has been completed',
            default => 'was updated',
        };

        return [
            '_ts' => $order->created_at->timestamp,
            'nid' => 'o:'.$order->id,
            'title' => "Order {$order->order_number} {$statusText}",
            'message' => 'Total: ₱'.$order->total,
            'time' => $order->created_at->diffForHumans(),
            'status' => $order->status,
            'is_read' => (bool) $order->is_read,
            'icon' => 'order',
            'action_url' => route('student.orders', [], false),
        ];
    }

    private static function serializeStaffOrder(Order $order): array
    {
        $statusText = match ($order->status) {
            'pending' => 'new order received',
            'preparing' => 'order is being prepared',
            'ready' => 'order is ready for pickup',
            'completed' => 'order has been completed',
            default => 'order status updated',
        };

        return [
            '_ts' => $order->created_at->timestamp,
            'nid' => 'o:'.$order->id,
            'title' => "Order {$order->order_number} {$statusText}",
            'message' => 'Total: ₱'.$order->total,
            'time' => $order->created_at->diffForHumans(),
            'status' => $order->status,
            'is_read' => true,
            'icon' => 'order',
            'action_url' => route('staff.orders', [], false),
        ];
    }

    private static function serializeActivity(ActivityNotification $n, bool $forStaff): array
    {
        return [
            '_ts' => $n->created_at->timestamp,
            'nid' => 'a:'.$n->id,
            'title' => $n->title,
            'message' => $n->body ?? '',
            'time' => $n->created_at->diffForHumans(),
            'status' => $n->badge_status,
            'is_read' => $n->read_at !== null,
            'icon' => $n->icon_key,
            'action_url' => $forStaff
                ? self::staffActivityActionUrl($n)
                : self::studentActivityActionUrl($n),
        ];
    }

    private static function studentActivityActionUrl(ActivityNotification $n): string
    {
        return match ($n->type) {
            ActivityNotification::TYPE_WALLET_LOADED,
            ActivityNotification::TYPE_DEPOSIT_INQUIRY_STUDENT,
            ActivityNotification::TYPE_DEPOSIT_INQUIRY_DONE => route('student.wallet', [], false),
            ActivityNotification::TYPE_SEAT_RESERVED,
            ActivityNotification::TYPE_SEAT_RELEASED => route('student.dashboard', [], false),
            default => route('student.notification', [], false),
        };
    }

    private static function staffActivityActionUrl(ActivityNotification $n): string
    {
        return match ($n->type) {
            ActivityNotification::TYPE_DEPOSIT_INQUIRY_STAFF => route('staff.wallet', [], false),
            default => route('staff.notification', [], false),
        };
    }

    private static function emptyStudentRow(): array
    {
        return [
            'nid' => 'none',
            'title' => 'No notifications yet',
            'message' => 'Orders, wallet updates, and other activity will show up here.',
            'time' => '',
            'status' => 'empty',
            'is_read' => true,
            'icon' => 'bell',
            'action_url' => '',
        ];
    }

    private static function emptyStaffRow(): array
    {
        return [
            'nid' => 'none',
            'title' => 'No notifications yet',
            'message' => 'Orders and wallet deposit alerts will show up here.',
            'time' => '',
            'status' => 'empty',
            'is_read' => true,
            'icon' => 'bell',
            'action_url' => '',
        ];
    }
}
