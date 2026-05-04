@php
    $href = $href ?? url('/');
    $label = $label ?? 'Back';
    $variant = $variant ?? 'student';
    $extraClass = $class ?? '';
    $useHistoryBack = $useHistoryBack ?? true;
    $disabled = $disabled ?? false;
    $disabledMessage = $disabledMessage ?? 'Back is temporarily disabled.';
    $classes = match ($variant) {
        'staff' => 'inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:text-emerald-800',
        'staff-muted' => 'inline-flex items-center gap-1 text-sm font-medium text-gray-600 hover:text-emerald-700',
        default => 'inline-flex items-center gap-1.5 text-sm font-semibold text-green-600 hover:text-green-700',
    };
@endphp

@if ($disabled)
    <button type="button"
        class="{{ $classes }} {{ $extraClass }} cursor-not-allowed opacity-60"
        aria-label="{{ $label }}"
        data-disabled-message="{{ $disabledMessage }}"
        onclick="return appBackLinkDisabled(event, this);">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        <span>{{ $label }}</span>
    </button>
@else
    <a href="{{ $href }}"
        class="{{ $classes }} {{ $extraClass }}"
        aria-label="{{ $label }}"
        @if ($useHistoryBack)
            data-fallback-href="{{ $href }}"
            onclick="return appBackLinkGo(event, this);"
        @endif
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        <span>{{ $label }}</span>
    </a>
@endif

@once
    <script>
        function appBackLinkGo(event, el) {
            const fallback = el?.getAttribute('data-fallback-href') || '/';
            const referrer = document.referrer || '';

            // Only use browser back when previous page is from the same app origin.
            // Otherwise, go to the explicit in-app fallback route.
            let sameOriginReferrer = false;
            try {
                if (referrer) {
                    const refUrl = new URL(referrer);
                    sameOriginReferrer = refUrl.origin === window.location.origin;
                }
            } catch (e) {
                sameOriginReferrer = false;
            }

            if (window.history.length > 1 && sameOriginReferrer) {
                event.preventDefault();
                window.history.back();
                return false;
            }
            window.location.assign(fallback);
            return false;
        }

        function appBackLinkDisabled(event, el) {
            event.preventDefault();
            const msg = el?.getAttribute('data-disabled-message') || 'Back is temporarily disabled.';
            alert(msg);
            return false;
        }
    </script>
@endonce
