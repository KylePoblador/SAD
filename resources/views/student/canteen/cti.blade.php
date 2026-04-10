<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $canteenName }}</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background:#f5f5f5;
    font-family:Arial;
}

.header {
    background:#fff;
    padding:15px;
    border-bottom:1px solid #ddd;
}

.brand {
    color:#22c55e;
    font-weight:bold;
    font-size:22px;
}

.subtext {
    font-size:12px;
    color:#555;
}

.card-custom {
    background:#fff;
    border-radius:12px;
    padding:15px;
    box-shadow:0 2px 5px rgba(0,0,0,0.08);
}

.btn-green {
    background:#22c55e;
    color:#fff;
    border-radius:20px;
    padding:6px 18px;
    border:none;
}

.menu-item {
    background:#fff;
    border-radius:12px;
    padding:15px;
    margin-bottom:15px;
    border:1px solid #ddd;
}

.menu-item img {
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:10px;
    margin-right:10px;
}

.price {
    color:#22c55e;
    font-weight:bold;
}

.bottom-nav {
    position:fixed;
    bottom:0;
    width:100%;
    background:#fff;
    border-top:1px solid #ddd;
    padding:10px 0;
}
.bottom-nav div {
    text-align:center;
}

.filter-btn {
    border-radius:10px;
    padding:6px 18px;
    border:none;
    background:#cfd8d3;
    font-size:14px;
    cursor:pointer;
}

.filter-btn.active {
    background:#22c55e;
    color:#fff;
}

.disabled-btn {
    background:#ccc !important;
    cursor:not-allowed;
}
</style>
</head>

<body>

<!-- HEADER -->
<div class="header d-flex justify-content-between">
    <div>
        <div class="brand">CoinMeal</div>
        <div class="subtext">University of Southern Mindanao</div>
    </div>

</div>

<div class="container mt-3 mb-5">

    <h5 class="d-flex align-items-center">
        <a href="{{ route('student.dashboard') }}" class="me-2 text-dark text-decoration-none">←</a>
        <b>{{ $canteenName }}</b>
    </h5>

    <small class="text-muted">{{ strtoupper($college) }}</small>

    <!-- SEAT -->
    <div class="card-custom d-flex justify-content-between align-items-center mt-3 mb-3">
        <div>
            <b>Seat availability</b><br>

            @if($hasReservedSeat)
                <div class="alert alert-success mt-2">
                    You reserved seat in this canteen: <b>{{ $reservedSeat }}</b>
                </div>
            @endif

            <small class="text-muted">{{ $availableSeats }}/{{ $totalSeats }} Available</small>
        </div>

        <a href="{{ route('student.reserve', $college) }}" class="btn btn-green">
            Reserve Seat
        </a>
    </div>

    <!-- MENU -->
    <h5><b>Menu</b></h5>

    <!-- CATEGORY FILTER -->
    <div class="mb-3 mt-2">
        <small>Category</small><br>
        <div class="d-flex flex-wrap gap-2 mt-1">
<button class="filter-btn category-btn active" onclick="filterCategory('All', this)">All</button>
<button class="filter-btn category-btn" onclick="filterCategory('Meals', this)">Meals</button>
<button class="filter-btn category-btn" onclick="filterCategory('Snacks', this)">Snacks</button>
<button class="filter-btn category-btn" onclick="filterCategory('Beverages', this)">Beverages</button>
<button class="filter-btn category-btn" onclick="filterCategory('Desserts', this)">Desserts</button>
        </div>
    </div>

    <!-- PRICE FILTER -->
    <div class="mb-3">
        <small>Price Range</small><br>
        <div class="d-flex flex-wrap gap-2 mt-1">
   <button class="filter-btn price-btn active" onclick="filterPrice('all', this)">All</button>
<button class="filter-btn price-btn" onclick="filterPrice('1-50', this)">₱1-50</button>
<button class="filter-btn price-btn" onclick="filterPrice('51-100', this)">₱51-100</button>
<button class="filter-btn price-btn" onclick="filterPrice('101-150', this)">₱101-150</button>
<button class="filter-btn price-btn" onclick="filterPrice('151+', this)">₱151+</button>
        </div>
    </div>

    <!-- ITEMS -->

    <!-- ITEM 1 -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Meals" data-price="65">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/adobo.jpg') }}">
            <div>
                <b>Pastil</b><br>
                <small class="text-muted">Meals</small>
            </div>
        </div>
        <div class="text-end">
    <button
    class="btn btn-green mb-1 {{ $hasReservedSeat ? '' : 'disabled-btn' }}"
    onclick="addToCart('Chicken Adobo Rice', 15)"
    {{ $hasReservedSeat ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱15</span>
        </div>
    </div>

    <!-- ITEM 2 -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Meals" data-price="45">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/pancit.jpg') }}">
            <div>
                <b>Pancit Canton</b><br>
                <small class="text-muted">Meals</small>
            </div>
        </div>
        <div class="text-end">
<button
    class="btn btn-green mb-1 {{ $hasReservedSeat ? '' : 'disabled-btn' }}"
    onclick="addToCart('Pancit Canton', 45)"
    {{ $hasReservedSeat ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱45</span>
        </div>
    </div>

    <!-- ITEM 3 (FIXED CATEGORY) -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Snacks" data-price="30">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/lumpia.jpg') }}">
            <div>
                <b>Lumpia Shanghai</b><br>
                <small class="text-muted">Snacks</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ $hasReservedSeat ? '' : 'disabled-btn' }}"
    onclick="addToCart('Lumpia Shanghai', 30)"
    {{ $hasReservedSeat ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱30</span>
        </div>
    </div>

</div>

<!-- BOTTOM NAV -->
<div class="bottom-nav d-flex justify-content-around">
    <div>Home</div>
    <div>Orders</div>
    <div>Profile</div>
</div>

<script>
let selectedCategory = "All";
let selectedPrice = "all";

/* ================= FILTER FIX ================= */

function filterCategory(category, btn) {
    selectedCategory = category;
    setActive(btn, "category");
    applyFilters();
}

function filterPrice(price, btn) {
    selectedPrice = price;
    setActive(btn, "price");
    applyFilters();
}

function setActive(btn, type) {
    let buttons;

    if (type === "category") {
        buttons = document.querySelectorAll(".category-btn");
    } else {
        buttons = document.querySelectorAll(".price-btn");
    }

    buttons.forEach(b => b.classList.remove("active"));
    btn.classList.add("active");
}

function applyFilters() {
    let items = document.querySelectorAll(".menu-item");

    items.forEach(item => {
        let category = item.getAttribute("data-category").toLowerCase();
        let price = parseFloat(item.getAttribute("data-price"));

        let categoryMatch =
            selectedCategory === "All" ||
            category === selectedCategory.toLowerCase();

        let priceMatch = false;

        switch (selectedPrice) {
            case "all": priceMatch = true; break;
            case "1-50": priceMatch = price >= 1 && price <= 50; break;
            case "51-100": priceMatch = price >= 51 && price <= 100; break;
            case "101-150": priceMatch = price >= 101 && price <= 150; break;
            case "151+": priceMatch = price >= 151; break;
        }

        item.style.display = (categoryMatch && priceMatch) ? "flex" : "none";
    });
}

/* ================= ADD TO CART ================= */

let cart = [];

function addToCart(name, price) {
    cart.push({ name, price });
    updateCartUI();
}

function updateCartUI() {
    document.getElementById("cart-count").innerText = cart.length;
}
</script>
</body>
</html>
