<?php
    // customer/browse-cars.php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Browse Cars – WheelBase</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <script>
    window.USER_ROLE = <?php echo isset($_SESSION['role']) ? json_encode($_SESSION['role']) : 'null'; ?>;
    window.USERNAME = <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>;
  </script>
</head>
<body>
  <header>
    <div class="container navbar">
      <div class="logo"><a href="/WheelBase/index.html">Wheel<span>Base</span></a></div>
      <nav class="nav-links">
        <a href="/WheelBase/customer/dashboard.php">Dashboard</a>
        <a href="/WheelBase/customer/browse-cars.php" class="active">Browse Cars</a>
        <a href="/WheelBase/customer/my-bookings.php">My Bookings</a>
        <a href="/WheelBase/logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container dashboard-main">
    <div class="dashboard-header">
      <h1>Browse Cars</h1>
      <p class="small-text">Below cars are sample UI. Later they will come from database.</p>
    </div>

    <!-- Search summary (filled from URL params if present) -->
    <div id="searchSummary" style="margin-bottom:18px; display:none;">
      <div class="card" style="padding:12px;">
        <div><strong>Search:</strong> <span id="sumCity"></span> — <span id="sumLocation"></span></div>
        <div class="small-text">Pickup: <span id="sumPickup"></span> • Drop: <span id="sumDrop"></span></div>
      </div>
    </div>

    <section class="card-grid" id="carsGrid">
      <!-- Example car 1 -->
      <div class="card">
        <img src="/WheelBase/img/sedan.jpeg" alt="Sedan" />
        <div class="card-title">WB10 AB 1234 – Honda City</div>
        <div class="card-text">Brand: Honda • 5 seats • ₹250 / hour</div>
        <br />
        <a href="/WheelBase/customer/book.php?car=WB10AB1234" class="btn btn-primary book-btn" data-car="WB10AB1234">Book now</a>
      </div>

      <!-- Example car 2 -->
      <div class="card">
        <img src="/WheelBase/img/suv.jpeg" alt="SUV" />
        <div class="card-title">WB11 CD 5678 – Mahindra XUV</div>
        <div class="card-text">Brand: Mahindra • 7 seats • ₹350 / hour</div>
        <br />
        <a href="/WheelBase/customer/book.php?car=WB11CD5678" class="btn btn-primary book-btn" data-car="WB11CD5678">Book now</a>
      </div>

      <!-- Example car 3 -->
      <div class="card">
        <img src="/WheelBase/img/hatchback.avif" alt="Hatchback" />
        <div class="card-title">WB09 EF 4321 – Maruti Swift</div>
        <div class="card-text">Brand: Maruti • 4 seats • ₹180 / hour</div>
        <br />
        <a href="/WheelBase/customer/book.php?car=WB09EF4321" class="btn btn-primary book-btn" data-car="WB09EF4321">Book now</a>
      </div>
    </section>

    <!-- Modal for Retailer/Admin trying to book -->
    <div id="roleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; z-index:9999;">
      <div style="background:#fff; padding:22px; border-radius:8px; max-width:420px; width:90%; box-shadow:0 10px 30px rgba(0,0,0,0.2); text-align:center;">
        <h3 style="margin-top:0;">Cannot Book</h3>
        <p id="roleModalMsg">You must login as a customer to book a car.</p>
        <div style="margin-top:18px; display:flex; gap:8px; justify-content:center;">
          <button id="roleModalClose" class="btn btn-outline">Close</button>
          <a id="roleModalLogin" class="btn btn-primary" href="/WheelBase/auth/login.html?role=customer">Login as Customer</a>
        </div>
      </div>
    </div>

  </main>

  <script>
  (function () {
    // show search summary if params present
    const params = new URLSearchParams(location.search);
    const city = params.get('city') || '';
    const locationName = params.get('location') || '';
    const pickup = params.get('pickup') || '';
    const drop = params.get('drop') || '';

    if (city && locationName && pickup && drop) {
      document.getElementById('sumCity').textContent = city;
      document.getElementById('sumLocation').textContent = locationName;
      try {
        document.getElementById('sumPickup').textContent = new Date(pickup).toLocaleString();
        document.getElementById('sumDrop').textContent = new Date(drop).toLocaleString();
      } catch (e) {
        document.getElementById('sumPickup').textContent = pickup;
        document.getElementById('sumDrop').textContent = drop;
      }
      document.getElementById('searchSummary').style.display = 'block';
    }

    // Keep params so Book links include them in their href (append)
    const extra = params.toString();
    if (extra) {
      document.querySelectorAll('.book-btn').forEach(function (a) {
        const href = a.getAttribute('href') || '';
        if (href.indexOf('?') === -1) a.setAttribute('href', href + '?' + extra);
        else a.setAttribute('href', href + '&' + extra);
      });
    }

    // Book button behavior: block retailer/admin; redirect anonymous to login with next param
    document.body.addEventListener('click', function(ev){
      const a = ev.target.closest('.book-btn');
      if (!a) return;

      const car = a.dataset.car || (new URL(a.href, location.href)).searchParams.get('car') || '';
      const role = window.USER_ROLE || null;

      // helper to create next url for booking
      function bookingNextUrl() {
        // preserve existing query params if any
        const href = a.getAttribute('href') || '';
        // If href already has query containing city/location/pickup/drop it will be included
        return href || ('/WheelBase/customer/book.php?car=' + encodeURIComponent(car));
      }

      if (!role) {
        // not logged in: redirect to login with role=customer and next param
        ev.preventDefault();
        const next = bookingNextUrl();
        window.location.href = '/WheelBase/auth/login.html?role=customer&next=' + encodeURIComponent(next);
        return;
      }

      if (role === 'retailer' || role === 'admin') {
        // show modal instructing to login as customer
        ev.preventDefault();
        const modal = document.getElementById('roleModal');
        document.getElementById('roleModalMsg').textContent = 'You must login as a customer to book a car.';
        const loginLink = document.getElementById('roleModalLogin');
        loginLink.href = '/WheelBase/auth/login.html?role=customer&next=' + encodeURIComponent(bookingNextUrl());
        modal.style.display = 'flex';
        return;
      }

      // role === 'customer' => allow normal link navigation
    });

    // modal close button
    document.getElementById('roleModalClose').addEventListener('click', function(){ document.getElementById('roleModal').style.display = 'none'; });

  })();
  </script>

  <script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>