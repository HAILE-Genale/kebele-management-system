<?php
// print_marriage.php - Marriage Certificate Print Template
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

if (!isset($_GET['id'])) die("Certificate ID not provided.");

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT m.*,
    h.first_name as h_first, h.father_name as h_father, h.grandfather_name as h_grand, h.date_of_birth as h_dob, h.place_of_birth as h_pob, h.nationality as h_nat,
    w.first_name as w_first, w.father_name as w_father, w.grandfather_name as w_grand, w.date_of_birth as w_dob, w.place_of_birth as w_pob, w.nationality as w_nat
    FROM marriage_certificates m
    JOIN persons h ON m.husband_id = h.id
    JOIN persons w ON m.wife_id = w.id
    WHERE m.id = ?");
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
    <title>Print Marriage Certificate</title>
    <link rel="stylesheet" href="assets/css/certificate.css?v=2">
</head>
<body class="print-mode">

    <div class="print-btn-container no-print">
        <button onclick="window.print()">🖨️ Print Certificate</button>
        <button onclick="window.location.href='marriages.php'" style="background:#666; margin-left:10px;">Back</button>
        <p style="margin-top:10px; color:#555;">Set Orientation to <b>Landscape</b> and enable <b>Background Graphics</b>.</p>
    </div>

    <div class="certificate-wrapper border-marriage">
        <div class="cert-inner">

            <div class="cert-header" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; width:100%;">
                <!-- Left Column -->
                <div style="flex: 0 0 320px; text-align:left; font-size:12px; line-height:1.4;">
                    <div style="margin-bottom:15px; font-weight:bold;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                            <span>የጋብቻ ክብር መዝገብ ቅጽ ቁጥር</span>
                            <span style="flex:1; border-bottom:1px dashed #333; margin-left:5px; text-align:center; font-weight:normal;"><?php echo htmlspecialchars($cert['certificate_number']); ?></span>
                        </div>
                        <div>Marriage Register Form Number</div>
                        
                        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:8px;">
                            <span>የጋብቻ ምዝገባ ልዩ መለያ ቁጥር</span>
                            <span style="flex:1; border-bottom:1px dashed #333; margin-left:5px; text-align:center; font-weight:normal;">MRG-<?php echo str_pad($cert['id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div>Marriage Registration Unique Identification Number</div>
                    </div>
                    <div style="width:130px; height:160px; border:4px solid #111; margin-left:15px;"></div>
                </div>

                <!-- Center Column -->
                <div class="cert-header-center" style="flex:1; text-align:center; padding-top:10px;">
                    <img src="assets/images/ethiopia-flag.png" alt="Ethiopian Flag" style="width:110px; height:auto; margin:0 auto 10px; display:block; border-radius:3px;">
                    <div class="title-am" style="font-size:20px; font-weight:bold; margin-bottom:5px;">በኢትዮጵያ ፌዴራላዊ ዲሞክራሲያዊ ሪፐብሊክ የወሳኝ ኩነት ምዝገባ</div>
                    <div class="title-en" style="font-size:15px; font-weight:bold; margin-bottom:15px;">Federal Democratic Republic of Ethiopia Vital Event Registration</div>
                    <div class="cert-title" style="font-size:24px; font-weight:bold;">የጋብቻ ምስክር ወረቀት<br>Marriage Certificate</div>
                </div>

                <!-- Right Column -->
                <div style="flex: 0 0 360px; text-align:left; font-size:12px; line-height:1.4;">
                    <div style="margin-bottom:15px; font-weight:bold;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                            <span>የሚስት ልደት ምዝገባ ልዩ መለያ ቁጥር</span>
                            <span style="flex:1; border-bottom:1px dashed #333; margin-left:5px; text-align:center; font-weight:normal;">BR-<?php echo str_pad($cert['wife_id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div>Wife's Birth Registration Unique Identification Number</div>
                        
                        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:8px;">
                            <span>የባል ልደት ምዝገባ ልዩ መለያ ቁጥር</span>
                            <span style="flex:1; border-bottom:1px dashed #333; margin-left:5px; text-align:center; font-weight:normal;">BR-<?php echo str_pad($cert['husband_id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div>Husband's Birth Registration Unique Identification Number</div>
                    </div>
                    <div style="width:130px; height:160px; border:4px solid #111; margin-left:auto; margin-right:15px;"></div>
                </div>
            </div>

            <!-- Two Column: Wife (Left) | Husband (Right) -->
            <div style="display:flex; gap:30px; margin-top:15px;">
                <!-- WIFE SIDE -->
                <div style="flex:1;">
                    <h4 style="text-align:center; border-bottom:1px solid #333; padding-bottom:5px; margin-bottom:10px;">የሚስት / Wife</h4>
                    <div class="form-row" style="flex-direction:column;">
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>ስም / Name</span></div>
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
                            <div class="field-label"><span>ዜግነት / Nationality</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['w_nat']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- HUSBAND SIDE -->
                <div style="flex:1;">
                    <h4 style="text-align:center; border-bottom:1px solid #333; padding-bottom:5px; margin-bottom:10px;">የባል / Husband</h4>
                    <div class="form-row" style="flex-direction:column;">
                        <div class="field" style="margin-bottom:8px;">
                            <div class="field-label"><span>ስም / Name</span></div>
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
                            <div class="field-label"><span>ዜግነት / Nationality</span></div>
                            <div class="field-value"><?php echo htmlspecialchars($cert['h_nat']); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Common Marriage Details -->
            <div class="form-row" style="margin-top:15px;">
                <div class="field">
                    <div class="field-label"><span>የጋብቻ ቀን / Date of Marriage</span></div>
                    <div class="field-value"><?php echo $cert['date_of_marriage']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>የጋብቻ ቦታ / Place of Marriage</span></div>
                    <div class="field-value"><?php echo htmlspecialchars($cert['place_of_marriage']); ?></div>
                </div>
            </div>

            <div class="form-row" style="margin-top:10px;">
                <div class="field">
                    <div class="field-label"><span>የምዝገባ ቀን / Date of Registration</span></div>
                    <div class="field-value"><?php echo $cert['registered_date']; ?></div>
                </div>
                <div class="field">
                    <div class="field-label"><span>ወረቀት የተሰጠበት ቀን / Date Certificate Issued</span></div>
                    <div class="field-value"><?php echo date('Y-m-d'); ?></div>
                </div>
            </div>

            <div class="form-row" style="margin-top:10px;">
                <div class="field">
                    <div class="field-label"><span>የክብር መዝጋቢ ስም / Name of Civil Registrar</span></div>
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
