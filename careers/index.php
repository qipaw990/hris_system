<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get company name from settings
$companyName = getSetting('company_name', 'Perusahaan Kami');

// Get all open job postings
try {
    $jobsStmt = $pdo->query("SELECT jp.*, d.department_name, p.position_name,
        (SELECT COUNT(*) FROM job_applications WHERE job_id = jp.id) as application_count
        FROM job_postings jp
        LEFT JOIN departments d ON jp.department_id = d.id
        LEFT JOIN positions p ON jp.position_id = p.id
        WHERE jp.status = 'Open' AND jp.closing_date >= CURDATE()
        ORDER BY jp.posted_date DESC");
    $jobs = $jobsStmt->fetchAll();
    
    // Get stats
    $statsStmt = $pdo->query("SELECT 
        COUNT(*) as total_jobs,
        SUM(vacancies) as total_vacancies,
        COUNT(DISTINCT department_id) as departments_hiring
        FROM job_postings 
        WHERE status = 'Open'");
    $stats = $statsStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Error fetching jobs: " . $e->getMessage());
    $jobs = [];
    $stats = ['total_jobs' => 0, 'total_vacancies' => 0, 'departments_hiring' => 0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karir - Bergabung Bersama <?php echo htmlspecialchars($companyName ?? 'Kami'); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%),
                        url('/hrm/assets/images/career_hero.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 120px 0 80px;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.2);
            z-index: 1;
        }
        
        .hero-section .container {
            position: relative;
            z-index: 2;
        }
        
        .hero-section h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            animation: fadeInDown 1s ease;
        }
        
        .hero-section p {
            font-size: 1.4rem;
            opacity: 0.95;
            max-width: 700px;
            margin: 0 auto 40px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            animation: fadeInUp 1s ease 0.2s both;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .jobs-container {
            background: white;
            border-radius: 30px 30px 0 0;
            padding: 60px 0;
            margin-top: -30px;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.1);
        }
        
        .job-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            border: 2px solid #f0f0f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .job-card:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
            transform: translateY(-5px);
        }
        
        .job-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        .job-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 15px 0;
        }
        
        .job-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #718096;
            font-size: 0.95rem;
        }
        
        .job-meta-item i {
            color: #667eea;
        }
        
        .badge-custom {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .badge-open {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-apply:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .stats-section {
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.1) 100%);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin: 40px 0;
            color: white;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
        }
        
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .search-box {
            background: white;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 30px auto;
            max-width: 600px;
        }
        
        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            padding: 10px;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .benefits-section {
            background: white;
            padding: 80px 0;
        }
        
        .benefit-card {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        
        .benefit-card:hover {
            transform: translateY(-10px);
        }
        
        .benefit-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        .culture-section {
            background: #f8f9fa;
            padding: 80px 0;
        }
        
        .culture-image {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .culture-image:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        
        .culture-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1><i class="fas fa-briefcase me-3"></i>Bergabung Bersama <?php echo htmlspecialchars($companyName); ?></h1>
            <p>Temukan peluang karir yang menarik dan jadilah bagian dari tim kami yang terus berkembang</p>
            
            <!-- Stats -->
            <div class="stats-section">
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $stats['total_jobs']; ?></span>
                            <span class="stat-label">Posisi Terbuka</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $stats['total_vacancies']; ?></span>
                            <span class="stat-label">Total Lowongan</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $stats['departments_hiring']; ?></span>
                            <span class="stat-label">Departemen Merekrut</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Search Box -->
            <div class="search-box">
                <div class="d-flex align-items-center">
                    <i class="fas fa-search text-muted me-3"></i>
                    <input type="text" id="searchInput" placeholder="Cari lowongan berdasarkan judul, departemen, atau lokasi..." class="flex-grow-1">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Why Join Us Section -->
    <div class="benefits-section">
        <div class="container">
            <h2 class="text-center mb-5">
                <span style="color: #667eea;">Mengapa</span> Bergabung dengan Kami?
            </h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h5>Pengembangan Karir</h5>
                        <p class="text-muted">Kesempatan belajar dan berkembang secara berkelanjutan</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5>Tim yang Solid</h5>
                        <p class="text-muted">Bekerja dengan profesional berbakat dan bersemangat</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5>Keseimbangan Hidup</h5>
                        <p class="text-muted">Jam kerja fleksibel dan opsi kerja remote</p>
                    </div>
                </div>
                <div class="col-md-3 mb-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-gift"></i>
                        </div>
                        <h5>Benefit Menarik</h5>
                        <p class="text-muted">Gaji kompetitif dan tunjangan lengkap</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Company Culture Section -->
    <div class="culture-section">
        <div class="container">
            <h2 class="text-center mb-5">
                <span style="color: #667eea;">Budaya</span> Perusahaan Kami
            </h2>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="culture-image">
                        <img src="/hrm/assets/images/team_collaboration.png" alt="Team Collaboration">
                    </div>
                    <h5 class="mt-3">Lingkungan Kolaboratif</h5>
                    <p class="text-muted">Kami percaya pada kerja sama tim dan komunikasi terbuka</p>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="culture-image">
                        <img src="/hrm/assets/images/office_culture.png" alt="Office Culture">
                    </div>
                    <h5 class="mt-3">Ruang Kerja Modern</h5>
                    <p class="text-muted">Lingkungan kerja yang nyaman dan menginspirasi</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Jobs Container -->
    <div class="jobs-container">
        <div class="container">
            <h2 class="text-center mb-5">
                Posisi yang <span style="color: #667eea;">Tersedia</span>
            </h2>
            
            <?php if (empty($jobs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Belum ada posisi terbuka saat ini</h4>
                    <p class="text-muted">Silakan cek kembali nanti untuk peluang baru</p>
                </div>
            <?php else: ?>
                <div id="jobsList">
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card" data-job-id="<?php echo $job['id']; ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h3 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h3>
                                    
                                    <div class="job-meta">
                                        <div class="job-meta-item">
                                            <i class="fas fa-building"></i>
                                            <span><?php echo htmlspecialchars($job['department_name'] ?? 'N/A'); ?></span>
                                        </div>
                                        <div class="job-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($job['location']); ?></span>
                                        </div>
                                        <div class="job-meta-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $job['employment_type']; ?></span>
                                        </div>
                                        <div class="job-meta-item">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $job['vacancies']; ?> position(s)</span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($job['job_description'])): ?>
                                        <p class="text-muted mb-3">
                                            <?php echo substr(htmlspecialchars($job['job_description']), 0, 150); ?>...
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge-custom badge-open">
                                            <i class="fas fa-circle-check me-1"></i> Open
                                        </span>
                                        <?php if (!empty($job['salary_range'])): ?>
                                            <span class="text-muted">
                                                <i class="fas fa-money-bill-wave me-1"></i>
                                                <?php echo htmlspecialchars($job['salary_range']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="ms-3">
                                    <button class="btn btn-apply" onclick="viewJobDetails(<?php echo $job['id']; ?>)">
                                        <i class="fas fa-arrow-right me-2"></i> Lihat & Lamar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Job Details Modal -->
    <div class="modal fade" id="jobDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-briefcase me-2"></i> Detail Lowongan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="jobDetailsContent">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Application Modal -->
    <div class="modal fade" id="applicationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-paper-plane me-2"></i> Kirim Lamaran</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="/hrm/careers/submit_application.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" id="apply_job_id">
                    <div class="modal-body">
                        <h6 class="mb-3">Informasi Pribadi</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Depan <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Belakang <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telepon <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <hr>
                        <h6 class="mb-3">Informasi Profesional</h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tingkat Pendidikan</label>
                                <select name="education_level" class="form-select">
                                    <option value="">Pilih...</option>
                                    <option value="High School">SMA/SMK</option>
                                    <option value="Diploma">Diploma (D3/D4)</option>
                                    <option value="Bachelor">Sarjana (S1)</option>
                                    <option value="Master">Magister (S2)</option>
                                    <option value="Doctorate">Doktor (S3)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pengalaman (Tahun)</label>
                                <input type="number" name="years_of_experience" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Perusahaan Saat Ini</label>
                                <input type="text" name="current_company" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Posisi Saat Ini</label>
                                <input type="text" name="current_position" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gaji yang Diharapkan (Rp)</label>
                            <input type="number" name="expected_salary" class="form-control" placeholder="e.g. 10000000">
                        </div>
                        
                        <hr>
                        <h6 class="mb-3">Dokumen <span class="text-danger">*</span></h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Resume/CV <span class="text-danger">*</span></label>
                            <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                            <small class="text-muted">PDF, DOC, or DOCX. Max 5MB</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Surat Lamaran (Opsional)</label>
                            <textarea name="cover_letter" class="form-control" rows="4" placeholder="Ceritakan mengapa Anda cocok untuk posisi ini..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profil LinkedIn</label>
                                <input type="url" name="linkedin_url" class="form-control" placeholder="https://linkedin.com/in/yourprofile">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">URL Portfolio</label>
                                <input type="url" name="portfolio_url" class="form-control" placeholder="https://yourportfolio.com">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-apply">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Lamaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const jobCards = document.querySelectorAll('.job-card');
            
            jobCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? 'block' : 'none';
            });
        });
        
        function viewJobDetails(jobId) {
            $.get('/hrm/careers/get_job_details.php?id=' + jobId, function(response) {
                $('#jobDetailsContent').html(response);
                $('#jobDetailsModal').modal('show');
            });
        }
        
        function applyNow(jobId) {
            $('#apply_job_id').val(jobId);
            $('#jobDetailsModal').modal('hide');
            $('#applicationModal').modal('show');
        }
    </script>
</body>
</html>
