<?php
// print_person.php - Printable Kebele ID Card (compact handy-card size)
require_once 'includes/auth.php';
requireLogin();
require_once 'config/database.php';

if (!isset($_GET['id'])) { die("Person ID not provided."); }

$person_id = intval($_GET['id']);
$db  = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM persons WHERE id = ?");
$stmt->execute([$person_id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) { die("Person record not found."); }

// ── Ethiopian Calendar Converter ──────────────────────────────────────
function toEthiopian(int $gy, int $gm, int $gd): array {
    // Gregorian → Julian Day Number
    $a   = intval((14 - $gm) / 12);
    $y   = $gy + 4800 - $a;
    $m   = $gm + 12 * $a - 3;
    $jdn = $gd + intval((153 * $m + 2) / 5) + 365 * $y
         + intval($y / 4) - intval($y / 100) + intval($y / 400) - 32045;

    // JDN → Ethiopian  (epoch: Meskerem 1, 1 ET = JDN 1724221)
    $d = $jdn - 1724221;
    $c = intval($d / 1461);   // complete 4-year cycles
    $r = $d % 1461;           // remaining days

    // within cycle: years 1,2,3(leap),4 → offsets 0,365,730,1096
    if      ($r < 365)  { $yi = 0; }
    elseif  ($r < 730)  { $yi = 1; $r -= 365; }
    elseif  ($r < 1096) { $yi = 2; $r -= 730; }
    else                { $yi = 3; $r -= 1096; }

    $ey = 4 * $c + $yi + 1;
    $em = intval($r / 30) + 1;
    $ed = ($r % 30) + 1;

    $names = ['','Meskerem','Tikimit','Hidar','Tahesas','Tir','Yekatit',
              'Megabit','Miyazia','Ginbot','Sene','Hamle','Nehase','Pagumé'];
    $amh   = ['','መስከረም','ጥቅምት','ህዳር','ታህሳስ','ጥር','የካቲት',
              'መጋቢት','ሚያዚያ','ግንቦት','ሰኔ','ሐምሌ','ነሐሴ','ጳጉሜ'];
    return ['year'=>$ey,'month'=>$em,'day'=>$ed,
            'name'=>$names[$em],'amh'=>$amh[$em]];
}

// Age (Gregorian diff is fine)
$age = '';
if (!empty($p['date_of_birth'])) {
    $age = (new DateTime($p['date_of_birth']))->diff(new DateTime())->y;
}

// Birth year in Ethiopian calendar (number only)
$birth_year = '';
if (!empty($p['date_of_birth'])) {
    [$by,$bm,$bd] = explode('-', $p['date_of_birth']);
    $et_birth   = toEthiopian((int)$by, (int)$bm, (int)$bd);
    $birth_year = $et_birth['year']; // ET year number only
}

// Today's date in Ethiopian calendar — pure numbers DD/MM/YYYY
$now        = new DateTime();
$et_today   = toEthiopian((int)$now->format('Y'), (int)$now->format('m'), (int)$now->format('d'));
$date_issued = sprintf('%02d/%02d/%04d', $et_today['day'], $et_today['month'], $et_today['year']);

$card_number = 'KBL-' . str_pad($p['id'], 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="om">
<head>
    <meta charset="UTF-8">
    <title>Kebele ID - <?php echo htmlspecialchars($p['first_name'].' '.$p['father_name']); ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background: #d0d0d0;
            font-family: 'Times New Roman', Times, serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
        }

        /* ── print controls ── */
        .no-print {
            text-align: center;
            margin-bottom: 22px;
        }
        .no-print button {
            padding: 9px 24px;
            font-size: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
            font-family: sans-serif;
        }
        .btn-p { background:#1a5276; color:#fff; }
        .btn-b { background:#555;    color:#fff; }
        .no-print small {
            display:block;
            margin-top:8px;
            color:#555;
            font-family:sans-serif;
            font-size:12px;
        }

        /* ── card shell  (A6 landscape ≈ 148 × 105 mm) ── */
        .id-card {
            width: 148mm;
            min-height: 105mm;
            background: #fff;
            border: 1.5px solid #444;
            padding: 4mm 4mm 3mm;
            display: flex;
            flex-direction: column;
        }

        /* ── header ── */
        .hdr {
            text-align: center;
            border-bottom: 1.5px solid #000;
            padding-bottom: 3px;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .hdr-top   { font-size: 6.5pt; font-weight: bold; letter-spacing: .3px; }
        .hdr-mid   { font-size: 6pt;   color: #333; }
        .hdr-type  { font-size: 9pt;   font-weight: bold; text-transform: uppercase; letter-spacing: .8px; }
        .hdr-kebele{ font-size: 6pt;   color: #555; }

        /* ── body: left + right ── */
        .body {
            display: flex;
            flex: 1;
            gap: 0;
        }

        /* LEFT */
        .col-left {
            flex: 1;
            padding-right: 4px;
            border-right: 1px solid #888;
        }

        /* field row */
        .fr {
            display: flex;
            align-items: flex-end;
            margin-bottom: 3.5px;
        }
        .fl {
            line-height: 1.2;
            min-width: 108px;
        }
        .fm { font-size: 6.8pt; font-weight: bold; }
        .fs { font-size: 5.5pt; color: #444; }
        .fv {
            flex: 1;
            border-bottom: .8px solid #333;
            font-size: 6.8pt;
            font-weight: bold;
            padding: 0 3px 1px;
            min-width: 50px;
        }

        /* split row (two fields side by side) */
        .fr-split { display:flex; gap:6px; margin-bottom:3.5px; }
        .fr-split .fu { display:flex; align-items:flex-end; }
        .fr-split .fu .fl { min-width: 60px; }

        /* RIGHT */
        .col-right {
            width: 44mm;
            padding-left: 4px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .cn-label {
            font-size: 5.5pt;
            font-weight: bold;
            width: 100%;
            text-align: right;
            letter-spacing: .2px;
        }
        .cn-value {
            font-size: 7pt;
            font-weight: bold;
            width: 100%;
            text-align: right;
            border-bottom: .8px solid #333;
            margin-bottom: 4px;
        }

        /* photo box */
        .photo {
            width: 30mm;
            height: 38mm;
            border: 1.5px solid #555;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7pt;
            color: #aaa;
            text-align: center;
            margin-bottom: 5px;
        }

        /* renewal */
        .renewal {
            width: 100%;
            border: .8px solid #aaa;
            padding: 2px 3px;
        }
        .ren-title { font-size: 5.5pt; font-weight: bold; margin-bottom: 2px; }
        .ren-row {
            display:flex;
            align-items:flex-end;
            gap:4px;
            margin-bottom:2px;
        }
        .ren-lbl  { font-size:5.5pt; min-width:22px; }
        .ren-line { flex:1; border-bottom:.7px solid #666; height:10px; }
        .ren-sub  { font-size:5pt;  min-width:22px; }

        /* ── footer ── */
        .footer {
            margin-top: 4px;
            border-top: 1.2px solid #000;
            padding-top: 4px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .ft-l { font-size: 6pt; }
        .ft-l b { font-size: 7pt; display:block; }
        .ft-r { text-align:right; }
        .ft-r .sig-label { font-size: 6pt; font-weight:bold; }
        .ft-r .sig-line  { display:block; border-bottom:.8px solid #333; width:110px; height:20px; margin-top:3px; }

        /* ── print ── */
        @media print {
            body { background:#fff; padding:0; }
            .no-print { display:none; }
            .id-card { border-color:#000; width:100%; }
            @page { size: A6 landscape; margin: 5mm; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-p" onclick="window.print()">🖨️ Print ID Card</button>
    <button class="btn-b" onclick="window.location.href='persons.php'">← Back</button>
    <small>Print as <strong>A6 Landscape</strong> · Enable <strong>Background Graphics</strong></small>
</div>

<div class="id-card">

    <!-- HEADER -->
    <div class="hdr">
        <div class="hdr-top">MOOTUMMAA FEDERAALAA DIMOKRAATAWAA RIPPABLIKII ITOOPHIYAA</div>
        <div class="hdr-mid">Mootummaa Naannoo Oromiyaa &nbsp;|&nbsp; Biiroo Galmeessa Ummataa</div>
        <div class="hdr-type">Waraqaa Eenyummaa Lammii Kebele &nbsp;/&nbsp; Kebele Resident ID</div>
        <div class="hdr-kebele">BEKKE AGALO KEBELE</div>
    </div>

    <!-- BODY -->
    <div class="body">

        <!-- LEFT -->
        <div class="col-left">

            <div class="fr">
                <div class="fl"><div class="fm">Maqaa</div><div class="fs">ሙሉ ስም</div></div>
                <div class="fv"><?php echo htmlspecialchars($p['first_name']); ?></div>
            </div>

            <div class="fr">
                <div class="fl"><div class="fm">Maqaa Akakkayyuu</div><div class="fs">የአያት ስም</div></div>
                <div class="fv"><?php echo htmlspecialchars($p['father_name']); ?></div>
            </div>

            <div class="fr">
                <div class="fl"><div class="fm">Maqaa Hadhaa</div><div class="fs">የአባት ስም</div></div>
                <div class="fv"><?php echo htmlspecialchars($p['grandfather_name']); ?></div>
            </div>

            <div class="fr-split">
                <div class="fu" style="flex:1;">
                    <div class="fl"><div class="fm">Hojii</div><div class="fs">ሙያ</div></div>
                    <div class="fv"><?php echo htmlspecialchars($p['occupational_status'] ?? ''); ?></div>
                </div>
                <div class="fu" style="flex:0 0 auto; min-width:55px;">
                    <div class="fl" style="min-width:32px;"><div class="fm">Saala</div><div class="fs">ፆታ</div></div>
                    <div class="fv" style="min-width:40px;"><?php echo htmlspecialchars($p['sex']); ?></div>
                </div>
            </div>

            <div class="fr-split">
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:48px;"><div class="fm">Ummrii</div><div class="fs">ዕድሜ</div></div>
                    <div class="fv"><?php echo $age; ?></div>
                </div>
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:58px;"><div class="fm">Lakk Maataa</div><div class="fs">የቤተሰብ ቁጥር</div></div>
                    <div class="fv"></div>
                </div>
            </div>

            <div class="fr">
                <div class="fl"><div class="fm">Lammiummaa</div><div class="fs">ዜግነት</div></div>
                <div class="fv"><?php echo htmlspecialchars($p['nationality'] ?? 'Ethiopian'); ?></div>
            </div>

            <div class="fr">
                <div class="fl"><div class="fm">Bakka Dhaloota</div><div class="fs">የተወለደበት ቦታ</div></div>
                <div class="fv"><?php echo htmlspecialchars($p['place_of_birth'] ?? ''); ?></div>
            </div>

            <div class="fr-split">
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:70px;"><div class="fm">Bara Dhaloota</div><div class="fs">የተወለደበት ዓ.ም</div></div>
                    <div class="fv"><?php echo $birth_year; ?></div>
                </div>
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:48px;"><div class="fm">Teessoo</div><div class="fs">አድራሻ</div></div>
                    <div class="fv"></div>
                </div>
            </div>

            <div class="fr-split">
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:42px;"><div class="fm">Godina</div><div class="fs">ዞን</div></div>
                    <div class="fv">Jimma</div>
                </div>
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:38px;"><div class="fm">Aanaa</div><div class="fs">ወረዳ</div></div>
                    <div class="fv">Abba Daree</div>
                </div>
            </div>

            <div class="fr-split">
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:48px;"><div class="fm">Olaanaa</div><div class="fs">ክፍተኛ</div></div>
                    <div class="fv"></div>
                </div>
                <div class="fu" style="flex:1;">
                    <div class="fl" style="min-width:42px;"><div class="fm">Ganda</div><div class="fs">ቀበሌ</div></div>
                    <div class="fv">Bekke Agalo</div>
                </div>
            </div>

        </div><!-- /col-left -->

        <!-- RIGHT -->
        <div class="col-right">
            <div class="cn-label">Lakk Waraqaa</div>
            <div class="cn-value"><?php echo $card_number; ?></div>

            <div class="photo"><span>Photo<br>ፎቶ</span></div>

            <div class="renewal">
                <div class="ren-title">Bara Haaromsaa / የታደሰበት ዓ.ም</div>
                <div class="ren-row">
                    <span class="ren-lbl">A20</span>
                    <span class="ren-line"></span>
                    <span class="ren-sub">ታደሰ</span>
                </div>
                <div class="ren-row">
                    <span class="ren-lbl">A20</span>
                    <span class="ren-line"></span>
                    <span class="ren-sub">ታደሰ</span>
                </div>
                <div class="ren-row">
                    <span class="ren-lbl">A20</span>
                    <span class="ren-line"></span>
                    <span class="ren-sub">ታደሰ</span>
                </div>
            </div>
        </div><!-- /col-right -->

    </div><!-- /body -->

    <!-- FOOTER -->
    <div class="footer">
        <div class="ft-l">
            <span>Guyyaa Kenname / የተሰጠበት ቀን</span>
            <b><?php echo $date_issued; ?></b>
        </div>
        <div class="ft-r">
            <span class="sig-label">Mallattoo Abbo Waraqaa / የባለ ሙሉ ፈርጅ ፊርማ</span>
            <span class="sig-line"></span>
        </div>
    </div>

</div><!-- /id-card -->

</body>
</html>
