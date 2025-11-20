<?php 
require_once '../includes/header.php';
require_login();

$user_id = get_user_id();
$certificate_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Support both certificate_id and course_id
if ($certificate_id == 0 && isset($_GET['course_id'])) {
    $course_id = (int)$_GET['course_id'];
    $stmt = $conn->prepare("SELECT certificate_id FROM certificates WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    $result = $stmt->fetch();
    if ($result) {
        $certificate_id = $result['certificate_id'];
    }
}

if ($certificate_id == 0) {
    set_error('Certificate not found');
    redirect('/dashboard/certificates.php');
}

// Get certificate details
// Allow admin to view any certificate, regular users can only view their own
if (is_admin()) {
    $stmt = $conn->prepare("
        SELECT c.*, co.course_title, co.course_id, s.subject_name, u.full_name
        FROM certificates c
        JOIN courses co ON c.course_id = co.course_id
        JOIN subjects s ON co.subject_id = s.subject_id
        JOIN users u ON c.user_id = u.user_id
        WHERE c.certificate_id = ?
    ");
    $stmt->execute([$certificate_id]);
} else {
    $stmt = $conn->prepare("
        SELECT c.*, co.course_title, co.course_id, s.subject_name, u.full_name
        FROM certificates c
        JOIN courses co ON c.course_id = co.course_id
        JOIN subjects s ON co.subject_id = s.subject_id
        JOIN users u ON c.user_id = u.user_id
        WHERE c.certificate_id = ? AND c.user_id = ?
    ");
    $stmt->execute([$certificate_id, $user_id]);
}
$certificate = $stmt->fetch();

if (!$certificate) {
    set_error('Certificate not found');
    redirect(is_admin() ? '/admin/certificates/' : '/dashboard/certificates.php');
}

$page_title = "Certificate - " . $certificate['course_title'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .certificate-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .certificate {
            background: white;
            /* Landscape A4 ratio: 297mm x 210mm = 1.414:1 */
            width: 1100px;
            max-width: 95vw;
            aspect-ratio: 297 / 210;
            padding: 40px 60px;
            border: 15px solid #8B5CF6;
            border-radius: 10px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(139, 92, 246, 0.03) 10px,
                rgba(139, 92, 246, 0.03) 20px
            );
            pointer-events: none;
        }
        
        .certificate-header {
            text-align: center;
            margin-bottom: 20px;
            position: relative;
        }

        .certificate-logo {
            font-size: 3rem;
            color: #8B5CF6;
            margin-bottom: 10px;
        }

        .certificate-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #8B5CF6;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 5px;
        }

        .certificate-subtitle {
            font-size: 1.2rem;
            color: #666;
            font-style: italic;
        }
        
        .certificate-body {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .certificate-text {
            font-size: 1rem;
            color: #333;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .recipient-name {
            font-size: 2.2rem;
            font-weight: bold;
            color: #8B5CF6;
            margin: 15px 0;
            text-decoration: underline;
            text-decoration-color: #8B5CF6;
            text-decoration-thickness: 3px;
            text-underline-offset: 8px;
        }

        .course-name {
            font-size: 1.6rem;
            font-weight: bold;
            color: #333;
            margin: 15px 0;
        }
        
        .certificate-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #8B5CF6;
            position: relative;
        }
        
        .signature-block {
            text-align: center;
            flex: 1;
        }
        
        .signature-line {
            width: 180px;
            height: 50px;
            border-bottom: 2px solid #333;
            margin: 0 auto 8px;
            font-family: 'Brush Script MT', cursive;
            font-size: 1.6rem;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 8px;
        }

        .signature-label {
            font-size: 0.8rem;
            color: #666;
            font-weight: bold;
        }

        .certificate-seal {
            position: absolute;
            bottom: 20px;
            right: 40px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8B5CF6, #6366F1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 5px 20px rgba(139, 92, 246, 0.4);
            border: 4px solid white;
        }

        .certificate-code {
            text-align: center;
            margin-top: 15px;
            font-size: 0.75rem;
            color: #999;
        }
        
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .certificate {
                width: 900px;
            }
            .certificate-title {
                font-size: 2rem;
            }
            .recipient-name {
                font-size: 1.8rem;
            }
            .course-name {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 768px) {
            .certificate {
                width: 100%;
                padding: 20px 30px;
                border-width: 10px;
            }
            .certificate-title {
                font-size: 1.5rem;
            }
            .recipient-name {
                font-size: 1.3rem;
            }
            .course-name {
                font-size: 1rem;
            }
            .certificate-seal {
                width: 60px;
                height: 60px;
                font-size: 1rem;
            }
        }

        @media print {
            body {
                background: white;
            }
            .certificate-container {
                margin: 0;
                padding: 0;
            }
            .certificate {
                width: 297mm;
                height: 210mm;
                max-width: none;
            }
            .action-buttons, .back-button {
                display: none !important;
            }
        }

        @page {
            size: landscape;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="<?php echo is_admin() ? '/admin/certificates/' : '/dashboard/certificates.php'; ?>" class="btn btn-secondary back-button">
                <i class="fas fa-arrow-left me-2"></i>Back to Certificates
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print me-2"></i>Print Certificate
            </button>
            <a href="/actions/download-certificate.php?id=<?php echo $certificate_id; ?>" 
               class="btn btn-success" 
               target="_blank">
                <i class="fas fa-download me-2"></i>Download PDF
            </a>
            <button onclick="shareCertificate()" class="btn btn-info">
                <i class="fas fa-share-alt me-2"></i>Share
            </button>
        </div>
        
        <!-- Certificate -->
        <div class="certificate">
            <div class="certificate-header">
                <div class="certificate-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="certificate-title">Certificate</div>
                <div class="certificate-subtitle">of Completion</div>
            </div>
            
            <div class="certificate-body">
                <p class="certificate-text">This is to certify that</p>
                
                <div class="recipient-name">
                    <?php echo htmlspecialchars($certificate['full_name']); ?>
                </div>
                
                <p class="certificate-text">has successfully completed the course</p>
                
                <div class="course-name">
                    <?php echo htmlspecialchars($certificate['course_title']); ?>
                </div>
                
                <p class="certificate-text">
                    in the field of <strong><?php echo htmlspecialchars($certificate['subject_name']); ?></strong>
                    <br>
                    on <strong><?php echo date('F d, Y', strtotime($certificate['issued_date'])); ?></strong>
                </p>
            </div>
            
            <div class="certificate-footer">
                <div class="signature-block">
                    <div class="signature-line"><?php echo SITE_NAME; ?></div>
                    <div class="signature-label">Authorized Signature</div>
                    <div class="signature-label"><?php echo SITE_NAME; ?></div>
                </div>
                
                <div class="signature-block">
                    <div class="signature-line"><?php echo date('M d, Y', strtotime($certificate['issued_date'])); ?></div>
                    <div class="signature-label">Date of Completion</div>
                </div>
            </div>
            
            <div class="certificate-seal">
                <i class="fas fa-award"></i>
            </div>
            
            <div class="certificate-code">
                Verification Code: <?php echo htmlspecialchars($certificate['certificate_code']); ?>
                <br>
                Verify at: <?php echo SITE_URL; ?>verify/<?php echo $certificate['certificate_code']; ?>
            </div>
        </div>
    </div>
    
    <script>
        function shareCertificate() {
            const url = window.location.href;
            const title = '<?php echo addslashes($certificate['course_title']); ?> Certificate';
            const text = 'I just earned a certificate for completing <?php echo addslashes($certificate['course_title']); ?>!';
            
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: text,
                    url: url
                }).catch(err => console.log('Error sharing:', err));
            } else {
                // Fallback to copying link
                navigator.clipboard.writeText(url).then(() => {
                    alert('Certificate link copied to clipboard!');
                });
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>