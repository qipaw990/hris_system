<?php
require_once __DIR__ . '/../../../config/database.php';

$applicant_id = $_GET['applicant_id'] ?? 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM applicant_documents WHERE applicant_id = ? ORDER BY uploaded_at DESC");
    $stmt->execute([$applicant_id]);
    $documents = $stmt->fetchAll();
    
    if (empty($documents)) {
        echo '<p class="text-muted text-center py-3">No documents uploaded yet</p>';
    } else {
        echo '<div class="list-group">';
        foreach ($documents as $doc) {
            $icon = '';
            switch ($doc['document_type']) {
                case 'Resume': $icon = 'fa-file-alt'; break;
                case 'Cover Letter': $icon = 'fa-envelope'; break;
                case 'Certificate': $icon = 'fa-certificate'; break;
                case 'Portfolio': $icon = 'fa-briefcase'; break;
                case 'ID Card': $icon = 'fa-id-card'; break;
                default: $icon = 'fa-file'; break;
            }
            
            $fileSize = round($doc['file_size'] / 1024, 2); // Convert to KB
            
            echo '<div class="list-group-item">';
            echo '<div class="d-flex justify-content-between align-items-start">';
            echo '<div>';
            echo '<h6 class="mb-1"><i class="fas ' . $icon . ' me-2"></i>' . htmlspecialchars($doc['document_name']) . '</h6>';
            echo '<small class="text-muted">';
            echo '<span class="badge bg-secondary">' . $doc['document_type'] . '</span> ';
            echo '| ' . $fileSize . ' KB ';
            echo '| Uploaded: ' . date('d M Y', strtotime($doc['uploaded_at']));
            echo '</small>';
            echo '</div>';
            echo '<div>';
            echo '<a href="/hrm/' . htmlspecialchars($doc['file_path']) . '" target="_blank" class="btn btn-sm btn-primary me-1">';
            echo '<i class="fas fa-download"></i> Download';
            echo '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }
    
} catch (PDOException $e) {
    error_log("Error fetching documents: " . $e->getMessage());
    echo '<p class="text-danger">Error loading documents</p>';
}
