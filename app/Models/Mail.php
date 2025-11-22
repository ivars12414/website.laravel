<?php

namespace App\Models;

use App\Services\MailSender;

class Mail extends BaseModel
{
    // Указываем имя таблицы в базе данных
    protected $table = 'mails';
    public $timestamps = false;
    protected $guarded = [];

    public static function send_mail_template($lang_id, $label, $to, $vars, $attachments = []): array
    {

        $error = false;
        $msg = "";

        // Получить контент письма из базы
        $email_content = getWhat("mail", "WHERE `lang_id` = '{$lang_id}' AND `label` = '{$label}'");

        // Получить основной шаблон
        $email_template = getWhat("mail_template", "WHERE `status` = '1' AND `lang_id` = '{$lang_id}'");

        if (!$email_content) {
            $error = true;
            $msg = "Content template not found";
        }

        if (!$email_template && !$error) {
            $error = true;
            $msg = "Main template not found";
        }

        if (!$error) {

            // Вставить переменные в Subject
            $subject = strip_tags(str_replace(array_keys($vars), array_values($vars), $email_content['subject']));

            // Вставить переменные в HTML
            $content_html = str_replace(array_keys($vars), array_values($vars), $email_content['body']);

            $copyrightDate = copyrightDate((int)getConfig('copyright_year'));
            $title = getConfig('title');

            $content_html = str_replace(['%emailLogo%', '%title%', '%copyright_date%', '%domain%'], [
                "https://" . str_replace("www.", "", getenv("HTTP_HOST")) . '/userfiles/mails_templates/' . $email_template['icon'],
                $title,
                $copyrightDate,
                str_replace("www.", "", getenv("HTTP_HOST")),
            ], $content_html);

            // Вставить переменные в основной шаблон письма

            $html = str_replace(['%emailLogo%', '%emailBody%', '%title%', '%copyright_date%', '%domain%'], [
                "https://" . str_replace("www.", "", getenv("HTTP_HOST")) . '/userfiles/mails_templates/' . $email_template['icon'],
                $content_html,
                $title,
                $copyrightDate,
                str_replace("www.", "", getenv("HTTP_HOST")),
            ], $email_template['descr']);

            $from = getConfig('request_from_email');

            MailLog::saveLog($label, $html, $subject);

            $mailSender = new MailSender();
            $mailSender->send($to, $subject, $html, $from, $attachments);

        }

        return ['error' => $error, 'msg' => $msg];

    }
}
