<?php
include "db.php";
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Twilio SMS
function sendTwilioSMS($to, $message){
    $account_sid = 'YOUR_TWILIO_SID';
    $auth_token = 'YOUR_TWILIO_AUTH';
    $twilio_number = 'YOUR_TWILIO_NUMBER';

    $data = ['To'=>$to,'From'=>$twilio_number,'Body'=>$message];
    $ch = curl_init("https://api.twilio.com/2010-04-01/Accounts/$account_sid/Messages.json");
    curl_setopt($ch, CURLOPT_POST,true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERPWD, "$account_sid:$auth_token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_exec($ch);
    curl_close($ch);
}

$today = date('Y-m-d');
$stmt = $pdo->prepare("
SELECT a.*, s.name as service_name, b.business_name 
FROM appointments a
JOIN services s ON a.service_id = s.id
JOIN businesses b ON a.business_id = b.id
WHERE a.status='scheduled' AND a.appointment_date=?
");
$stmt->execute([$today]);
$appointments = $stmt->fetchAll();

foreach($appointments as $a){
    $business_id = $a['business_id'];

    // Load templates
    $stmt2 = $pdo->prepare("SELECT * FROM reminder_templates WHERE business_id=?");
    $stmt2->execute([$business_id]);
    $templates = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $template_map = [];
    foreach($templates as $t) $template_map[$t['type']] = $t;

    $placeholders = [
        '{customer_name}' => $a['customer_name'],
        '{appointment_date}' => $a['appointment_date'],
        '{appointment_time}' => $a['appointment_time'],
        '{service_name}' => $a['service_name'],
        '{business_name}' => $a['business_name']
    ];

    // Email
    if(isset($template_map['email'])){
        $mail = new PHPMailer(true);
        $status='sent'; $error='';
        try{
            $mail->isSMTP();
            $mail->Host='smtp.yourdomain.com';
            $mail->SMTPAuth=true;
            $mail->Username='your_email@domain.com';
            $mail->Password='your_email_password';
            $mail->SMTPSecure='tls';
            $mail->Port=587;
            $mail->setFrom('no-reply@smartbookr.com','SmartBookr');
            $mail->addAddress($a['customer_email'],$a['customer_name']);
            $mail->isHTML(true);
            $mail->Subject=str_replace(array_keys($placeholders),array_values($placeholders),$template_map['email']['subject']);
            $mail->Body=str_replace(array_keys($placeholders),array_values($placeholders),$template_map['email']['message']);
            $mail->send();
        }catch(Exception $e){$status='failed';$error=$mail->ErrorInfo;}
        $stmt_log=$pdo->prepare("INSERT INTO reminder_logs (appointment_id,business_id,type,recipient,status,error_message) VALUES (?,?,?,?,?,?)");
        $stmt_log->execute([$a['id'],$business_id,'email',$a['customer_email'],$status,$error]);
    }

    // SMS
    if(isset($template_map['sms'])){
        $status='sent'; $error='';
        try{
            $sms_message=str_replace(array_keys($placeholders),array_values($placeholders),$template_map['sms']['message']);
            sendTwilioSMS($a['customer_phone'],$sms_message);
        }catch(Exception $e){$status='failed'; $error=$e->getMessage();}
        $stmt_log=$pdo->prepare("INSERT INTO reminder_logs (appointment_id,business_id,type,recipient,status,error_message) VALUES (?,?,?,?,?,?)");
        $stmt_log->execute([$a['id'],$business_id,'sms',$a['customer_phone'],$status,$error]);
    }
}
?>
