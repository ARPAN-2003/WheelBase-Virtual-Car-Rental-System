<?php
    // customer/book.php
    session_start();
    // expose role to JS, and if logged-in but not customer, we'll still show modal that suggests login-as-customer
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Book Car — WheelBase</title>
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
        <a href="/WheelBase/customer/browse-cars.php">Browse Cars</a>
        <a href="/WheelBase/customer/my-bookings.php">My Bookings</a>
        <a href="/WheelBase/logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container dashboard-main">
    <div class="dashboard-header">
      <h1>Book Car</h1>
      <p class="small-text">Fill pickup / drop-off details. Payment later.</p>
    </div>

    <div class="table-wrapper">
      <!-- post to server endpoint book.php in project root -->
      <form method="post" action="/WheelBase/book.php" id="bookingForm">
        <div class="form-group">
          <label>Selected car</label>
          <input id="selected_car" type="text" readonly />
        </div>

        <div class="form-group">
          <label>City</label>
          <input id="selected_city" type="text" readonly />
        </div>

        <div class="form-group">
          <label>Location / Area</label>
          <input id="selected_location" type="text" readonly />
        </div>

        <div class="form-group">
          <label for="pickupDateTime">Pickup Date &amp; Time</label>
          <input id="pickupDateTime" name="pickup_datetime" type="datetime-local" required />
        </div>

        <div class="form-group">
          <label for="dropDateTime">Drop-off Date &amp; Time</label>
          <input id="dropDateTime" name="drop_datetime" type="datetime-local" required />
        </div>

        <!-- hidden inputs submitted to server -->
        <input id="car_reg_input" type="hidden" name="car_reg_no" />
        <input id="city_input" type="hidden" name="city" />
        <input id="location_input" type="hidden" name="location" />

        <div class="small-text" style="margin-top:8px;">
          Pickup &amp; drop details will be confirmed by phone on your registered number after booking.
        </div>

        <button class="btn btn-primary" type="submit" style="margin-top:12px;">Confirm Booking (Pending)</button>
      </form>
    </div>

    <!-- modal if logged-in role is not customer -->
    <div id="roleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; z-index:9999;">
      <div style="background:#fff; padding:22px; border-radius:8px; max-width:420px; width:90%; text-align:center;">
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
    // read URL params and prefill fields
    const params = new URLSearchParams(location.search);
    const car = params.get('car') || '';
    const city = params.get('city') || '';
    const loc = params.get('location') || '';
    const pickup = params.get('pickup') || '';
    const drop = params.get('drop') || '';

    // visible
    if (document.getElementById('selected_car')) {
      document.getElementById('selected_car').value = car ? car + ' (selected)' : 'No car selected';
    }
    if (document.getElementById('selected_city')) {
      document.getElementById('selected_city').value = city;
    }
    if (document.getElementById('selected_location')) {
      document.getElementById('selected_location').value = loc;
    }

    // hidden
    if (document.getElementById('car_reg_input')) document.getElementById('car_reg_input').value = car || '';
    if (document.getElementById('city_input')) document.getElementById('city_input').value = city || '';
    if (document.getElementById('location_input')) document.getElementById('location_input').value = loc || '';

    if (pickup && document.getElementById('pickupDateTime')) {
      document.getElementById('pickupDateTime').value = pickup;
    }
    if (drop && document.getElementById('dropDateTime')) {
      document.getElementById('dropDateTime').value = drop;
    }

    // role handling: if not customer, show modal and prevent form submit
    const role = window.USER_ROLE || null;
    const bookingForm = document.getElementById('bookingForm');

    function showModal(msg, nextUrl) {
      const modal = document.getElementById('roleModal');
      document.getElementById('roleModalMsg').textContent = msg;
      const loginLink = document.getElementById('roleModalLogin');
      if (nextUrl) {
        loginLink.href = '/WheelBase/auth/login.html?role=customer&next=' + encodeURIComponent(nextUrl);
      } else {
        loginLink.href = '/WheelBase/auth/login.html?role=customer';
      }
      modal.style.display = 'flex';
    }

    document.getElementById('roleModalClose').addEventListener('click', function(){ document.getElementById('roleModal').style.display='none'; });

    // check role at load; if not customer and logged-in (retailer/admin) -> suggest login-as-customer
    // if not logged in at all -> redirect to login with next param
    const bookingNext = '/WheelBase/customer/book.php' + location.search;
    if (!role) {
      // not logged in: redirect to login (preserve next so after login user returns here)
      window.location.href = '/WheelBase/auth/login.html?role=customer&next=' + encodeURIComponent(bookingNext);
    } else if (role !== 'customer') {
      // logged-in but not customer -> show modal and block form submit
      showModal('You are logged in as ' + role + '. You must login as a customer to book a car.', bookingNext);
      bookingForm.addEventListener('submit', function(e){ e.preventDefault(); showModal('You must login as a customer to book a car.', bookingNext); });
    }
    // else role === 'customer' -> do nothing, allow submit

  })();
  </script>

  <script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>