<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Wallet Management</title>

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background: #f3f4f6;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-100">

    {{-- HEADER --}}
    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 shadow-lg">
        <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center gap-2 text-white hover:opacity-80 transition mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Dashboard
        </a>
        <h1 class="text-3xl font-bold">Student Wallet Management</h1>
        <p class="text-pink-100 mt-2">{{ $canteenName }}</p>
    </div>

    <div class="max-w-5xl mx-auto p-6 space-y-6">

        {{-- LOAD STUDENT WALLET --}}
        <div id="load-wallet" class="hidden bg-gradient-to-b from-green-50 to-white rounded-2xl p-8 shadow-lg">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Load Student Wallet</h2>
                <button onclick="closeLoadWallet()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search Bar --}}
            <div class="mb-8">
                <input type="text" id="search-input" placeholder="Search by Student ID or Name"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>

            {{-- Student Card --}}
            <div class="bg-white border-2 border-green-200 rounded-2xl p-8 text-center mb-8">
                <div class="flex justify-center mb-6">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 h-24 w-24 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                </div>

                <h3 class="text-2xl font-bold text-gray-800 mb-2" id="load-name">Student Name</h3>
                <p class="text-gray-600 mb-6" id="load-id">Student ID: #0000</p>

                <div class="bg-green-100 rounded-xl p-4 border-2 border-green-300 mb-6">
                    <p class="text-gray-700 text-sm mb-2">Current Balance</p>
                    <p class="text-4xl font-bold text-green-600" id="load-balance">₱0.00</p>
                </div>
            </div>

            {{-- Amount to Load --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Amount to Load</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600 text-lg font-semibold">₱</span>
                    <input type="number" id="load-amount" min="0" step="1" placeholder="0.00"
                        class="w-full pl-8 pr-4 py-4 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg">
                </div>
            </div>

            {{-- Quick Select Buttons --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Quick Select</label>
                <div class="grid grid-cols-3 gap-3">
                    <button onclick="setLoadAmount(50)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱50
                    </button>
                    <button onclick="setLoadAmount(100)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱100
                    </button>
                    <button onclick="setLoadAmount(200)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱200
                    </button>
                    <button onclick="setLoadAmount(500)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱500
                    </button>
                    <button onclick="setLoadAmount(1000)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱1000
                    </button>
                    <button onclick="setLoadAmount(2000)" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition">
                        ₱2000
                    </button>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="grid grid-cols-2 gap-4">
                <button onclick="closeLoadWallet()"
                    class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-3 px-6 rounded-lg transition">
                    Cancel
                </button>
                <button onclick="confirmLoad()"
                    class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg transition">
                    Confirm Load
                </button>
            </div>
        </div>

        {{-- STUDENTS LIST --}}
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
                <h2 class="text-2xl font-bold">Select Student</h2>
                <p class="text-pink-100 mt-1">Total Students: {{ $students->count() }}</p>
            </div>

            @if($students->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Student ID</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Balance</th>
                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Total Spent</th>
                            <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800">{{ $student->name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-600 font-mono text-sm">#{{ $student->id }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-green-600">₱{{ number_format($student->balance, 2) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-600">₱{{ number_format($student->total_spent, 2) }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button
                                    onclick="openLoadWallet({{ json_encode($student) }})"
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                                    View Details
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3.667A1.667 1.667 0 012 18.333V5.667C2 4.747 2.747 4 3.667 4h10.666c.92 0 1.667.747 1.667 1.667v12.666c0 .92-.747 1.667-1.667 1.667z" />
                </svg>
                <p class="text-gray-500 text-lg">No students registered for this canteen yet</p>
            </div>
            @endif
        </div>

    </div>

    <script>
        let selectedStudent = null;

        function openLoadWallet(student) {
            selectedStudent = student;

            // Populate student details
            document.getElementById('load-name').textContent = student.name;
            document.getElementById('load-id').textContent = 'Student ID: #' + student.id;
            document.getElementById('load-balance').textContent = '₱' + parseFloat(student.balance).toFixed(2);

            // Clear input
            document.getElementById('load-amount').value = '';
            document.getElementById('search-input').value = '';

            // Show load wallet panel
            document.getElementById('load-wallet').classList.remove('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function closeLoadWallet() {
            document.getElementById('load-wallet').classList.add('hidden');
            selectedStudent = null;
            document.getElementById('load-amount').value = '';
        }

        function setLoadAmount(amount) {
            document.getElementById('load-amount').value = amount;
        }

        function confirmLoad() {
            const amount = parseFloat(document.getElementById('load-amount').value);

            if (!selectedStudent) {
                alert('Please select a student first');
                return;
            }

            if (!amount || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            // Show confirmation
            const newBalance = parseFloat(selectedStudent.balance) + amount;
            const confirmed = confirm(
                `Load ₱${amount.toFixed(2)} to ${selectedStudent.name}'s wallet?\n` +
                `New Balance: ₱${newBalance.toFixed(2)}`
            );

            if (confirmed) {
                // Update wallet via API
                fetch(`/student/wallet/update/${selectedStudent.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        amount: amount
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the student's balance
                        selectedStudent.balance = newBalance;

                        // Update balance in the table row
                        const tableRows = document.querySelectorAll('tbody tr');
                        tableRows.forEach(row => {
                            const studentId = row.querySelector('td:nth-child(2)').textContent.trim();
                            if (studentId === '#' + selectedStudent.id) {
                                // Update the balance cell (3rd column) - preserve the <p> tag structure
                                const balanceCell = row.querySelector('td:nth-child(3)');
                                balanceCell.innerHTML = '<p class="font-bold text-green-600">₱' + newBalance.toFixed(2) + '</p>';
                            }
                        });

                        // Update balance display in the load wallet panel
                        document.getElementById('load-balance').textContent = '₱' + newBalance.toFixed(2);

                        // Show success message
                        alert('✓ Successfully loaded ₱' + amount.toFixed(2) + ' to ' + selectedStudent.name + '\'s wallet');

                        // Clear input
                        document.getElementById('load-amount').value = '';
                    } else {
                        alert('Error: ' + (data.error || 'Failed to update wallet'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating wallet: ' + error);
                });
            }
        }

        // Search functionality
        document.getElementById('search-input')?.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            // You could implement search filtering here if needed
        });
    </script>

</body>
</html>
