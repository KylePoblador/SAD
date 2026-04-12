<x-layouts.staff-subpage title="Customer feedbacks" :subtitle="$canteenName">
    <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-violet-700/80">Average rating</p>
        <p class="mt-1 text-3xl font-bold text-violet-900">★ {{ $averageRating }}</p>
        <p class="mt-2 text-xs text-gray-600">Based on student feedback for <strong>{{ strtoupper($collegeCode) }}</strong>.
            Shows <strong>0.0</strong> until someone submits a rating (e.g. after orders).</p>
    </div>

    <p class="text-sm font-bold text-gray-800">All feedback</p>

    @forelse ($feedbacks as $fb)
        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="font-semibold text-gray-900">{{ $fb->user->name ?? 'Student' }}</p>
                    <p class="text-xs text-gray-500">{{ $fb->created_at->format('M d, Y · g:i A') }}</p>
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
        </div>
    @empty
        <div
            class="rounded-2xl border border-dashed border-violet-200 bg-violet-50/40 px-5 py-10 text-center text-sm text-violet-900/80">
            <p class="font-medium text-violet-900">No feedback yet</p>
            <p class="mt-1 text-violet-800/75">Ratings will appear here once students can leave feedback (linked to
                completed orders). Your dashboard rating stays at <strong>0.0</strong> until then.</p>
        </div>
    @endforelse
</x-layouts.staff-subpage>
