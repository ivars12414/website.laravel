<?php

namespace App\Models;

use App\Models\Traits\WithStatus;

class MailLog extends BaseModel
{
    // Указываем имя таблицы в базе данных
    protected $table = 'mails_log';
    public $timestamps = false;
    protected $guarded = ['id'];

    public static function saveLog(string $label, string $body, string $subject)
    {
        return self::create([
            'label' => $label,
            'body' => $body,
            'subject' => $subject,
            'sent_tm' => time()
        ]);
    }
}
