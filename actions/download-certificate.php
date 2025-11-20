<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';
require_once '../vendor/autoload.php';

require_login();

$user_id = get_user_id();
$certificate_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($certificate_id == 0) {
    set_error('Certificate not found');
    redirect('/dashboard/certificates.php');
}

// Get certificate details
// Allow admin to download any certificate, regular users can only download their own
if (is_admin()) {
    $stmt = $conn->prepare("
        SELECT c.*, co.course_title, s.subject_name, u.full_name
        FROM certificates c
        JOIN courses co ON c.course_id = co.course_id
        JOIN subjects s ON co.subject_id = s.subject_id
        JOIN users u ON c.user_id = u.user_id
        WHERE c.certificate_id = ?
    ");
    $stmt->execute([$certificate_id]);
} else {
    $stmt = $conn->prepare("
        SELECT c.*, co.course_title, s.subject_name, u.full_name
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

// Create new PDF document
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(SITE_NAME);
$pdf->SetAuthor(SITE_NAME);
$pdf->SetTitle('Certificate - ' . $certificate['course_title']);
$pdf->SetSubject('Certificate of Completion');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(false, 20);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Draw decorative border
$pdf->SetLineStyle(array('width' => 3, 'color' => array(139, 92, 246)));
$pdf->Rect(10, 10, 277, 190, 'D');
$pdf->SetLineStyle(array('width' => 1, 'color' => array(139, 92, 246)));
$pdf->Rect(15, 15, 267, 180, 'D');

// Logo/Icon (using text as icon)
$pdf->SetFont('helvetica', 'B', 40);
$pdf->SetTextColor(139, 92, 246);
$pdf->SetXY(20, 30);
$pdf->Cell(257, 20, '🎓', 0, 1, 'C');

// Certificate Title
$pdf->SetFont('helvetica', 'B', 36);
$pdf->SetTextColor(139, 92, 246);
$pdf->SetXY(20, 55);
$pdf->Cell(257, 15, 'CERTIFICATE', 0, 1, 'C');

// Subtitle
$pdf->SetFont('helvetica', 'I', 18);
$pdf->SetTextColor(102, 102, 102);
$pdf->SetXY(20, 72);
$pdf->Cell(257, 10, 'of Completion', 0, 1, 'C');

// Body text
$pdf->SetFont('helvetica', '', 14);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(20, 90);
$pdf->Cell(257, 8, 'This is to certify that', 0, 1, 'C');

// Recipient name
$pdf->SetFont('helvetica', 'B', 28);
$pdf->SetTextColor(139, 92, 246);
$pdf->SetXY(20, 100);
$pdf->Cell(257, 12, strtoupper($certificate['full_name']), 0, 1, 'C');

// Draw line under name
$pdf->SetLineStyle(array('width' => 0.5, 'color' => array(139, 92, 246)));
$nameWidth = $pdf->GetStringWidth(strtoupper($certificate['full_name'])) + 20;
$nameX = (297 - $nameWidth) / 2;
$pdf->Line($nameX, 114, $nameX + $nameWidth, 114);

// Body text continued
$pdf->SetFont('helvetica', '', 14);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(20, 120);
$pdf->Cell(257, 8, 'has successfully completed the course', 0, 1, 'C');

// Course name
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(20, 130);
$pdf->MultiCell(257, 10, $certificate['course_title'], 0, 'C', 0, 1);

// Subject and date
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(20, 150);
$issued_date = date('F d, Y', strtotime($certificate['issued_date']));
$pdf->Cell(257, 6, 'in the field of ' . $certificate['subject_name'], 0, 1, 'C');
$pdf->SetXY(20, 157);
$pdf->Cell(257, 6, 'on ' . $issued_date, 0, 1, 'C');

// Footer with signatures
$pdf->SetLineStyle(array('width' => 0.3, 'color' => array(139, 92, 246)));
$pdf->Line(20, 172, 277, 172);

// Left signature block
$pdf->SetFont('helvetica', 'I', 16);
$pdf->SetTextColor(139, 92, 246);
$pdf->SetXY(40, 176);
$pdf->Cell(80, 6, SITE_NAME, 0, 0, 'C');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(102, 102, 102);
$pdf->SetXY(40, 182);
$pdf->Cell(80, 4, 'Authorized Signature', 0, 0, 'C');
$pdf->SetXY(40, 186);
$pdf->Cell(80, 4, SITE_NAME, 0, 0, 'C');

// Right signature block (Date)
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(177, 176);
$pdf->Cell(80, 6, date('M d, Y', strtotime($certificate['issued_date'])), 0, 0, 'C');

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(102, 102, 102);
$pdf->SetXY(177, 182);
$pdf->Cell(80, 4, 'Date of Completion', 0, 0, 'C');

// Certificate code at bottom
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(153, 153, 153);
$pdf->SetXY(20, 193);
$pdf->Cell(257, 4, 'Verification Code: ' . $certificate['certificate_code'], 0, 1, 'C');
$pdf->SetXY(20, 197);
$pdf->Cell(257, 4, 'Verify at: ' . SITE_URL . 'verify/' . $certificate['certificate_code'], 0, 1, 'C');

// Decorative seal (circle)
$pdf->SetFillColor(139, 92, 246);
$pdf->SetTextColor(255, 255, 255);
$pdf->Circle(250, 165, 15, 0, 360, 'F');
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetXY(235, 158);
$pdf->Cell(30, 15, '★', 0, 0, 'C');

// Output PDF
$filename = 'Certificate_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $certificate['full_name']) . '_' .
            preg_replace('/[^A-Za-z0-9_\-]/', '_', $certificate['course_title']) . '.pdf';
$pdf->Output($filename, 'D');
exit();
?>
