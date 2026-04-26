<?php
// print_divorce.php - Divorce Certificate Print Template
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

if (!isset($_GET['id'])) die("Certificate ID not provided.");

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT dv.*,
    h.first_name as h_first, h.father_name as h_father, h.grandfather_name as h_grand, h.date_of_birth as h_dob, h.place_of_birth as h_pob, h.nationality as h_nat,
    w.first_name as w_first, w.father_name as w_father, w.grandfather_name as w_grand, w.date_of_birth as w_dob, w.place_of_birth as w_pob, w.nationality as w_nat
    FROM divorce_certificates dv
    JOIN persons h ON dv.husband_id = h.id
    JOIN persons w ON dv.wife_id = w.id
    WHERE dv.id = ?");
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
    <title>Print Divorce Certificate</title>
    <link rel="stylesheet" href="assets/css/certificate.css">
</head>
<body class="print-mode">

    <div class="print-btn-container no-print">
        <button onclick="window.print()">🖨️ Print Certificate</button>
        <button onclick="window.location.href='divorces.php'" style="background:#666; margin-left:10px;">Back</button>
        <p style="margin-top:10px; color:#555;">Set Orientation to <b>Landscape</b> and enable <b>Background Graphics</b>.</p>
    </div>

    <div class="certificate-wrapper border-divorce">
        <div class="cert-inner">

            <div class="cert-header">
                <div class="cert-header-spacer"></div>
                <div class="cert-header-center">
                    <img src="assets/images/ethiopia-flag.png" alt="Ethiopian Flag" style="width:90px; height:auto; margin:0 auto 10px; display:block; border-radius:3px;">
                    <div class="title-am">በኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ የወሳኝ ኩነት ምዝገባ</div>
                    <div class="title-en">Federal Democratic Republic of Ethiopia Vital Events Registration</div>
                    <div class="cert-title">የፍቺ ምስክር ወረቀት<br>Divorce Certificate</div>
                </div>
                <div class="top-meta">
                    <div class="multi-field">
                        <span>የፍቺ ክብር መዝገብ ቅጽ ቁጥር / Divorce Register Form Number:</span>
                        <strong><?php echo $cert['certificate_number']; ?></strong>
                    </div>
                    <div class="multi-field">
                        <span>የፍቺ ምዝገባ ልዩ መለያ ቁጥር / Divorce Registration Unique ID:</span>
                        <strong>DIV-<?php echo str_pad($cert['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Two Column: Divorcée (Husband Left) | Divorcée (Wife Right) -->
            <div style="display:flex; gap:30px; margin-top:15px;">
                <!-- HUSBAND SIDE -->
                <div style="flex:1;">
                    <h4 style="text-align:center; border-bottom:1px solid #333; padding-bottom:5px; margin-bottom:10px;">የተፋቺው / Divorcée (Husband)</h4>
                    <div style="display:flex; flex-direction:column;">
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የተፋቺው ስም / Divorcée Name</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['h_first']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የአባት ስም / Father's Name</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['h_father']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የአያት ስም / Grand Father's Name</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['h_grand']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የትውልድ ቀን / Date of Birth</span></div>
                            <div class="field-value"><?php echo $cert['h_dob']; ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የትውልድ ቦታ / Place of Birth</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['h_pob']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>ዜግነት / Nationality</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['h_nat']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- WIFE SIDE -->
                <div style="flex:1;">
                    <h4 style="text-align:center; border-bottom:1px solid #333; padding-bottom:5px; margin-bottom:10px;">የተፋቺዋ / Divorcée (Wife)</h4>
                    <div style="display:flex; flex-direction:column;">
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የተፋቺዋ ስም / Divorcée Name</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['w_first']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የአባት ስም / Father's Name</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['w_father']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የአያት ስም / Grand Father's Name</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['w_grand']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የትውልድ ቀን / Date of Birth</span></div>
                            <div class="field-value"><?php echo $cert['w_dob']; ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>የትውልድ ቦታ / Place of Birth</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['w_pob']); ?></div>
                        </div>
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>ዜግነት / Nationality</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['w_nat']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Common Divorce Details -->
            <div class="form-row" style="margin-top:15px;">
                <div class="field">
                    <div class="field-label"><span>ፍቺው የተፈጸመበት ቀን / Date of Divorce</span></div>
                    <div class="field-value"><?php echo $cert['date_of_divorce']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>ፍቺው የተፈጸመበት ቦታ / Place of Divorce</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['place_of_divorce']); ?></div>
                </div>
            </div>

            <div class="form-row" style="margin-top:10px;">
                <div class="field">
                    <div class="field-label"><span>የፍቺው የምዝገባ ቀን / Date of Divorce Registration</span></div>
                    <div class="field-value"><?php echo $cert['registered_date']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የምስክር ወረቀት የተሰጠበት ቀን / Date Certificate Issued</span></div>
                    <div class="field-value"><?php echo date('Y-m-d'); ?></div>
                </div>
            </div>

            <div class="form-row" style="margin-top:10px;">
                <div class="field">
                    <div class="field-label"><span>የክብር መዝጋቢ ሙሉ ስም / Name of Civil Registrar</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($registrar_name); ?></div>
                </div>
            </div>

            <div class="signature-box" style="margin-top:25px;">
                <div class="field-label"><span>ፊርማ / Signature</span></div>
                <div class="field-value" style="height:40px;"></div>
            </div>

            <div class="seal-box" style="margin-top:25px;">
                <div class="field-label"><span>ማኅተም / Seal</span></div>
                <div style="width:80px;height:80px;border:2px dashed #999;border-radius:50%;display:inline-block;margin-top:5px;"></div>
            </div>

        </div>
    </div>
</body>
</html>
