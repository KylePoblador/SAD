<x-layouts.staff-subpage title="Customer feedbacks" :subtitle="$canteenName">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-violet-700/80">Average rating</p>
        <p class="mt-1 text-3xl font-bold text-violet-900">★ {{ $averageRating }}</p>
        <p class="mt-2 text-xs text-gray-600">Based on student feedback for <strong>{{ strtoupper($collegeCode) }}</strong>.</p>
    </div>

    <p class="mt-6 text-sm font-bold text-gray-800">All feedback</p>

    @forelse ($feedbacks as $fb)
        <div class="mt-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-gray-900">{{ $fb->user->name ?? 'Student' }}</p>
                    <p class="text-xs text-gray-500">{{ $fb->created_at->format('M d, Y · g:i A') }}</p>
                    @if ($fb->order)
                        <p class="mt-1 text-[11px] text-gray-400">Order {{ $fb->order->order_number ?? '#'.$fb->order->id }}</p>
                    @endif
                </div>
                <span
                    class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-900">
                    {{ $fb->rating }}/5
                </span>
            </div>
            @if ($fb->comment)
                <p class="mt-3 text-sm leading-relaxed text-gray-700">{{ $fb->comment }}</p>
            @else
                <p class="mt-2 text-xs italic text-gray-400">No written comment.</p>
            @endif

            @if ($fb->staff_reply)
                <div class="mt-3 rounded-lg border border-emerald-100 bg-emerald-50/80 px-3 py-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-800">Your reply</p>
                    <p class="mt-1 text-sm text-emerald-950">{{ $fb->staff_reply }}</p>
                    @if ($fb->staff_reply_at)
                        <p class="mt-1 text-[10px] text-emerald-700/70">{{ $fb->staff_reply_at->format('M d, Y g:i A') }}
                            @if ($fb->staffReplier)
                                · {{ $fb->staffReplier->name }}
                            @endif
                        </p>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('staff.feedbacks.reply', $fb) }}" class="mt-3 space-y-2">
                @csrf
                <label for="reply-{{ $fb->id }}" class="sr-only">Reply to student</label>
                <textarea id="reply-{{ $fb->id }}" name="reply" rows="2" required maxlength="1000"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:border-emerald-500"
                    placeholder="{{ $fb->staff_reply ? 'Update your reply…' : 'Reply to this student…' }}">{{ old('reply', $fb->staff_reply) }}</textarea>
                @error('reply')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                    {{ $fb->staff_reply ? 'Update reply' : 'Send reply' }}
                </button>
            </form>
        </div>
    @empty
        <div
            class="mt-4 rounded-2xl border border-dashed border-violet-200 bg-violet-50/40 px-5 py-10 text-center text-sm text-violet-900/80">
            <p class="font-medium text-violet-900">No feedback yet</p>
            <p class="mt-1 text-violet-800/75">Ratings appear when students complete orders and submit feedback from
                <strong>My orders</strong>.</p>
        </div>
    @endforelse
</x-layouts.staff-subpage>
