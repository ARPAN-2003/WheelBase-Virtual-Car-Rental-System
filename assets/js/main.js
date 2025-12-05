// main.js

// -------- ADMIN SIDEBAR SECTION SWITCHING --------

// This function shows one section and hides others.
// It will be used on the Admin Dashboard page.
function showAdminSection(sectionId, buttonElement) {
  // Hide all sections
  var sections = document.querySelectorAll(".section");
  sections.forEach(function (sec) {
    sec.classList.remove("active");
  });

  // Remove "active" class from all menu buttons
  var buttons = document.querySelectorAll(".admin-menu button");
  buttons.forEach(function (btn) {
    btn.classList.remove("active");
  });

  // Show the selected section
  var target = document.getElementById(sectionId);
  if (target) {
    target.classList.add("active");
  }

  // Highlight the clicked button
  if (buttonElement) {
    buttonElement.classList.add("active");
  }
}

// preselect registration role (if register.html loaded separately)
// already included as per earlier snippet in register page — this is just a central place if you prefer:
function preselectRoleFromParam(selectId='userType') {
  const params = new URLSearchParams(location.search);
  const role = params.get('role');
  if (role) {
    const sel = document.getElementById(selectId);
    if (sel) sel.value = role;
  }
}

// Set active header nav and ensure sidebar gets an active button
function setActiveNav() {
  const navLinks = document.querySelectorAll('.nav-links a');
  const current = location.pathname.split('/').pop(); // current page filename

  navLinks.forEach(link => {
    try {
      const linkName = new URL(link.href, location.origin).pathname.split('/').pop();
      if (linkName === current || (current === '' && (linkName === 'index.html' || linkName === ''))) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    } catch (e) {}
  });

  // If on customer pages try to mark the matching header (Dashboard/Browse/My Bookings)
  const path = location.pathname;
  if (path.includes('/customer/')) {
    if (path.includes('browse-cars')) {
      navLinks.forEach(a =>
      { 
        if (a.href.includes('browse-cars')) 
          a.classList.add('active'); 
      });
    }
    else if (path.includes('my-bookings')) {
      navLinks.forEach(a =>
        {
          if (a.href.includes('my-bookings'))
            a.classList.add('active');
        });
    }
    else {
      navLinks.forEach(a =>
        {
          if (a.href.includes('dashboard'))
            a.classList.add('active');
        });
    }
  }

  // Sidebar: ensure at least one button has active class for admin/retailer
  const sidebar = document.querySelector('.admin-menu');
  if (sidebar) {
    const anyActive = sidebar.querySelector('button.active, a.active');
    if (!anyActive) {
      const firstBtn = sidebar.querySelector('button, a');
      if (firstBtn)
        firstBtn.classList.add('active');
    }
  }
}

document.addEventListener('DOMContentLoaded', setActiveNav);

// Optional: You can add more simple JS later, e.g. form validation
