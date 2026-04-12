<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Feedbacks</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>

<body class="min-h-screen bg-gray-100 pb-20" style="font-family: 'Figtree', sans-serif;">
    <div class="bg-green-500 px-4 py-4 text-white sticky top-0 z-10">
        <div class="flex items-center justify-between max-w-lg mx-auto">
            <div>
                <h1 class="text-lg font-bold">CEIT Main Canteen</h1>
                <p class="text-xs opacity-85">Completed Order Feedbacks</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold underline">Back</a>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-4 space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-xl border border-green-100 bg-green-50 p-4">
            <p class="text-sm text-green-800 font-semibold">Feedback rule</p>
            <p class="text-xs text-green-700 mt-1">Only completed orders can provide feedback.</p>
        </div>

        <div class="space-y-2">
            <p class="text-sm font-semibold text-gray-700">Submitted Feedbacks</p>
            @forelse ($feedbacks as $index => $item)
                <div class="bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-700">#{{ $item['order_id'] }}</p>
                        <span class="text-[10px] bg-green-100 text-green-700 px-2 py-1 rounded-full">{{ ucfirst($item['from'] ?? 'student') }}</span>
                    </div>
                    <p class="text-sm text-gray-700 mt-2">"{{ $item['message'] }}"</p>
                    <p class="text-[11px] text-gray-500 mt-1">
                        From: {{ ucfirst($item['from'] ?? 'student') }}
                        @if (!empty($item['student_name']) && ($item['from'] ?? '') === 'student')
                            • {{ $item['student_name'] }}
                        @endif
                        @if (!empty($item['submitted_at']))
                            • {{ $item['submitted_at'] }}
                        @endif
                    </p>

                    @if (($item['from'] ?? 'student') === 'student')
                        @if (!empty($item['staff_reply']))
                            <div class="mt-2 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                                <p class="text-xs font-semibold text-blue-700">Staff Reply</p>
                                <p class="text-sm text-blue-900 mt-1">"{{ $item['staff_reply'] }}"</p>
                                @if (!empty($item['replied_at']))
                                    <p class="text-[11px] text-blue-700 mt-1">{{ $item['replied_at'] }}</p>
                                @endif
                            </div>
                        @else
                            <form method="POST" action="{{ route('staff.feedbacks.reply', $index) }}" class="mt-2">
                                @csrf
                                <textarea name="reply" rows="2"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                    placeholder="Reply to this student feedback..."></textarea>
                                @error('reply')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <button type="submit"
                                    class="mt-2 rounded-lg bg-blue-500 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-600">
                                    Send Reply
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl px-4 py-3 shadow-sm border border-gray-100">
                    <p class="text-sm text-gray-500">No submitted feedback yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</body>

</html>
