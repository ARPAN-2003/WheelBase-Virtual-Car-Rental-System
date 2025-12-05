<?php
    // index.php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>WheelBase – Online Car Rental System</title>
  <link rel="stylesheet" href="/WheelBase/assets/css/style.css" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
</head>
<body>
  <header>
    <div class="container navbar">
      <div class="logo">Wheel<span>Base</span></div>
      <nav class="nav-links" id="mainNav">
        <a href="/WheelBase/index.php">Home</a>
        <a id="navLogin" href="/WheelBase/auth/login.html">Login</a>
        <a id="navRegister" href="/WheelBase/auth/register.html">Register</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <section class="hero">
      <div>
        <h1 class="hero-title">Unlock the freedom of the road.</h1>
        <p class="hero-subtitle">
          WheelBase lets customers, retailers and admins manage car rentals in one simple portal.
        </p>

        <div class="hero-actions">
          <a href="/WheelBase/auth/register.html" class="btn btn-primary">Get Started</a>
          <a href="/WheelBase/auth/login.html" class="btn btn-outline">Login</a>
        </div>

        <p class="hero-note">
          Customers can browse cars and book trips. Retailers can list their cars. Admin monitors bookings & revenue.
        </p>
      </div>

      <!-- QUICK TRIP SEARCH CARD -->
      <div class="hero-card" style="padding:22px 22px;">
        <h3>Quick trip search (demo only)</h3>
        <p class="small-text">This is UI-first. After you test the backend, search will use real data.</p>

        <div class="form-group">
          <label for="city">City</label>
          <select id="city" name="city" required>
            <option value="">-- Select city --</option>
            <option value="Kolkata">🏙️ Kolkata</option>
            <option value="New Delhi">🏛️ New Delhi</option>
            <option value="Bengaluru">🌆 Bengaluru</option>
            <option value="Mumbai">🌉 Mumbai</option>
            <option value="Chennai">🌴 Chennai</option>
          </select>
        </div>

        <div class="form-group">
          <label for="location">Location / Area</label>
          <select id="location" name="location" required>
            <option value="">-- Select location --</option>
          </select>
        </div>

        <div class="form-group">
          <label for="pickup-date">Pickup date &amp; time</label>
          <input id="pickup-date" type="datetime-local" />
        </div>

        <div class="form-group">
          <label for="drop-date">Drop-off date &amp; time</label>
          <input id="drop-date" type="datetime-local" />
        </div>

        <div style="margin-top:8px; font-style: italic;" class="small-text">
          For car pickup and drop-off we will call you on your registered phone number to confirm pickup details.
        </div>

        <div style="margin-top:12px; display:flex; gap:8px; align-items:center;">
          <button id="searchQuick" class="btn btn-primary" type="button"> Search Cars</button>
        </div>
      </div>
    </section>

    <section>
      <h2>Popular rentals</h2>
      <div class="card-grid">
        <div class="card">
          <img src="/WheelBase/img/sedan.jpeg" alt="Sedan" />
          <div class="card-title">City Comfort</div>
          <div class="card-text">Sedan • 5 seats • From ₹250 / hour</div>
        </div>
        <div class="card">
          <img src="/WheelBase/img/suv.jpeg" alt="SUV" />
          <div class="card-title">Family SUV</div>
          <div class="card-text">SUV • 7 seats • From ₹350 / hour</div>
        </div>
        <div class="card">
          <img src="/WheelBase/img/hatchback.avif" alt="Hatchback" />
          <div class="card-title">Budget Ride</div>
          <div class="card-text">Hatchback • 4 seats • From ₹180 / hour</div>
        </div>
      </div>

      <div class="card-grid">
        <div class="card">
          <img src="/WheelBase/img/honda.jpg" alt="Honda" />
          <div class="card-title">Honda</div>
          <div class="card-text">Honda • 5 seats • From ₹250 / hour</div>
        </div>
        <div class="card">
          <img src="/WheelBase/img/hyundai.avif" alt="Hyundai" />
          <div class="card-title">Hyundai</div>
          <div class="card-text">Hyundai • 7 seats • From ₹350 / hour</div>
        </div>
        <div class="card">
          <img src="/WheelBase/img/skoda.jpeg" alt="Skoda" />
          <div class="card-title">Skoda</div>
          <div class="card-text">Skoda • 4 seats • From ₹180 / hour</div>
        </div>
      </div>

      <div style="text-align:center; margin: 10px 0 40px;">
        <a href="/WheelBase/customer/browse-cars.php" class="btn btn-primary">Browse all cars</a>
      </div>
    </section>

    <!-- Join as retailer card -->
    <section class="container" style="margin-bottom:30px;">
      <div class="card" style="display:flex; align-items:center; justify-content:space-between; gap:18px;">
        <div>
          <div style="font-weight:700; font-size:1.1rem;">Own cars? Join as a retailer</div>
          <div class="small-text" style="margin-top:6px;">List your cars and earn. Quick signup for retailers.</div>
        </div>
        <div>
          <a href="/WheelBase/auth/register.html?role=retailer" class="btn btn-primary">Join as retailer</a>
        </div>
      </div>
    </section>

    <!-- 4-feature strip -->
    <section style="background:#fff; padding:18px 0; margin-bottom:30px;">
      <div class="container" style="display:flex; gap:20px; justify-content:space-between; align-items:center; flex-wrap:wrap;">
        <div style="flex:1; display:flex; gap:12px; align-items:center;">
          <img src="/WheelBase/img/shield.jpg" alt="shield" style="width:44px;height:44px" />
          <div>
            <div style="font-weight:700">100%</div>
            <div class="small-text">Hassle free Secured Trip</div>
          </div>
        </div>

        <div style="flex:1; display:flex; gap:12px; align-items:center;">
          <img src="/WheelBase/img/star.jpg" alt="star" style="width:60px;height:60px" />
          <div>
            <div style="font-weight:700">25000+</div>
            <div class="small-text">Quality cars in the city</div>
          </div>
        </div>

        <div style="flex:1; display:flex; gap:12px; align-items:center;">
          <img src="/WheelBase/img/delivery.jpg" alt="delivery" style="width:70px;height:58px" />
          <div>
            <div style="font-weight:700">Delivery</div>
            <div class="small-text">Anywhere, Anytime</div>
          </div>
        </div>

        <div style="flex:1; display:flex; gap:12px; align-items:center;">
          <img src="/WheelBase/img/clock.png" alt="clock" style="width:50px;height:50px" />
          <div>
            <div style="font-weight:700">Endless</div>
            <div class="small-text">Pay by hour, drive limitless</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="container" style="display:flex; gap:30px; flex-wrap:wrap; justify-content:space-between; align-items:flex-start;">
        <div style="flex:1; min-width:220px;">
          <h3 style="color:#fff">About us</h3>
          <p class="small-text" style="color:#cbd5e1; margin-top:6px;">
            WheelBase is a demo car rental portal. (Real functionality will work after backend.)
          </p>
        </div>

        <div style="flex:1; min-width:160px;">
          <h3 style="color:#fff">Our services</h3>
          <ul style="margin-top:6px; color:#cbd5e1; list-style:none; padding-left:0;">
            <li>Hourly rentals</li>
            <li>Outstation trips</li>
            <li>Retailer listing</li>
          </ul>
        </div>

        <div style="flex:1; min-width:160px;">
          <h3 style="color:#fff">Help & support</h3>
          <ul style="margin-top:6px; color:#cbd5e1; list-style:none; padding-left:0;">
            <li>FAQ</li>
            <li>Contact us</li>
            <li>Terms & privacy</li>
          </ul>
        </div>

        <div style="min-width:160px;">
          <h3 style="color:#fff">Follow</h3>
          <div style="margin-top:6px;">
            <a href="#" style="color:#cbd5e1; margin-right:8px;">Twitter</a>
            <a href="#" style="color:#cbd5e1; margin-right:8px;">Facebook</a>
            <a href="#" style="color:#cbd5e1;">Instagram</a>
          </div>
        </div>
      </div>

      <div style="text-align:center; margin-top:18px; color:#9aa7b8;">© WheelBase · Demo</div>
    </footer>
  </main>

  <script>
    // expose server-side session role to client
    window.USER_ROLE = <?php echo json_encode($_SESSION['role'] ?? null); ?>;
    window.USERNAME = <?php echo json_encode($_SESSION['username'] ?? null); ?>;

    /* ========== City -> Location data mapping ========== */
    const cityLocations = {
      "Kolkata": ["Salt Lake", "Garia", "Howrah", "Ballygunge", "Dumdum"],
      "New Delhi": ["Connaught Place", "Karol Bagh", "Chanakyapuri", "Saket", "Dwarka"],
      "Bengaluru": ["MG Road", "Koramangala", "Whitefield", "Indiranagar", "BTM Layout"],
      "Mumbai": ["Andheri", "Bandra", "Colaba", "Juhu", "BKC"],
      "Chennai": ["T Nagar", "Adyar", "Velachery", "Anna Nagar", "Chromepet"]
    };

    function fillLocationsFor(city) {
      const locSelect = document.getElementById('location');
      if (!locSelect) return;
      locSelect.innerHTML = '<option value="">-- Select location --</option>';
      if (!city || !cityLocations[city]) return;
      cityLocations[city].forEach(loc => {
        const opt = document.createElement('option');
        opt.value = loc;
        opt.textContent = loc;
        locSelect.appendChild(opt);
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      const citySel = document.getElementById('city');
      if (citySel) {
        citySel.addEventListener('change', function () {
          fillLocationsFor(this.value);
        });
      }

      const searchBtn = document.getElementById('searchQuick');
      if (searchBtn) {
        searchBtn.addEventListener('click', function () {
          const city = document.getElementById('city').value;
          const location = document.getElementById('location').value;
          const pickup = document.getElementById('pickup-date').value;
          const drop = document.getElementById('drop-date').value;

          if (!city || !location || !pickup || !drop) {
            alert('Please select city, location, pickup and drop-off date/time.');
            return;
          }

          // build URL with params
          const params = new URLSearchParams({
            city: city,
            location: location,
            pickup: pickup,
            drop: drop
          });
          const browseUrl = '/WheelBase/customer/browse-cars.php?' + params.toString();

          // if not logged in, send to login with role=customer and next param
          if (!window.USER_ROLE) {
            window.location.href = '/WheelBase/auth/login.html?role=customer&next=' + encodeURIComponent(browseUrl);
            return;
          }

          // if logged in as customer: go directly
          if (window.USER_ROLE === 'customer') {
            window.location.href = browseUrl;
            return;
          }

          // logged-in retailer/admin: still allow browsing (booking will be blocked later)
          window.location.href = browseUrl;
        });
      }

      // update navbar links if already logged in
      (function updateNav() {
        const navLogin = document.getElementById('navLogin');
        const navRegister = document.getElementById('navRegister');
        if (window.USER_ROLE) {
          // replace Login/Register with role-specific links
          const nav = document.getElementById('mainNav');
          navLogin.style.display = 'none';
          navRegister.style.display = 'none';
          const dashLink = document.createElement('a');
          dashLink.href = (window.USER_ROLE === 'customer') ? '/WheelBase/customer/dashboard.php' : (window.USER_ROLE === 'retailer' ? '/WheelBase/retailer/dashboard.php' : '/WheelBase/admin/dashboard.php');
          dashLink.textContent = 'Dashboard';
          nav.appendChild(dashLink);

          const logout = document.createElement('a');
          logout.href = '/WheelBase/logout.php';
          logout.textContent = 'Logout';
          nav.appendChild(logout);
        }
      })();

    });
  </script>

  <script src="/WheelBase/assets/js/main.js"></script>
</body>
</html>