<?php

namespace App\Services;

use App\Models\TroubleshootingMail;
use PHPMailer\PHPMailer\PHPMailer;

class MailSender
{

    /**
     * Holds collected SMTP debug output for the current message.
     *
     * @var string
     */
    protected $smtpDebugHtml = '';

    public function send($to, $subject, $body, $from = '', $attachments = [], $smtpDebug = 4)
    {

        $mail = new PHPMailer();

        if (!empty(SMTP_CONFIGS['mail_smtp'])) {
            $mail->IsSMTP();
            $mail->SMTPDebug = $smtpDebug;
            $mail->Debugoutput = function ($str, $level) {
                $this->smtpDebugHtml .= date('Y-m-d H:i:s') . " {$level}: {$str}</br>";
            };
            $mail->Host = SMTP_CONFIGS['mail_smtp_host'];
            $mail->Port = SMTP_CONFIGS['mail_smtp_port'];
            if (SMTP_CONFIGS['mail_smtp_user'] != "" && SMTP_CONFIGS['mail_smtp_password'] != "") {
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_CONFIGS['mail_smtp_user'];
                $mail->Password = SMTP_CONFIGS['mail_smtp_password'];
            }

            if (SMTP_CONFIGS['mail_smtp_secure'] != '') {
                $mail->SMTPSecure = SMTP_CONFIGS['mail_smtp_secure'];
            }
        }

        if (empty($from)) {
            $from = SMTP_CONFIGS['mail_smtp_user'];
        }

        $mail->setFrom($from, getConfig('title'));
        $mail->Sender = $from;
        $mail->addAddress($to);
        $mail->CharSet = "utf-8";
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);// Add attachments
            }
        }

        // Send mail
        $result = $mail->send();

        // log the email and its result in the database
        $debug = $mail->ErrorInfo;

//    if (!$result) {
        TroubleshootingMail::saveLog($to, $subject, $body, $from, $result, $debug, $this->smtpDebugHtml);
//    }

        return [
            'error' => !$result,
            'msg' => $result ? 'Mail successfully sent' : $debug,
        ];

    }
}
