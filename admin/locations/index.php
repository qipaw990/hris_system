<?php
$page_title = 'Lokasi Kantor';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

// Get all office locations
try {
    $stmt = $pdo->query("SELECT * FROM office_locations ORDER BY is_active DESC, location_name ASC");
    $locations = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching locations: " . $e->getMessage());
    $locations = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1><i class="fas fa-map-marker-alt me-2"></i> Lokasi Kantor</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/hrm/admin/index.php">Beranda</a></li>
                    <li class="breadcrumb-item active">Lokasi Kantor</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="/hrm/admin/locations/add.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i> Tambah Lokasi
            </a>
        </div>
    </div>
</div>

<!-- Locations List -->
<div class="row">
    <div class="col-12">
        <div class="card fade-in">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i> Semua Lokasi Kantor
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($locations)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-map-marker-alt fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Belum ada lokasi kantor</h4>
                        <p class="text-muted">Tambahkan lokasi kantor pertama Anda</p>
                        <a href="/hrm/admin/locations/add.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Tambah Lokasi
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="locationsTable">
                            <thead>
                                <tr>
                                    <th>Nama Lokasi</th>
                                    <th>Alamat</th>
                                    <th>Koordinat</th>
                                    <th>Radius</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($locations as $location): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($location['location_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($location['address'] ?? '-'); ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="fas fa-map-pin me-1"></i>
                                                <?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $location['radius_meters']; ?> meter
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($location['is_active']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewOnMap(<?php echo $location['latitude']; ?>, <?php echo $location['longitude']; ?>, '<?php echo htmlspecialchars($location['location_name']); ?>', <?php echo $location['radius_meters']; ?>)" 
                                                        data-bs-toggle="tooltip" title="Lihat di Peta">
                                                    <i class="fas fa-map"></i>
                                                </button>
                                                <a href="/hrm/admin/locations/edit.php?id=<?php echo $location['id']; ?>" 
                                                   class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Ubah">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="confirmDelete('/hrm/admin/locations/delete.php?id=<?php echo $location['id']; ?>', 'Hapus Lokasi?', 'Ini akan menghapus permanen <?php echo htmlspecialchars($location['location_name']); ?>')" 
                                                        class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-map me-2"></i> <span id="modalLocationName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let marker;
let circle;

function viewOnMap(lat, lng, name, radius) {
    document.getElementById('modalLocationName').textContent = name;
    
    const mapModal = new bootstrap.Modal(document.getElementById('mapModal'));
    mapModal.show();
    
    // Wait for modal to be shown before initializing map
    document.getElementById('mapModal').addEventListener('shown.bs.modal', function () {
        const position = [parseFloat(lat), parseFloat(lng)];
        
        // Initialize map
        if (map) {
            map.remove(); // Remove previous map instance
        }
        
        map = L.map('map').setView(position, 16);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Add marker
        marker = L.marker(position).addTo(map)
            .bindPopup('<b>' + name + '</b>').openPopup();
        
        // Add circle for radius
        circle = L.circle(position, {
            color: '#667eea',
            fillColor: '#667eea',
            fillOpacity: 0.2,
            radius: parseInt(radius)
        }).addTo(map);
        
        // Fit bounds to show circle
        map.fitBounds(circle.getBounds());
    }, { once: true });
}

$(document).ready(function() {
    $('#locationsTable').DataTable({
        order: [[4, 'desc'], [0, 'asc']], // Sort by status then name
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
