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
            $error = 'Please fill in all required fields.';
            break;
        }
    }

    // Validate file
    if (!isset($_FILES['proof']) || $_FILES['proof']['error'] != 0) {
        $error = 'Please attach proof of payment.';
    } else {
        $allowed_ext = ['jpg','jpeg','png','pdf'];
        $file_ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext,$allowed_ext)) $error='Invalid file type! Use jpg, jpeg, png, pdf.';
        elseif ($_FILES['proof']['size']>5*1024*1024) $error='File too large (max 5MB).';
    }

    if(!$error){
        $upload_dir=__DIR__.'/uploads/';
        if(!file_exists($upload_dir)) mkdir($upload_dir,0777,true);
        $uploaded_file=$upload_dir.basename($_FILES['proof']['name']);

        if(move_uploaded_file($_FILES['proof']['tmp_name'],$uploaded_file)){
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'leonardjjmhone@gmail.com';
                $mail->Password   = 'ehyq hbtt vcir easb';
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                $applicant_email = $_POST['email'];

                //Recipients
                $mail->setFrom('leonardjjmhone@gmail.com','ITEC ICT');
                $mail->addAddress('leonardjjmhone@gmail.com','Registration Desk'); // company inbox
                $mail->addAddress($applicant_email, $_POST['name']); // send copy to applicant

                //Attachments
                $mail->addAttachment($uploaded_file);

                //HTML email body
                $html = "<h2 style='color:#ff1a1a'>New ICT Internship Registration</h2>";
                $html .= "<table style='width:100%;border-collapse:collapse;font-family:sans-serif'>";
                foreach($_POST as $key=>$val){
                    $k = ucfirst(str_replace('_',' ',$key));
                    $html .= "<tr><td style='padding:5px;border:1px solid #444'><b>$k</b></td><td style='padding:5px;border:1px solid #444'>$val</td></tr>";
                }
                $html .= "<tr><td style='padding:5px;border:1px solid #444'><b>Proof of Payment</b></td><td style='padding:5px;border:1px solid #444'>Attached</td></tr>";
                $html .= "</table>";
                $html .= "<p style='color:#ff1a1a;font-weight:bold'>Note: Internship is private. Applicant must cover lodging, food, transport. Working hours 9am-12pm, 2pm-4pm.</p>";

                $mail->isHTML(true);
                $mail->Subject = 'ICT Internship Registration';
                $mail->Body    = $html;

                $mail->send();
                $success = true;

            } catch (Exception $e) {
                $error = "Email could not be sent. Error: {$mail->ErrorInfo}";
            }
        } else {
            $error="Failed to upload file.";
        }
    }
}
?>

