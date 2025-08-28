<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $required_fields = [
        'name','surname','email','phone','guardian_phone','origin','dob','residence',
        'school','year','course','reason','interests','payment_method','payment_plan'
    ];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error = "Please fill in all required fields.";
            break;
        }
    }

    // Validate proof file
    if (!$error) {
        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] != 0) {
            $error = 'Please attach proof of payment.';
        } else {
            $allowed_ext = ['jpg','jpeg','png','pdf'];
            $file_ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_ext)) $error='Invalid file type! Use jpg, jpeg, png, pdf.';
            elseif ($_FILES['proof']['size'] > 5*1024*1024) $error='File too large (max 5MB).';
        }
    }

    if (!$error) {
        $upload_dir = __DIR__.'/uploads/';
        if(!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

        $uploaded_file = $upload_dir . basename($_FILES['proof']['name']);
        if(move_uploaded_file($_FILES['proof']['tmp_name'], $uploaded_file)) {

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = '';   // Replace with your Gmail
                $mail->Password   = '';      // Replace with App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('leonardjjmhone@gmail.com','ITEC ICT E SOLUTIONS');
                $mail->addAddress('leonardjjmhone@gmail.com','ITEC Admin'); // admin
                $mail->addAddress($_POST['email'], $_POST['name']); // applicant copy

                // Attach uploaded proof
                $mail->addAttachment($uploaded_file);

                // HTML email
                $html = "<h2 style='color:#ff1a1a;'>New ICT Internship Application</h2>";
                $html .= "<table style='width:100%;border-collapse:collapse;font-family:sans-serif'>";
                foreach($_POST as $key=>$val){
                    $k = ucfirst(str_replace('_',' ',$key));
                    $html .= "<tr><td style='padding:5px;border:1px solid #444'><b>$k</b></td><td style='padding:5px;border:1px solid #444'>$val</td></tr>";
                }
                $html .= "<tr><td style='padding:5px;border:1px solid #444'><b>Proof of Payment</b></td><td style='padding:5px;border:1px solid #444'>Attached</td></tr>";
                $html .= "</table>";
                $html .= "<p style='color:#ff1a1a;font-weight:bold'>
                            Note: Internship is private. Applicant must cover lodging, food, transport.
                            Working hours: 9am-12pm, 2pm-4pm.</p>";

                $mail->isHTML(true);
                $mail->Subject = 'ICT Internship Registration';
                $mail->Body    = $html;

                $mail->send();
                $success = true;

            } catch (Exception $e) {
                $error = "Email could not be sent. Error: {$mail->ErrorInfo}";
            }

        } else {
            $error = "Failed to upload proof file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="author" content="ITEC ICT E SOLUTIONS">
<meta name="description" content="Internship registration form for ITEC ICT E SOLUTIONS. Apply for an ICT internship with us.">

<title>ITEC ICT E SOLUTIONS - Internship Registration</title>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="icon" type="image/png" href="logo.png">
<style>
body {
    font-family: Arial, sans-serif;
    background: #f5f5f5;
    margin:0;
    padding:0;
}
.container {
    max-width: 800px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,0,0,0.15);
}
.logo-container {
    text-align:center;
    margin-bottom:20px;
}
.logo { max-width:150px; }
h1 { text-align:center; color:#ff1a1a; }
h2 { text-align:center; margin-bottom:30px; }
input, select { width:100%; padding:10px; margin:8px 0; border-radius:5px; border:1px solid #ccc; }
button { padding:10px 20px; margin:10px 5px; border:none; border-radius:5px; background:#ff1a1a; color:#fff; cursor:pointer; }
button:hover { background:#cc0000; }
.form-section { display:none; animation:fadein 0.6s; }
.form-section.active { display:block; }
.quill-editor { height:120px; background:#fff; border:1px solid #ccc; border-radius:5px; margin-bottom:10px; }
.buttons { text-align:right; }
footer { text-align:center; margin-top:20px; font-size:14px; color:#555; }
.success-message { text-align:center; padding:20px; background:#1c1c1c; color:#fff; border-radius:15px; box-shadow:0 0 30px rgba(255,0,0,0.6); }
@keyframes fadein { from {opacity:0;} to {opacity:1;} }
</style>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<body>
<div class="container">

    <div class="logo-container">
        <img src="logo.png" alt="ITEC Logo" class="logo">
    </div>

    <h1>ITEC ICT E SOLUTIONS</h1>
    <h2>Internship Registration Form</h2>

    <?php if($success): ?>
        <div class="success-message">
            <h2>Application Submitted!</h2>
            <p>Thank you for applying. A copy has been sent to your email and we will contact you soon.</p>
        </div>
    <?php else: ?>
        <?php if($error): ?>
            <div style="color:red; text-align:center; font-weight:bold;"><?=$error?></div>
        <?php endif; ?>

        <form id="internForm" method="POST" enctype="multipart/form-data">

            <!-- SECTION 1 -->
            <div class="form-section active">
                <h3>Personal Information</h3>
                <input type="text" name="name" placeholder="First Name" required>
                <input type="text" name="surname" placeholder="Surname" required>
                <input type="email" name="email" placeholder="Email (example@example.com)" required>
                <input type="text" name="phone" placeholder="Phone (+265...)" required>
                <input type="text" name="guardian_phone" placeholder="Guardian Phone (+265...)" required>
                <input type="text" name="origin" placeholder="Home of Origin" required>
                <label>Date of Birth:</label>
                <input type="date" name="dob" required>
                <input type="text" name="residence" placeholder="Current Residence" required>
                <div class="buttons"><button type="button" class="next">Next</button></div>
            </div>

            <!-- SECTION 2 -->
            <div class="form-section">
                <h3>Academic Information</h3>
                <input type="text" name="school" placeholder="School Currently Enrolled" required>
                <input type="text" name="year" placeholder="Year of Study" required>
                <input type="text" name="course" placeholder="Course Being Undertaken" required>
                <div class="buttons">
                    <button type="button" class="prev">Previous</button>
                    <button type="button" class="next">Next</button>
                </div>
            </div>

            <!-- SECTION 3 -->
            <div class="form-section">
                <h3>Internship Details</h3>
                <label>Why you need this internship:</label>
                <div id="reasonEditor" class="quill-editor"></div>
                <input type="hidden" name="reason" id="reasonInput">

                <label>Areas of Interest:</label>
                <div id="interestsEditor" class="quill-editor"></div>
                <input type="hidden" name="interests" id="interestsInput">

                <p style="color:#555;">
                    Internship is private: no salary. Applicants must cover lodging, food, transport.<br>
                    Working hours: <b>9am–12pm, 2pm–4pm</b>. Must have a computer & place to stay.
                </p>
                <div class="buttons">
                    <button type="button" class="prev">Previous</button>
                    <button type="button" class="next">Next</button>
                </div>
            </div>

            <!-- SECTION 4 -->
            <div class="form-section">
                <h3>Payment Information</h3>
                <label>Payment Method:</label>
                <select name="payment_method" required>
                    <option value="">-- Select --</option>
                    <option>Mpamba - +265881610633</option>
                    <option>Airtel Money - +265992919716</option>
                    <option>National Bank - 1003549441</option>
                </select>

                <label>Payment Plan:</label>
                <select name="payment_plan" required>
                    <option value="">-- Select --</option>
                    <option>Below 4 months - MK200,000 (Payable Twice)</option>
                    <option>3–6 months - MK380,000</option>
                    <option>6–12 months - MK690,000</option>
                </select>

                <label>Proof of Payment (jpg, png, pdf; max 5MB):</label>
                <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required>

                <div class="buttons">
                    <button type="button" class="prev">Previous</button>
                    <button type="submit">Submit</button>
                </div>
            </div>

        </form>
    <?php endif; ?>

    <footer>
        <p>© 2025 ITEC ICT E SOLUTIONS</p>
    </footer>
</div>

<script>

// Disable right click
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    alert("Right-click is disabled on this page.");
});

// Quill setup
var reasonQuill = new Quill('#reasonEditor', {theme: 'snow', placeholder: 'Write your motivation...', modules: { toolbar: [['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link']] } });
var interestsQuill = new Quill('#interestsEditor', {theme: 'snow', placeholder: 'List your interests...', modules: { toolbar: [['bold','italic','underline'],[{list:'ordered'},{list:'bullet'}],['link']] } });

// Copy Quill HTML before submit
document.getElementById('internForm').onsubmit = function() {
    document.getElementById('reasonInput').value = reasonQuill.root.innerHTML;
    document.getElementById('interestsInput').value = interestsQuill.root.innerHTML;
};

// Multi-step navigation
document.addEventListener("DOMContentLoaded", () => {
    const sections = document.querySelectorAll(".form-section");
    let current = 0;
    function showSection(i){ sections.forEach((s,idx)=>s.classList.toggle("active", idx===i)); }
    document.querySelectorAll(".next").forEach(btn => btn.addEventListener("click",()=>{if(current<sections.length-1) showSection(++current);}));
    document.querySelectorAll(".prev").forEach(btn => btn.addEventListener("click",()=>{if(current>0) showSection(--current);}));
});
</script>
</body>
</html>

