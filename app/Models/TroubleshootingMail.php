<?php


namespace App\Models;

use App\Models\Traits\WithStatus;

class TroubleshootingMail extends BaseModel
{
  // Указываем имя таблицы в базе данных
  protected $table = 'mails_log';
  public $timestamps = false;
  protected $guarded = ['id'];

  public static function saveLog($to, $subject, $body, $from, $result, $debug, $smtpDebugHtml)
  {
    return self::create([
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'from' => $from,
            'result' => $result ? 'success' : 'failure',
            'debug' => $debug,
            'smtpDebugHtml' => $smtpDebugHtml,
            'add_tm' => time(),
            'ip' => getIp()
    ]);
  }
}
