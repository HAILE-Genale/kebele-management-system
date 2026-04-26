<?php
// print_birth.php
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    die("Certificate ID not provided.");
}

$cert_id = $_GET['id'];

$db = new Database();
$conn = $db->getConnection();

// Fetch Birth Certificate joined with Person details
$query = "SELECT b.certificate_number, b.person_id, b.mother_name, b.mother_nationality, b.father_nationality, b.registrar_id, b.registered_date, b.created_at,
                 p.first_name, p.father_name, p.grandfather_name, p.sex, p.date_of_birth, p.place_of_birth, p.nationality
          FROM birth_certificates b 
          JOIN persons p ON b.person_id = p.id 
          WHERE b.id = ?";

$stmt = $conn->prepare($query);
$stmt->execute([$cert_id]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cert) {
    die("Certificate Data Not Found.");
}

// Fetch Registrar Name
$registrar_name = "N/A";
if ($cert['registrar_id']) {
    $r_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $r_stmt->execute([$cert['registrar_id']]);
    $registrar = $r_stmt->fetch(PDO::FETCH_ASSOC);
    if ($registrar) $registrar_name = $registrar['name'];
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Print Birth Certificate - <?php echo htmlspecialchars($cert['first_name']); ?></title>
    <link rel="stylesheet" href="assets/css/certificate.css">
</head>
<body class="print-mode border-birth">
    
    <div class="print-btn-container no-print">
        <button onclick="window.print()">🖨️ Print Certificate</button>
        <button onclick="window.location.href='births.php'" style="background: #666; margin-left:10px;">Back</button>
        <p style="margin-top:10px; color:#555;">Make sure to set Orientation to "Landscape" and enable "Background Graphics" in Print Settings.</p>
    </div>

    <!-- CERTIFICATE TEMPLATE START -->
    <div class="certificate-wrapper border-birth">
        <div class="cert-inner">
            
            <div class="cert-header">
                <div class="cert-header-spacer"></div>
                <div class="cert-header-center">
                    <img src="assets/images/ethiopia-flag.png" alt="Ethiopian Flag" style="width:90px; height:auto; margin:0 auto 10px; display:block; border-radius:3px;">
                    <div class="title-am">በኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ የወሳኝ ኩነት ምዝገባ</div>
                    <div class="title-en">Federal Democratic Republic of Ethiopia Vital Event Registration</div>
                    <div class="cert-title">የልደት ምስክር ወረቀት<br>Birth Certificate</div>
                </div>
                <div class="top-meta">
                    <div class="multi-field">
                        <span>የልደት ክብር መዝገብ ቅጽ ቁጥር / Birth Register Form Number:</span>
                        <strong><?php echo $cert['certificate_number']; ?></strong>
                    </div>
                    <div class="multi-field">
                        <span>የልደት ምዝገባ ልዩ መለያ ቁጥር / Birth Registration Unique ID:</span>
                        <strong><?php echo str_pad($cert['person_id'], 8, '0', STR_PAD_LEFT); ?></strong>
                    </div>
                </div>
            </div>

            <div class="form-row mt-4">
                <div class="field">
                    <div class="field-label"><span>ስም / Name</span></div>
                    <div class="field-value"><?php echo $cert['first_name']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የአባት ስም / Father's Name</span></div>
                    <div class="field-value"><?php echo $cert['father_name']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የአያት ስም / Grand Father's Name</span></div>
                    <div class="field-value"><?php echo $cert['grandfather_name']; ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>ፆታ / Sex</span></div>
                    <div class="field-value"><?php echo $cert['sex']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የተወለደበት ቀን / Date of Birth (YYYY-MM-DD)</span></div>
                    <div class="field-value"><?php echo $cert['date_of_birth']; ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>የትውልድ ቦታ/ሀገር / Place/Country of Birth</span></div>
                    <div class="field-value"><?php echo $cert['place_of_birth']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>ዜግነት / Nationality</span></div>
                    <div class="field-value"><?php echo $cert['nationality']; ?></div>
                </div>
            </div>

            <div class="form-row mt-4">
                <div class="field">
                    <div class="field-label"><span>የእናት ሙሉ ስም / Mother's Full Name</span></div>
                    <div class="field-value"><?php echo $cert['mother_name']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የእናት ዜግነት / Mother's Nationality</span></div>
                    <div class="field-value"><?php echo $cert['mother_nationality']; ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>የአባት ዜግነት / Father's Nationality</span></div>
                    <div class="field-value"><?php echo $cert['father_nationality']; ?></div>
                </div>
            </div>

            <div class="form-row mt-4">
                <div class="field">
                    <div class="field-label"><span>የምዝገባ ቀን / Date of Registration</span></div>
                    <div class="field-value"><?php echo $cert['registered_date']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>ውረቀት የተሰጠበት ቀን / Date Certificate Issued</span></div>
                    <div class="field-value"><?php echo date('Y-m-d'); ?></div>
                </div>
            </div>

            <div class="form-row mt-4">
                <div class="field">
                    <div class="field-label"><span>የክብር መዝጋቢ ስም / Name of Civil Registrar</span></div>
                    <div class="field-value"><?php echo $registrar_name; ?></div>
                </div>
            </div>

            <div class="signature-box mt-4">
                <div class="field-label"><span>ፊርማ / Signature</span></div>
                <div class="field-value" style="height: 40px;"></div>
            </div>

            <div class="seal-box mt-4">
                <div class="field-label"><span>ማኅተም / Seal</span></div>
                <div style="width: 80px; height: 80px; border: 2px dashed #999; border-radius: 50%; display:inline-block; margin-top:5px;"></div>
            </div>

        </div>
    </div>

</body>
</html>
