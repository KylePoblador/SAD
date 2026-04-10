<!DOCTYPE html>
<html>
<head>
    <title>Customer Feedbacks</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f2f2f2;
            margin:0;
        }

        .header{
            background:#ec4899;
            color:white;
            padding:20px;
        }

        .header h1{ margin:0; }

        .back{
            font-size:14px;
            margin-bottom:10px;
            display:inline-block;
            color:white;
            text-decoration:none;
        }

        .container{
            padding:20px;
            max-width:600px;
            margin:0 auto;
        }

        .rating-summary{
            background:white;
            padding:20px;
            border-radius:10px;
            margin-bottom:20px;
            text-align:center;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }

        .rating-value{
            font-size:48px;
            font-weight:bold;
            color:#fbbf24;
            margin-bottom:10px;
        }

        .rating-count{
            color:#666;
            font-size:14px;
        }

        .feedback-item{
            background:white;
            padding:15px;
            border-radius:10px;
            margin-bottom:15px;
            box-shadow:0 2px 5px rgba(0,0,0,0.1);
        }

        .feedback-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:10px;
        }

        .feedback-user{
            font-weight:bold;
            color:#333;
        }

        .feedback-rating{
            color:#fbbf24;
            font-size:14px;
        }

        .feedback-date{
            color:#999;
            font-size:12px;
            margin-bottom:8px;
        }

        .feedback-text{
            color:#666;
            line-height:1.5;
            font-size:14px;
        }

        .stars{
            color:#fbbf24;
            font-size:14px;
            letter-spacing:2px;
        }

        .filter-btn{
            background:#f3f4f6;
            border:1px solid #ddd;
            padding:8px 16px;
            border-radius:6px;
            cursor:pointer;
            margin-bottom:20px;
            transition:all 0.3s ease;
        }

        .filter-btn:hover{
            background:#e5e7eb;
        }

        .filter-btn.active{
            background:#ec4899;
            color:white;
            border-color:#ec4899;
        }
    </style>
</head>

<body>

<div class="header">
    <a href="{{ route('staff.dashboard') }}" class="back">← Back to Dashboard</a>
    <h1>Customer Feedbacks</h1>
</div>

<div class="container">

    <div class="rating-summary">
        <div class="rating-value">4.5</div>
        <div class="stars">★★★★☆</div>
        <div class="rating-count">Based on 48 reviews</div>
    </div>

    <div style="margin-bottom:20px; text-align:center;">
        <button class="filter-btn active">All (48)</button>
        <button class="filter-btn">5 Stars (28)</button>
        <button class="filter-btn">4 Stars (15)</button>
        <button class="filter-btn">Below 4 (5)</button>
    </div>

    <div class="feedback-item">
        <div class="feedback-header">
            <span class="feedback-user">Maria Santos</span>
            <span class="feedback-rating">★★★★★</span>
        </div>
        <div class="feedback-date">Apr 10, 2:30 PM</div>
        <div class="feedback-text">
            "Excellent food quality and very fast service! The staff was so friendly and accommodating. Highly recommended!"
        </div>
    </div>

    <div class="feedback-item">
        <div class="feedback-header">
            <span class="feedback-user">John Reyes</span>
            <span class="feedback-rating">★★★★☆</span>
        </div>
        <div class="feedback-date">Apr 09, 1:15 PM</div>
        <div class="feedback-text">
            "Good food and reasonable prices. Would be perfect if they had more variety in the menu."
        </div>
    </div>

    <div class="feedback-item">
        <div class="feedback-header">
            <span class="feedback-user">Anna Cruz</span>
            <span class="feedback-rating">★★★★★</span>
        </div>
        <div class="feedback-date">Apr 08, 12:45 PM</div>
        <div class="feedback-text">
            "The chicken adobo is delicious! Fresh ingredients and consistent quality every time I order."
        </div>
    </div>

    <div class="feedback-item">
        <div class="feedback-header">
            <span class="feedback-user">Paul Mercado</span>
            <span class="feedback-rating">★★★☆☆</span>
        </div>
        <div class="feedback-date">Apr 07, 3:20 PM</div>
        <div class="feedback-text">
            "Average experience. The food was okay but a bit salty. The service could be faster during lunch rush."
        </div>
    </div>

    <div class="feedback-item">
        <div class="feedback-header">
            <span class="feedback-user">Lisa Tan</span>
            <span class="feedback-rating">★★★★★</span>
        </div>
        <div class="feedback-date">Apr 06, 11:30 AM</div>
        <div class="feedback-text">
            "Best canteen in campus! Always fresh, clean, and the staff really care about customer satisfaction."
        </div>
    </div>

</div>

</body>
</html>
