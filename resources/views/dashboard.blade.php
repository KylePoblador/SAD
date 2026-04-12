<x-app-layout>
    <x-slot name="header">
        @php
            $role = session('selected_role', 'student');
            $isStaff = $role === 'staff';
        @endphp
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $isStaff ? 'Canteen Staff Dashboard' : 'Student Dashboard' }}
            </h2>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $isStaff ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800' }}">
                {{ $isStaff ? 'STAFF VIEW' : 'STUDENT VIEW' }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($isStaff)
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">Orders Today</p>
                        <p class="mt-2 text-3xl font-bold text-gray-800">142</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="mt-2 text-3xl font-bold text-amber-600">18</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">Items Low Stock</p>
                        <p class="mt-2 text-3xl font-bold text-red-600">6</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Incoming Orders (UI Demo)</h3>
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
                                <p class="font-medium text-gray-700">#ORD-1032 - Chicken Rice</p>
                                <span class="text-xs rounded-full bg-yellow-100 text-yellow-700 px-2 py-1">Preparing</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
                                <p class="font-medium text-gray-700">#ORD-1033 - Iced Tea</p>
                                <span class="text-xs rounded-full bg-blue-100 text-blue-700 px-2 py-1">Queued</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
                                <p class="font-medium text-gray-700">#ORD-1034 - Burger Meal</p>
                                <span class="text-xs rounded-full bg-green-100 text-green-700 px-2 py-1">Ready</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Inventory Alerts (UI Demo)</h3>
                        <ul class="mt-4 space-y-3 text-sm">
                            <li class="flex items-center justify-between rounded-lg border border-red-100 bg-red-50 px-4 py-3">
                                <span>Rice (5kg)</span><span class="font-semibold text-red-700">Critical</span>
                            </li>
                            <li class="flex items-center justify-between rounded-lg border border-amber-100 bg-amber-50 px-4 py-3">
                                <span>Chicken Fillet</span><span class="font-semibold text-amber-700">Low</span>
                            </li>
                            <li class="flex items-center justify-between rounded-lg border border-amber-100 bg-amber-50 px-4 py-3">
                                <span>Cups (16oz)</span><span class="font-semibold text-amber-700">Low</span>
                            </li>
                        </ul>
                    </div>
                </div>
            @else
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">Wallet Balance</p>
                        <p class="mt-2 text-3xl font-bold text-green-700">PHP 320.00</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">Active Orders</p>
                        <p class="mt-2 text-3xl font-bold text-gray-800">2</p>
                    </div>
                    <div class="rounded-xl bg-white p-5 shadow-sm border border-gray-100">
                        <p class="text-sm text-gray-500">Reward Points</p>
                        <p class="mt-2 text-3xl font-bold text-indigo-700">185</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Today's Menu (UI Demo)</h3>
                        <div class="mt-4 space-y-3">
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">Chicken Rice Meal</p>
                                    <p class="text-sm text-gray-500">Best seller</p>
                                </div>
                                <p class="font-semibold text-gray-700">PHP 75</p>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">Burger + Fries</p>
                                    <p class="text-sm text-gray-500">Combo meal</p>
                                </div>
                                <p class="font-semibold text-gray-700">PHP 95</p>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800">Iced Coffee</p>
                                    <p class="text-sm text-gray-500">Cold drinks</p>
                                </div>
                                <p class="font-semibold text-gray-700">PHP 45</p>
                            </div>
                        </div>
                    </div>

                    <div id="order-status" class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Order Status</h3>
                        <div class="mt-4 space-y-4 text-sm">
                            <div class="rounded-lg border border-gray-100 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-800">#ORD-1018</span>
                                    <span class="rounded-full bg-green-100 px-2 py-1 text-green-700">Completed</span>
                                </div>
                                <div id="provide-feedback" class="mt-3">
                                    <label for="feedback_ord_1018"
                                        class="mb-1 block text-xs font-semibold text-gray-700">Provide Feedback</label>
                                    <textarea id="feedback_ord_1018" rows="2"
                                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500"
                                        placeholder="Order is completed, you can leave feedback here."></textarea>
                                    <button type="button"
                                        class="mt-2 rounded-md bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                        Submit Feedback
                                    </button>
                                </div>
                            </div>

                            <div class="rounded-lg border border-gray-100 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-800">#ORD-1020</span>
                                    <span class="rounded-full bg-yellow-100 px-2 py-1 text-yellow-700">Preparing</span>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">Feedback is disabled until order is completed.</p>
                            </div>

                            <div class="rounded-lg border border-gray-100 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-800">#ORD-1023</span>
                                    <span class="rounded-full bg-blue-100 px-2 py-1 text-blue-700">Queued</span>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">Feedback is disabled until order is completed.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
