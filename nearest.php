<?php
require_once "config/init.php";
require_once "config/db.php";

$userLat = $_GET['lat'] ?? null;
$userLng = $_GET['lng'] ?? null;

if (!$userLat || !$userLng) {
  header("Location: index.php");
  exit;
}

// Format coordinates to 4 decimal places for cleaner URL (optional)
$userLat = number_format((float)$userLat, 6);
$userLng = number_format((float)$userLng, 6);

// First check if any salons have coordinates
$check = $conn->query("SELECT COUNT(*) as count FROM salons WHERE lat IS NOT NULL AND lng IS NOT NULL");
$hasCoords = $check->fetch_assoc()['count'];

// Haversine distance in KM
$sql = "
SELECT id, name, address, phone, description, lat, lng,
(
  6371 * acos(
    cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?))
    + sin(radians(?)) * sin(radians(lat))
  )
) AS distance
FROM salons
WHERE status='active' AND lat IS NOT NULL AND lng IS NOT NULL
ORDER BY distance ASC
LIMIT 24
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ddd", $userLat, $userLng, $userLat);
$stmt->execute();
$result = $stmt->get_result();

// Salon images array
$salonImages = [
  1 => "https://images.pexels.com/photos/1813272/pexels-photo-1813272.jpeg?auto=compress&cs=tinysrgb&w=600",
  2 => "https://images.pexels.com/photos/897270/pexels-photo-897270.jpeg?auto=compress&cs=tinysrgb&w=600",
  3 => "https://images.pexels.com/photos/3993449/pexels-photo-3993449.jpeg?auto=compress&cs=tinysrgb&w=600",
  4 => "https://images.pexels.com/photos/3997374/pexels-photo-3997374.jpeg?auto=compress&cs=tinysrgb&w=600",
];

// Count results
$salonCount = $result->num_rows;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nearest Salons | SalonConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" />
  <link rel="stylesheet" href="assets/css/navbar.css">
  <style>
    :root{
      --bg:#0b0b12;
      --text:#f5f4ff;
      --muted:#b8b6c8;
      --gold:#c8a14a;
      --accent:#7b2cbf;
    }
    body{ background: var(--bg); color: var(--text); }
    .muted{ color: var(--muted); }
    
    .page-header {
      background: rgba(255,255,255,.02);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 24px;
      padding: 30px;
      margin-bottom: 30px;
    }
    
    /* Map Styles */
    #map {
      height: 450px;
      border-radius: 20px;
      margin-bottom: 30px;
      border: 1px solid rgba(200,161,74,0.3);
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    .salon-card {
      background: rgba(255,255,255,.03);
      border: 1px solid rgba(255,255,255,.06);
      border-radius: 20px;
      overflow: hidden;
      transition: transform .2s ease, border-color .2s ease;
      height: 100%;
    }
    
    .salon-card:hover{ 
      transform: translateY(-5px); 
      border-color: rgba(200,161,74,.35); 
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .salon-thumb {
      height: 180px;
      background-size: cover;
      background-position: center;
      position: relative;
    }
    
    .salon-thumb::after{
      content:"";
      position:absolute; inset:0;
      background: linear-gradient(180deg, rgba(0,0,0,.0), rgba(0,0,0,.65));
    }
    
    .distance-badge {
      display:inline-flex;
      align-items:center;
      gap:6px;
      padding: 6px 12px;
      border-radius: 999px;
      border: 1px solid rgba(200,161,74,.35);
      color: var(--gold);
      font-size: 12px;
      background: rgba(200,161,74,.08);
      position: absolute;
      top: 10px;
      right: 10px;
      z-index: 2;
    }
    
    .btn-view {
      background: linear-gradient(90deg, var(--accent), #9d4edd);
      border: none;
      color: white;
      padding: 8px 16px;
      border-radius: 30px;
      text-decoration: none;
      display: inline-block;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn-view:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(123,44,191,.3);
      color: white;
    }
    
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: rgba(255,255,255,.02);
      border-radius: 30px;
      border: 1px dashed rgba(255,255,255,.1);
      margin: 40px 0;
    }
    
    .btn-gold {
      background: linear-gradient(90deg, var(--accent), #9d4edd);
      color: white;
      padding: 12px 30px;
      border-radius: 40px;
      text-decoration: none;
      display: inline-block;
    }
    
    .back-link {
      color: var(--gold);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .back-link:hover {
      text-decoration: underline;
    }
    
    .badge-count {
      background: rgba(200,161,74,.2);
      color: var(--gold);
      padding: 5px 15px;
      border-radius: 30px;
      font-size: 13px;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 10px;
    }

    .legend {
      display: flex;
      gap: 20px;
      margin-bottom: 15px;
      padding: 10px 15px;
      background: rgba(255,255,255,.03);
      border-radius: 40px;
      width: fit-content;
    }

    .legend-item {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .legend-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
    }

    .legend-dot.blue { background: #4285F4; }
    .legend-dot.red { background: #db4437; }
    .legend-dot.purple { background: #7b2cbf; }

    .search-controls {
      margin: 20px 0;
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }

    .search-btn {
      background: linear-gradient(90deg, #7b2cbf, #9d4edd);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 30px;
      cursor: pointer;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
    }

    .search-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(123,44,191,0.4);
    }

    .radius-select {
      background: #1a1a2a;
      color: white;
      padding: 10px 20px;
      border-radius: 30px;
      border: 1px solid rgba(200,161,74,0.3);
      font-weight: 500;
      cursor: pointer;
    }

    .radius-select:focus {
      outline: none;
      border-color: var(--gold);
    }

    .results-count {
      background: rgba(200,161,74,0.1);
      padding: 8px 16px;
      border-radius: 30px;
      font-size: 14px;
      color: var(--gold);
    }
  </style>
</head>
<body>
<?php include "includes/navbar.php"; ?>

<main class="container py-4">
  
  <!-- Header - URL parameters are NOT shown here -->
  <div class="page-header d-flex justify-content-between align-items-center">
    <div>
      <span class="badge-count">
        📍 FOUND <?= $salonCount ?> SALONS NEAR YOU
      </span>
      <h2 class="fw-bold mb-2">Salons Near Your Location</h2>
      <p class="muted mb-0">Sorted by distance from your current location</p>
    </div>
    <div class="d-flex gap-2">
      <a href="index.php#salons" class="back-link">
        <span class="material-symbols-rounded">arrow_back</span>
        Back to All Salons
      </a>
    </div>
  </div>
  
  <?php if ($hasCoords == 0): ?>
    <!-- No coordinates at all -->
    <div class="empty-state">
      <span class="material-symbols-rounded" style="font-size:60px; color:var(--gold); opacity:0.3;">map</span>
      <h4 class="mt-3 mb-2">No location data available</h4>
      <p class="muted mb-4">Salon coordinates haven't been added yet. Please check back later.</p>
      <a href="index.php" class="btn-gold">Browse All Salons</a>
    </div>
    
  <?php elseif($result->num_rows === 0): ?>
    <!-- Has some coordinates but none near you -->
    <div class="empty-state">
      <span class="material-symbols-rounded" style="font-size:60px; color:var(--gold); opacity:0.3;">location_off</span>
      <h4 class="mt-3 mb-2">No salons found near you</h4>
      <p class="muted mb-4">Try adjusting your location or browse all salons.</p>
      <a href="index.php#salons" class="btn-gold">Browse All Salons</a>
    </div>
    
  <?php else: ?>
    
    <!-- Map Legend -->
    <div class="legend">
      <div class="legend-item">
        <div class="legend-dot blue"></div>
        <span class="muted">Your Location</span>
      </div>
      <div class="legend-item">
        <div class="legend-dot red"></div>
        <span class="muted">Real Salons (Google)</span>
      </div>
      <div class="legend-item">
        <div class="legend-dot purple"></div>
        <span class="muted">Our Partner Salons</span>
      </div>
    </div>
    
    <!-- Google Map -->
    <div id="map"></div>
    
    <!-- Search Controls -->
    <div class="search-controls">
      <button class="search-btn" onclick="findRealSalons()">
        <span class="material-symbols-rounded">search</span>
        Find Real Salons Near Me
      </button>
      <select id="radiusSelect" class="radius-select">
        <option value="2000">2 km radius</option>
        <option value="5000" selected>5 km radius</option>
        <option value="10000">10 km radius</option>
        <option value="20000">20 km radius</option>
      </select>
      <div id="resultCount" class="results-count">Ready to search...</div>
    </div>
    
    <!-- Show salons with distance -->
    <h3 class="fw-bold mt-5 mb-3">Our Partner Salons</h3>
    <div class="row g-4">
      <?php 
      // Reset result pointer for the grid display
      $stmt->execute();
      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()): 
      ?>
        <?php
          $sid = (int)$row["id"];
          $img = $salonImages[$sid] ?? "https://images.pexels.com/photos/6629882/pexels-photo-6629882.jpeg?auto=compress&cs=tinysrgb&w=600";
          $dist = round((float)$row["distance"], 1);
        ?>
        <div class="col-md-6 col-lg-3">
          <div class="salon-card">
            <div class="position-relative">
              <div class="salon-thumb" style="background-image:url('<?= $img ?>');"></div>
              <span class="distance-badge">
                <span class="material-symbols-rounded" style="font-size:14px;">near_me</span>
                <?= $dist ?> km
              </span>
            </div>
            <div class="p-3">
              <h5 class="fw-semibold"><?= htmlspecialchars($row["name"]) ?></h5>
              <p class="muted small mb-2"><?= htmlspecialchars($row["address"]) ?></p>
              <p class="muted small mb-3"><?= htmlspecialchars(mb_strimwidth($row["description"] ?? "", 0, 60, "...")) ?></p>
              <a href="customer/salon.php?id=<?= $sid ?>" class="btn-view w-100 text-center">View Salon</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php endif; ?>
  
</main>

<?php include "includes/footer.php"; ?>

<!-- FIXED Google Maps Script -->
<script>
let map;
let markers = [];
let userLocation = { lat: <?= $userLat ?>, lng: <?= $userLng ?> };

function initMap() {
    console.log('✅ Map initializing...');
    
    const mapDiv = document.getElementById('map');
    if (!mapDiv) {
        console.error('Map div not found');
        return;
    }
    
    // Create map
    map = new google.maps.Map(mapDiv, {
        center: userLocation,
        zoom: 14,
        gestureHandling: 'greedy',
        zoomControl: true,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true
    });
    
    console.log('✅ Map created');
    
    // Add user marker
    new google.maps.Marker({
        position: userLocation,
        map: map,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: "#4285F4",
            fillOpacity: 1,
            strokeColor: "#ffffff",
            strokeWeight: 2,
        },
        title: "Your Location"
    });
    
    // Add database salons
    addDatabaseSalons();
}

function findRealSalons() {
    const radius = parseInt(document.getElementById('radiusSelect').value);
    const resultCountDiv = document.getElementById('resultCount');
    
    resultCountDiv.innerHTML = '🔍 Searching for salons...';
    resultCountDiv.style.color = '#c8a14a';
    
    // Clear old markers
    if (window.realSalonMarkers) {
        window.realSalonMarkers.forEach(m => m.setMap(null));
    }
    window.realSalonMarkers = [];
    
    // Create PlacesService
    const service = new google.maps.places.PlacesService(map);
    
    // Search for salons
    const request = {
        location: userLocation,
        radius: radius,
        types: ['beauty_salon', 'hair_care', 'spa'],
        keyword: 'salon'
    };
    
    service.nearbySearch(request, (results, status) => {
        console.log('Search status:', status);
        
        if (status === 'OK' && results.length > 0) {
            resultCountDiv.innerHTML = `✅ Found ${results.length} real salons!`;
            resultCountDiv.style.color = '#28a745';
            
            results.forEach((place) => {
                addRealSalonMarker(place);
            });
            
        } else if (status === 'ZERO_RESULTS') {
            resultCountDiv.innerHTML = '⚠️ No salons found. Try larger radius.';
            resultCountDiv.style.color = '#ffc107';
        } else {
            resultCountDiv.innerHTML = '❌ Search failed. Try again.';
            resultCountDiv.style.color = '#dc3545';
        }
    });
}

function addRealSalonMarker(place) {
    const marker = new google.maps.Marker({
        map: map,
        position: place.geometry.location,
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
        },
        title: place.name
    });
    
    window.realSalonMarkers = window.realSalonMarkers || [];
    window.realSalonMarkers.push(marker);
    
    const infowindow = new google.maps.InfoWindow({
        content: `
            <div style="color: #000; padding: 12px; max-width: 200px;">
                <h4 style="margin: 0 0 5px 0; color: #7b2cbf;">${place.name}</h4>
                <p style="margin: 0 0 5px 0;">${place.vicinity || ''}</p>
                ${place.rating ? `<p>⭐ ${place.rating} (${place.user_ratings_total || 0} reviews)</p>` : ''}
            </div>
        `
    });
    
    marker.addListener('click', () => {
        infowindow.open(map, marker);
    });
}

function addDatabaseSalons() {
    <?php 
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()): 
    ?>
    (function() {
        const marker = new google.maps.Marker({
            position: { lat: <?= $row['lat'] ?>, lng: <?= $row['lng'] ?> },
            map: map,
            icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/purple-dot.png",
            },
            title: "<?= addslashes($row['name']) ?>"
        });
        
        const infowindow = new google.maps.InfoWindow({
            content: `
                <div style="color: #000; padding: 12px;">
                    <h4 style="margin: 0 0 5px 0; color: #7b2cbf;"><?= addslashes($row['name']) ?></h4>
                    <p><?= addslashes($row['address']) ?></p>
                    <a href="customer/salon.php?id=<?= $row['id'] ?>" style="color: #c8a14a;">View Salon →</a>
                </div>
            `
        });
        
        marker.addListener('click', () => {
            infowindow.open(map, marker);
        });
    })();
    <?php endwhile; ?>
}
</script>

<!-- Load Google Maps with API key -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC7tqW3rNNJ-VLdLsKlKM8Wh4qDAlyqhSA&libraries=places&callback=initMap" async defer></script>

</body>
</html>