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

.hidden-item {
    display: none !important;
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

            @if(session('seat'))
                <div class="alert alert-success mt-2">
                    You reserved seat: <b>{{ session('seat') }}</b>
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

    <!-- MEALS CATEGORY -->
    <!-- ITEM 1 -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Meals" data-price="65">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/adobo.jpg') }}">
            <div>
                <b>Chicken Adobo Rice</b><br>
                <small class="text-muted">Meals</small>
            </div>
        </div>
        <div class="text-end">
    <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Chicken Adobo Rice', 65)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱65</span>
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
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Pancit Canton', 45)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱45</span>
        </div>
    </div>

    <!-- ITEM 3 - Fried Rice -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Meals" data-price="55">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/friedrice.jpg') }}">
            <div>
                <b>Fried Rice</b><br>
                <small class="text-muted">Meals</small>
            </div>
        </div>
        <div class="text-end">
<button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Fried Rice', 55)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱55</span>
        </div>
    </div>

    <!-- SNACKS CATEGORY -->
    <!-- ITEM 4 - Lumpia Shanghai -->
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
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Lumpia Shanghai', 30)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱30</span>
        </div>
    </div>

    <!-- ITEM 5 - Nachos -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Snacks" data-price="40">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/nachos.jpg') }}">
            <div>
                <b>Nachos with Cheese</b><br>
                <small class="text-muted">Snacks</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Nachos with Cheese', 40)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱40</span>
        </div>
    </div>

    <!-- ITEM 6 - Chips -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Snacks" data-price="25">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/chips.jpg') }}">
            <div>
                <b>Potato Chips</b><br>
                <small class="text-muted">Snacks</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Potato Chips', 25)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱25</span>
        </div>
    </div>

    <!-- BEVERAGES CATEGORY -->
    <!-- ITEM 7 - Iced Coffee -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Beverages" data-price="35">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/coffee.jpg') }}">
            <div>
                <b>Iced Coffee</b><br>
                <small class="text-muted">Beverages</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Iced Coffee', 35)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱35</span>
        </div>
    </div>

    <!-- ITEM 8 - Mango Shake -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Beverages" data-price="45">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/mango_shake.jpg') }}">
            <div>
                <b>Mango Shake</b><br>
                <small class="text-muted">Beverages</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Mango Shake', 45)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱45</span>
        </div>
    </div>

    <!-- ITEM 9 - Soft Drink -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Beverages" data-price="20">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/softdrink.jpg') }}">
            <div>
                <b>Soft Drink (250ml)</b><br>
                <small class="text-muted">Beverages</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Soft Drink', 20)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱20</span>
        </div>
    </div>

    <!-- DESSERTS CATEGORY -->
    <!-- ITEM 10 - Chocolate Cake -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Desserts" data-price="80">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/chocolate_cake.jpg') }}">
            <div>
                <b>Chocolate Cake Slice</b><br>
                <small class="text-muted">Desserts</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Chocolate Cake', 80)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱80</span>
        </div>
    </div>

    <!-- ITEM 11 - Leche Flan -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Desserts" data-price="75">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/leche_flan.jpg') }}">
            <div>
                <b>Leche Flan</b><br>
                <small class="text-muted">Desserts</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Leche Flan', 75)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱75</span>
        </div>
    </div>

    <!-- ITEM 12 - Ice Cream -->
    <div class="menu-item d-flex justify-content-between align-items-center"
         data-category="Desserts" data-price="50">
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/ice_cream.jpg') }}">
            <div>
                <b>Ice Cream Cup</b><br>
                <small class="text-muted">Desserts</small>
            </div>
        </div>
        <div class="text-end">
      <button
    class="btn btn-green mb-1 {{ session('seat') ? '' : 'disabled-btn' }}"
    onclick="addToCart('Ice Cream Cup', 50)"
    {{ session('seat') ? '' : 'disabled' }}>
    Add to Cart
</button><br>
            <span class="price">₱50</span>
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

// Initialize all items to be visible
function initializeItems() {
    const items = document.querySelectorAll("[data-category][data-price]");
    items.forEach(item => {
        item.style.display = "flex";
    });
}

function filterCategory(category, btn) {
    selectedCategory = category;

    // Update button styling
    document.querySelectorAll(".category-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    applyFilters();
}

function filterPrice(price, btn) {
    selectedPrice = price;

    // Update button styling
    document.querySelectorAll(".price-btn").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    applyFilters();
}

function applyFilters() {
    const items = document.querySelectorAll("[data-category][data-price]");

    console.log(`Applying filters: Category="${selectedCategory}", Price="${selectedPrice}", Items count: ${items.length}`);

    items.forEach((item, idx) => {
        const category = item.getAttribute("data-category");
        const price = parseFloat(item.getAttribute("data-price"));

        // Category filter
        let showByCategory = (selectedCategory === "All" || category === selectedCategory);

        // Price filter
        let showByPrice = true;
        if (selectedPrice === "1-50" && price > 50) showByPrice = false;
        if (selectedPrice === "51-100" && (price <= 50 || price > 100)) showByPrice = false;
        if (selectedPrice === "101-150" && (price <= 100 || price > 150)) showByPrice = false;
        if (selectedPrice === "151+" && price <= 150) showByPrice = false;

        // Apply filtering
        const shouldShow = showByCategory && showByPrice;

        if (shouldShow) {
            item.style.display = "flex";
            item.style.backgroundColor = "white";
        } else {
            item.style.display = "none";
            item.style.backgroundColor = "lightgray";
        }

        console.log(`[${idx}] ${category} ₱${price} - Show: ${shouldShow} (Cat: ${showByCategory}, Price: ${showByPrice})`);
    });
}

// Initialize on page load
window.addEventListener("load", initializeItems);
window.addEventListener("load", applyFilters);

let cart = [];

function addToCart(name, price) {
    cart.push({ name, price });
}
</script>
</body>
</html>
