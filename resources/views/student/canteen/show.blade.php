<x-app-layout>

<div class="max-w-4xl mx-auto p-6">

<h2 class="text-2xl font-bold mb-4">
{{ $canteenName }}
</h2>

@php
$reserved = session('seat_reserved', false);
@endphp

<div class="bg-gray-100 p-4 rounded-lg flex justify-between items-center mb-6">

<div>
<strong>Seat availability</strong>
<br>
12 / 25 Available
</div>

@if($reserved)

<span class="bg-green-300 px-4 py-2 rounded-lg font-semibold">
Seat Reserved
</span>
s
@else

<a href="{{ route('student.reserve',$college) }}"
class="bg-green-500 text-white px-4 py-2 rounded-lg">
Reserve Seat
</a>

@endif

</div>

<h3 class="text-xl font-semibold mb-3">Menu</h3>

@php

$menus = [

'ceit'=>[
['name'=>'Chicken Adobo Rice','price'=>65],
['name'=>'Pancit Canton','price'=>45],
['name'=>'Lumpia Shanghai','price'=>30],
],

'cass'=>[
['name'=>'Burger Meal','price'=>70],
['name'=>'Spaghetti','price'=>55],
['name'=>'Hotdog Sandwich','price'=>40],
],

'chefs'=>[
['name'=>'Fried Chicken Meal','price'=>85],
['name'=>'Sisig Rice','price'=>75],
['name'=>'Beef Tapa','price'=>90],
],

'cbdem'=>[
['name'=>'Club Sandwich','price'=>60],
['name'=>'Fries','price'=>35],
['name'=>'Milk Tea','price'=>50],
],

'cti'=>[
['name'=>'Pork BBQ Rice','price'=>70],
['name'=>'Chicken Curry','price'=>80],
['name'=>'Egg Sandwich','price'=>35],
]

];

@endphp


@foreach($menus[$college] as $food)

<div class="bg-white border rounded-lg p-4 flex justify-between items-center mb-3">

<div>

<strong>{{ $food['name'] }}</strong>

</div>

<div class="flex items-center gap-4">

@if($reserved)

<button class="bg-green-500 text-white px-4 py-1 rounded-lg">
Add to Cart
</button>

@else

<button class="bg-gray-400 text-white px-4 py-1 rounded-lg" disabled>
Reserve seat first
</button>

@endif

<span class="text-green-600 font-bold">
₱{{ $food['price'] }}
</span>

</div>

</div>

@endforeach

</div>

</x-app-layout>
