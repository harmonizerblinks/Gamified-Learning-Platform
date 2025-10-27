<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';

require_login();

$user_id = get_user_id();
$certificate_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($certificate_id == 0) {
    set_error('Certificate not found');
    redirect('/dashboard/certificates.php');
}

// Get certificate details
$stmt = $conn->prepare("
    SELECT c.*, co.course_title, s.subject_name, u.full_name
    FROM certificates c
    JOIN courses co ON c.course_id = co.course_id
    JOIN subjects s ON co.subject_id = s.subject_id
    JOIN users u ON c.user_id = u.user_id
    WHERE c.certificate_id = ? AND c.user_id = ?
");
$stmt->execute([$certificate_id, $user_id]);
$certificate = $stmt->fetch();

if (!$certificate) {
    set_error('Certificate not found');
    redirect('/dashboard/certificates.php');
}

// For now, redirect to view page
// In production, you would use a library like TCPDF or mPDF to generate PDF
redirect('/dashboard/certificate-view.php?id=' . $certificate_id);

/* 
 * TO IMPLEMENT PDF GENERATION:
 * 
 * 1. Install TCPDF or mPDF via Composer:
 *    composer require tecnickcom/tcpdf
 * 
 * 2. Generate PDF:
 * 
 * require_once 'vendor/autoload.php';
 * 
 * $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
 * $pdf->SetCreator(SITE_NAME);
 * $pdf->SetTitle('Certificate - ' . $certificate['course_title']);
 * $pdf->setPrintHeader(false);
 * $pdf->setPrintFooter(false);
 * $pdf->AddPage();
 * 
 * // Add certificate content
 * $html = '<div style="text-align: center;">...certificate HTML...</div>';
 * $pdf->writeHTML($html, true, false, true, false, '');
 * 
 * // Output PDF
 * $filename = 'Certificate_' . $certificate['certificate_code'] . '.pdf';
 * $pdf->Output($filename, 'D'); // D = download
 */
?>