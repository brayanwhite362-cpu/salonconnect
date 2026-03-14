<?php
if (defined("SALONCONNECT_FOOTER_RENDERED")) { return; }
define("SALONCONNECT_FOOTER_RENDERED", true);
?>

<footer class="py-4 mt-5">
  <div class="container d-flex justify-content-between flex-wrap gap-2">
    <div class="muted small">
      © <?= date("Y") ?> SalonConnect · Luxury booking platform
    </div>
    <div class="small">
      <a class="muted me-3" href="/about.php">About</a>
      <a class="muted" href="/contact.php">Contact</a>
    </div>
  </div>
</footer>

<!-- Loader hide and form loading script -->
<script>
// Hide loader immediately
(function() {
  const loader = document.getElementById('pageLoader');
  if (loader) {
    loader.classList.remove('show');
  }
})();

// Hide on load
window.addEventListener('load', function() {
  const loader = document.getElementById('pageLoader');
  if (loader) {
    loader.classList.remove('show');
  }
});

// Hide on DOM ready
document.addEventListener('DOMContentLoaded', function() {
  const loader = document.getElementById('pageLoader');
  if (loader) {
    loader.classList.remove('show');
  }
  
  // Add loading state to forms
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        const originalText = submitBtn.textContent;
        submitBtn.dataset.originalText = originalText;
        submitBtn.classList.add('btn-loading');
        
        if (originalText.toLowerCase().includes('login')) {
          submitBtn.textContent = 'Logging in...';
        } else if (originalText.toLowerCase().includes('register')) {
          submitBtn.textContent = 'Creating account...';
        } else if (originalText.toLowerCase().includes('book')) {
          submitBtn.textContent = 'Booking...';
        } else if (originalText.toLowerCase().includes('add to cart')) {
          submitBtn.textContent = 'Adding...';
        } else {
          submitBtn.textContent = 'Processing...';
        }
      }
    });
  });
});

// Fallback hide after 1 second
setTimeout(function() {
  const loader = document.getElementById('pageLoader');
  if (loader) {
    loader.classList.remove('show');
  }
}, 1000);

// Page transition for links
document.addEventListener('DOMContentLoaded', function() {
  const loader = document.getElementById('pageLoader');
  if (!loader) return;
  
  document.addEventListener('click', function(e) {
    const link = e.target.closest('a');
    if (!link) return;
    
    // Skip external links, target blank, etc.
    if (link.hostname !== window.location.hostname || 
        link.hasAttribute('target') || 
        link.getAttribute('href') === '#' ||
        link.getAttribute('href') === 'javascript:void(0)') {
      return;
    }
    
    // Don't show loader for "Use My Location" button
    if (link.classList.contains('btn-outline-gold') && link.textContent.includes('Use My Location')) {
      return;
    }
    
    e.preventDefault();
    
    // Set loading message
    const loadingMessage = document.getElementById('loadingMessage');
    if (loadingMessage) {
      const linkText = link.innerText.toLowerCase();
      const linkHref = link.getAttribute('href') || '';
      
      if (linkHref.includes('salon.php') || linkText.includes('salon')) {
        loadingMessage.textContent = 'Loading salon details...';
      } else if (linkHref.includes('book') || linkText.includes('book')) {
        loadingMessage.textContent = 'Preparing booking...';
      } else if (linkHref.includes('cart') || linkText.includes('cart')) {
        loadingMessage.textContent = 'Loading your cart...';
      } else if (linkHref.includes('profile') || linkText.includes('profile')) {
        loadingMessage.textContent = 'Loading profile...';
      } else {
        loadingMessage.textContent = 'Loading...';
      }
    }
    
    loader.classList.add('show');
    
    setTimeout(() => {
      window.location.href = link.href;
    }, 300);
  });
});
</script>

</body>
</html>