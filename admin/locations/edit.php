<?php
$page_title = 'Edit Lokasi Kantor';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get location ID
$locationId = $_GET['id'] ?? null;

if (!$locationId) {
    header('Location: /hrm/admin/locations/index.php');
    exit();
}

// Get location data
try {
    $stmt = $pdo->prepare("SELECT * FROM office_locations WHERE id = ?");
    $stmt->execute([$locationId]);
    $location = $stmt->fetch();
    
    if (!$location) {
        $_SESSION['error'] = 'Lokasi tidak ditemukan';
        header('Location: /hrm/admin/locations/index.php');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching location: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal mengambil data lokasi';
    header('Location: /hrm/admin/locations/index.php');
    exit();
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-edit me-2"></i> Edit Lokasi Kantor</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/hrm/admin/locations/index.php">Lokasi Kantor</a></li>
                    <li class="breadcrumb-item active">Edit Lokasi</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/locations/index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</div>

<!-- Edit Location Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Form Edit Lokasi</h5>
            </div>
            <div class="card-body">
                <form action="/hrm/admin/locations/process_edit.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="id" value="<?php echo $location['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="location_name" class="form-control" required 
                               value="<?php echo htmlspecialchars($location['location_name']); ?>"
                               placeholder="Contoh: Kantor Pusat Jakarta">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3" 
                                  placeholder="Alamat lengkap lokasi kantor"><?php echo htmlspecialchars($location['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih Lokasi di Peta <span class="text-danger">*</span></label>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-info" id="btnCurrentLocation">
                                <i class="fas fa-crosshairs me-2"></i> Gunakan Lokasi Saat Ini
                            </button>
                            <span id="locationStatus" class="ms-2 text-muted"></span>
                        </div>
                        <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                        <small class="text-muted">Klik pada peta untuk memilih lokasi kantor atau seret marker</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="0.00000001" name="latitude" id="latitude" 
                                   class="form-control" required readonly 
                                   value="<?php echo $location['latitude']; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="0.00000001" name="longitude" id="longitude" 
                                   class="form-control" required readonly 
                                   value="<?php echo $location['longitude']; ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Radius (meter) <span class="text-danger">*</span></label>
                            <input type="number" name="radius_meters" id="radius_meters" 
                                   class="form-control" value="<?php echo $location['radius_meters']; ?>" 
                                   min="10" max="1000" required>
                            <small class="text-muted">Jarak maksimal untuk check-in</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?php echo $location['is_active'] ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo !$location['is_active'] ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/hrm/admin/locations/index.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card fade-in">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informasi</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Dibuat:</dt>
                    <dd class="col-sm-7"><?php echo formatDate($location['created_at'], 'd M Y H:i'); ?></dd>
                    
                    <dt class="col-sm-5">Diupdate:</dt>
                    <dd class="col-sm-7"><?php echo formatDate($location['updated_at'], 'd M Y H:i'); ?></dd>
                </dl>
                <hr>
                <p class="small text-muted mb-0">
                    <i class="fas fa-lightbulb me-1"></i>
                    Karyawan hanya bisa check-in jika berada dalam radius yang ditentukan dari lokasi ini.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let marker;
let circle;

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Use existing location coordinates
    const existingPosition = [<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>];
    
    map = L.map('map').setView(existingPosition, 17);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Place initial marker
    placeMarker(L.latLng(existingPosition[0], existingPosition[1]));
    
    // Add click listener to map
    map.on('click', function(e) {
        placeMarker(e.latlng);
    });
    
    // Current location button handler
    document.getElementById('btnCurrentLocation').addEventListener('click', function() {
        getCurrentLocation();
    });
});

function getCurrentLocation() {
    const statusEl = document.getElementById('locationStatus');
    const btnEl = document.getElementById('btnCurrentLocation');
    
    if (!navigator.geolocation) {
        statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Browser tidak support GPS</span>';
        return;
    }
    
    // Show loading
    btnEl.disabled = true;
    statusEl.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin"></i> Mengambil lokasi...</span>';
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const userLocation = [position.coords.latitude, position.coords.longitude];
            map.setView(userLocation, 17);
            placeMarker(L.latLng(userLocation[0], userLocation[1]));
            
            statusEl.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Lokasi berhasil diambil</span>';
            btnEl.disabled = false;
            
            // Clear status after 3 seconds
            setTimeout(function() {
                statusEl.innerHTML = '';
            }, 3000);
        },
        function(error) {
            let errorMsg = 'Gagal mengambil lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMsg = 'Izin lokasi ditolak. Aktifkan GPS di browser Anda.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMsg = 'Informasi lokasi tidak tersedia';
                    break;
                case error.TIMEOUT:
                    errorMsg = 'Timeout saat mengambil lokasi';
                    break;
            }
            statusEl.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> ' + errorMsg + '</span>';
            btnEl.disabled = false;
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

function placeMarker(latlng) {
    // Remove existing marker and circle
    if (marker) {
        map.removeLayer(marker);
    }
    if (circle) {
        map.removeLayer(circle);
    }
    
    // Place new marker
    marker = L.marker(latlng, {
        draggable: true
    }).addTo(map);
    
    // Add drag listener
    marker.on('dragend', function(event) {
        const position = event.target.getLatLng();
        updateCoordinates(position);
        updateCircle(position);
    });
    
    // Update coordinates
    updateCoordinates(latlng);
    updateCircle(latlng);
}

function updateCoordinates(latlng) {
    document.getElementById('latitude').value = latlng.lat.toFixed(8);
    document.getElementById('longitude').value = latlng.lng.toFixed(8);
}

function updateCircle(latlng) {
    if (circle) {
        map.removeLayer(circle);
    }
    
    const radius = parseInt(document.getElementById('radius_meters').value) || 100;
    
    circle = L.circle(latlng, {
        color: '#667eea',
        fillColor: '#667eea',
        fillOpacity: 0.2,
        radius: radius
    }).addTo(map);
    
    // Fit map to show circle
    map.fitBounds(circle.getBounds());
}

// Update circle when radius changes
document.getElementById('radius_meters').addEventListener('change', function() {
    if (marker) {
        updateCircle(marker.getLatLng());
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
