<?php
require_once '../config/settings.php';
require_once CONFIG_PATH . 'db.php';
require_once INCLUDES_PATH . 'session.php';
require_once INCLUDES_PATH . 'functions.php';
require_once LIB_PATH .'../lib/fpdf.php';

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

// Create PDF using FPDF
class PDF extends FPDF {
    function DrawBorder() {
        // Outer border
        $this->SetLineWidth(1);
        $this->SetDrawColor(139, 92, 246);
        $this->Rect(5, 5, 287, 200, 'D');

        // Inner border
        $this->SetLineWidth(0.5);
        $this->Rect(10, 10, 277, 190, 'D');
    }

    function DrawSeal($x, $y) {
        // Draw circular seal
        $this->SetFillColor(139, 92, 246);
        $this->SetDrawColor(139, 92, 246);
        $this->SetLineWidth(0.5);
        $this->Circle($x, $y, 15, 'FD');

        // Draw star in seal
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 24);
        $this->SetXY($x - 10, $y - 8);
        $this->Cell(20, 15, '*', 0, 0, 'C');
    }

    function Circle($x, $y, $r, $style='D') {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    function Ellipse($x, $y, $rx, $ry, $style='D') {
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->k;
        $h=$this->h;
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }
}

// Create new PDF document in landscape
$pdf = new PDF('L', 'mm', 'A4');
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Draw borders
$pdf->DrawBorder();

// Logo/Icon area
$pdf->SetFont('Arial', 'B', 48);
$pdf->SetTextColor(139, 92, 246);
$pdf->SetXY(10, 25);
$pdf->Cell(277, 20, 'CERTIFICATE', 0, 1, 'C');

// Subtitle
$pdf->SetFont('Arial', 'I', 20);
$pdf->SetTextColor(102, 102, 102);
$pdf->SetXY(10, 48);
$pdf->Cell(277, 10, 'of Completion', 0, 1, 'C');

// Body text
$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(10, 70);
$pdf->Cell(277, 8, 'This is to certify that', 0, 1, 'C');

// Recipient name with underline
$pdf->SetFont('Arial', 'B', 28);
$pdf->SetTextColor(139, 92, 246);
$fullName = strtoupper($certificate['full_name']);
$pdf->SetXY(10, 80);
$pdf->Cell(277, 15, $fullName, 0, 1, 'C');

// Draw line under name
$pdf->SetDrawColor(139, 92, 246);
$pdf->SetLineWidth(0.5);
$nameWidth = $pdf->GetStringWidth($fullName);
$nameX = (297 - $nameWidth) / 2;
$pdf->Line($nameX, 96, $nameX + $nameWidth, 96);

// Continue body text
$pdf->SetFont('Arial', '', 14);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(10, 105);
$pdf->Cell(277, 8, 'has successfully completed the course', 0, 1, 'C');

// Course name
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(10, 118);
$pdf->MultiCell(277, 10, $certificate['course_title'], 0, 'C');

// Subject and date
$pdf->SetFont('Arial', '', 12);
$pdf->SetXY(10, 145);
$issued_date = date('F d, Y', strtotime($certificate['issued_date']));
$pdf->Cell(277, 6, 'in the field of ' . $certificate['subject_name'], 0, 1, 'C');
$pdf->SetXY(10, 152);
$pdf->Cell(277, 6, 'on ' . $issued_date, 0, 1, 'C');

// Footer line
$pdf->SetDrawColor(139, 92, 246);
$pdf->SetLineWidth(0.3);
$pdf->Line(20, 168, 277, 168);

// Signature blocks
// Left signature
$pdf->SetFont('Arial', 'I', 16);
$pdf->SetTextColor(139, 92, 246);
$pdf->SetXY(40, 172);
$pdf->Cell(80, 8, SITE_NAME, 0, 0, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(102, 102, 102);
$pdf->SetXY(40, 180);
$pdf->Cell(80, 4, 'Authorized Signature', 0, 0, 'C');
$pdf->SetXY(40, 184);
$pdf->Cell(80, 4, SITE_NAME, 0, 0, 'C');

// Right signature (Date)
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(51, 51, 51);
$pdf->SetXY(177, 172);
$pdf->Cell(80, 8, date('M d, Y', strtotime($certificate['issued_date'])), 0, 0, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(102, 102, 102);
$pdf->SetXY(177, 180);
$pdf->Cell(80, 4, 'Date of Completion', 0, 0, 'C');

// Certificate code at bottom
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(153, 153, 153);
$pdf->SetXY(10, 193);
$pdf->Cell(277, 4, 'Verification Code: ' . $certificate['certificate_code'], 0, 1, 'C');
$pdf->SetXY(10, 197);
$pdf->Cell(277, 4, 'Verify at: ' . SITE_URL . 'verify/' . $certificate['certificate_code'], 0, 1, 'C');

// Draw seal
$pdf->DrawSeal(250, 165);

// Output PDF
$filename = 'Certificate_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $certificate['full_name']) . '_' .
            preg_replace('/[^A-Za-z0-9_\-]/', '_', $certificate['course_title']) . '.pdf';
$pdf->Output('D', $filename);
exit();
?>
