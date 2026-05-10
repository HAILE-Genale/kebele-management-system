<?php
// print_death.php - Death Certificate Print Template
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

if (!isset($_GET['id'])) die("Certificate ID not provided.");

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT d.*, p.first_name, p.father_name, p.grandfather_name, p.sex, p.date_of_birth, p.place_of_birth, p.nationality
    FROM death_certificates d JOIN persons p ON d.person_id = p.id WHERE d.id = ?");
$stmt->execute([$_GET['id']]);
$cert = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cert) die("Certificate not found.");

$registrar_name = "N/A";
if ($cert['registrar_id']) {
    $r = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $r->execute([$cert['registrar_id']]);
    $reg = $r->fetch(PDO::FETCH_ASSOC);
    if ($reg) $registrar_name = $reg['name'];
}
?>
<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <title>Print Death Certificate</title>
    <link rel="stylesheet" href="assets/css/certificate.css?v=2">
</head>
<body class="print-mode">

    <div class="print-btn-container no-print">
        <button onclick="window.print()">🖨️ Print Certificate</button>
        <button onclick="window.location.href='deaths.php'" style="background:#666; margin-left:10px;">Back</button>
        <p style="margin-top:10px; color:#555;">Set Orientation to <b>Landscape</b> and enable <b>Background Graphics</b>.</p>
    </div>

    <div class="certificate-wrapper border-death">
        <div class="cert-inner">

            <div class="cert-header">
                <div class="cert-header-spacer"></div>
                <div class="cert-header-center">
                    <img src="assets/images/ethiopia-flag.png" alt="Ethiopian Flag" style="width:90px; height:auto; margin:0 auto 10px; display:block; border-radius:3px;">
                    <div class="title-am">በኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ የወሳኝ ኩነት ምዝገባ</div>
                    <div class="title-en">Federal Democratic Republic of Ethiopia Vital Event Registration</div>
                    <div class="cert-title">የሞት ምስክር ወረቀት<br>Death Certificate</div>
                </div>
                <div class="top-meta">
                    <div class="multi-field">
                        <span>የሞት ክብር መዝገብ ቅጽ ቁጥር / Death Register Form Number:</span>
                        <strong><?php echo $cert['certificate_number']; ?></strong>
                    </div>
                    <div class="multi-field">
                        <span>የሞት ምዝገባ ልዩ መለያ ቁጥር / Death Registration Unique ID:</span>
                        <strong><?php echo str_pad($cert['person_id'], 8, '0', STR_PAD_LEFT); ?></strong>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>የሟች ስም / Deceased's Name</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['first_name']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የአባት ስም / Father's Name</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['father_name']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የአያት ስም / Grandfather's Name</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['grandfather_name']); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>ማዕረግ / Title</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['title']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>ፆታ / Sex</span></div>
                    <div class="field-value"><?php echo $cert['sex']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የተወለደበት ቀን / Date of Birth</span></div>
                    <div class="field-value"><?php echo $cert['date_of_birth']; ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>ዜግነት / Nationality</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['nationality']); ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>ሞቱ የተከሰተበት ቦታ / Place of Death</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['place_of_death']); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>ሞቱ የተከሰተበት ቀን / Date of Death</span></div>
                    <div class="field-value"><?php echo $cert['date_of_death']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>ሞቱ የተመዘገበበት ቀን / Date of Registration</span></div>
                    <div class="field-value"><?php echo $cert['registered_date']; ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="field">
                    <div class="field-label"><span>የምስክር ወረቀት የተሰጠበት ቀን / Date Certificate Issued</span></div>
                    <div class="field-value"><?php echo date('Y-m-d'); ?></div>
                </div>
            </div>

            <div class="form-row" style="margin-top:20px;">
                <div class="field">
                    <div class="field-label"><span>የክብር መዝጋቢ ሙሉ ስም / Name of Civil Registrar</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($registrar_name); ?></div>
                </div>
            </div>

            <div class="signature-box" style="margin-top:30px;">
                <div class="field-label"><span>ፊርማ / Signature</span></div>
                <div class="field-value" style="height:40px;"></div>
            </div>

            <div class="seal-box" style="margin-top:30px;">
                <div class="field-label"><span>ማኅተም / Seal</span></div>
                <div style="width:80px;height:80px;border:2px dashed #999;border-radius:50%;display:inline-block;margin-top:5px;"></div>
            </div>

        </div>
    </div>
</body>
</html>
