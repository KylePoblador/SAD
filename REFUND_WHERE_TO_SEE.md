# 📍 WHERE TO SEE REFUNDS ON THE WEBSITE

## 🎯 Quick Navigation

### ✅ **STAFF** - Issue & View Refunds

**Location:** Staff Dashboard → **Refunds** (Quick Actions)

```
URL: /staff/refunds
```

**Features:**

- ✅ Issue refund form (search student, enter amount, add reason)
- ✅ Real-time refund history (auto-updates every 30 seconds)
- ✅ Confirmation when refund processed
- ✅ New balance displayed immediately

---

### ✅ **STUDENTS** - View Received Refunds

**Location:** Student Wallet → **My Refunds** (Card at bottom)

```
URL: /student/refunds
```

**Features:**

- ✅ Total refunded amount
- ✅ Refund count
- ✅ List of all refunds received
- ✅ Who refunded it (staff name)
- ✅ Reason for refund
- ✅ Date & time of refund

---

### ✅ **ADMIN** - View All Refunds

**Location:** Admin Dashboard → URL

```
URL: /admin/refunds
```

**Features:**

- ✅ All system refunds (paginated)
- ✅ Filter by staff or student
- ✅ Full audit trail

---

## 📸 SCREENSHOTS OF WHERE TO CLICK

### Staff Dashboard

```
┌─────────────────────────────────────────┐
│ Staff Dashboard                         │
├─────────────────────────────────────────┤
│ [Today's Orders] [Revenue] [Seats] ...  │
├─────────────────────────────────────────┤
│ Quick Actions:                          │
│ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐   │
│ │Orders│ │Menu  │ │Wallet│ │Seats │   │
│ └──────┘ └──────┘ └──────┘ └──────┘   │
│ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐   │
│ │Feed..│ │QR Sc.│ │Report│ │REFUND│ ◄─ CLICK HERE
│ └──────┘ └──────┘ └──────┘ └──────┘   │
└─────────────────────────────────────────┘
```

### Student Wallet Page

```
┌─────────────────────────────────────────┐
│ My Wallet                               │
├─────────────────────────────────────────┤
│ Balance: ₱500.00                        │
├─────────────────────────────────────────┤
│ [Load Wallet Button]                    │
│ [Connect · Share Coins]                 │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ MY REFUNDS                          │ │ ◄─ CLICK HERE
│ │ View all refunds you've received    │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ [Recent Transactions]                   │
└─────────────────────────────────────────┘
```

---

## 🚀 HOW TO USE EACH PAGE

### Staff Refund Page (/staff/refunds)

**Step 1: Search for Student**

```
Search Box: "Student Name or ID"
↓
System shows matching students
↓
Click to select
```

**Step 2: Enter Refund Details**

```
Amount: 500.00
Reason: "Double charged for wallet load"
```

**Step 3: Click Issue Refund**

```
✅ Success message with new balance
```

**Step 4: View History**

```
Automatically shows below with:
- Student name
- Amount refunded
- Reason
- Date/Time
- Status (Refunded)
```

---

### Student Refund Page (/student/refunds)

**Shows:**

```
┌─────────────────────────────────────┐
│ Total Refunded: ₱500.00              │
│ Refunds Received: 1                 │
├─────────────────────────────────────┤
│ From: John Doe (Staff)              │
│ Amount: +₱500.00                    │
│ Reason: Double charged              │
│ Date: 2026-05-04 23:26:29          │
└─────────────────────────────────────┘
```

---

## 💡 REAL-WORLD EXAMPLE

### Scenario: Student Was Charged Twice

**1. Student notices double charge**

- Balance: ₱1000 (should be ₱500)

**2. Student asks staff for help**

**3. Staff goes to: /staff/refunds**

- Searches for student name
- Enters: Amount ₱500, Reason "Duplicate charge"
- Clicks "Issue Refund"

**4. System processes:**

- ✅ Creates refund record
- ✅ Updates student wallet (+₱500)
- ✅ Shows confirmation

**5. Student can see it at: /student/refunds**

- ✅ Refund appears in history
- ✅ Shows who refunded them
- ✅ Shows reason
- ✅ Shows date/time

---

## 🔗 ALL URLs

| Page            | URL                             | Role    | Purpose                    |
| --------------- | ------------------------------- | ------- | -------------------------- |
| Issue Refund    | `/staff/refunds`                | Staff   | Issue refund to student    |
| Refund History  | `/staff/refunds/history`        | Staff   | API - get issued refunds   |
| My Refunds      | `/student/refunds`              | Student | View received refunds      |
| Student History | `/student/refunds/history/{id}` | Student | API - get received refunds |
| All Refunds     | `/admin/refunds`                | Admin   | View all system refunds    |

---

## 📱 Mobile Friendly

All refund pages are **fully responsive**:

- ✅ Works on mobile
- ✅ Works on tablet
- ✅ Works on desktop

---

## ✨ Features at Each Page

### Staff Refund Management Page

- 🔍 Search students by name/ID
- 💰 Enter refund amount
- 📝 Add reason for refund
- ✅ Issue refund button
- 📊 View full refund history below
- 🔄 Auto-refreshes every 30 seconds

### Student Refund History Page

- 💵 Total refunded amount
- 🔢 Count of refunds
- 👤 Staff member name who refunded
- 📝 Reason for each refund
- 📅 Date & time of refund
- 🎯 Status (Refunded)

---

## 🎯 NEXT STEPS

1. **Run migration:**

    ```bash
    php artisan migrate
    ```

2. **Test as Staff:**
    - Go to Staff Dashboard
    - Click "Refunds" card
    - Try issuing a test refund

3. **Test as Student:**
    - Go to Student Wallet
    - Click "My Refunds" card
    - See your refund history

4. **Done!** System is live

---

## ✅ NOW YOU CAN:

✅ Issue refunds from staff dashboard
✅ View refund history as staff
✅ Students see their refunds in wallet
✅ All transactions recorded
✅ Full audit trail
✅ Automatic balance updates

**Status:** 🎉 READY TO USE!
