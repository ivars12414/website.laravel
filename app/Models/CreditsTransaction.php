<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CreditsTransaction extends BaseModel
{
    // Указываем имя таблицы в базе данных
    protected $table = 'credits_transaction';

    use \App\Models\Traits\BelongsToClient;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'add_data' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($transaction) {
            $transaction->nr = self::generateTransactionNumber();
            $transaction->lang_id = lang()->id;
        });
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'order');
    }

    /**
     * @throws \Exception
     */
    public function setStatus(Status $status, int $source, string $reason_hash = '', array $add_data = []): static
    {
        Log::info('[CreditsTransaction] setStatus', [
            'id' => $this->id,
            'add_data' => $add_data,
        ]);

        if ($this->status()->exists()) {
            if (empty($this->status->next_statuses)) throw new \Exception('Current status is final');
            if (!in_array($status->id, $this->status->next_statuses ?? [])) throw new \Exception('Cannot change to this status');
        }


        $this->status()->associate($status);
        $this->save();

        Log::info('[CreditsTransaction] setStatus - status saved');

        StatusLog::logChange($this->id, $status, 0, $source, $reason_hash, $add_data);

        switch ($status->label) {
            case 'credited':
                $this->client->changeBalance($this->credits, BalanceLog::TYPE_CREDIT_TRANSACTION, $this->id);
                $this->sendMailToClient();

//                $tgStartText = 'Top up completed - ';
//                $tgText = [];
//                $tgText[] = 'E-mail: ' . $this->client->mail;
//                $tgText[] = 'IP: ' . getIp();
//                $tgText[] = 'Transaction Nr: ' . $this->nr;
//                $tgText[] = 'Total amount: ' . currency($this->price_in_currency, $this->currency_code)->format(withStrongInt: false);
//                insertTelegramNotification($tgStartText . implode(". ", $tgText));
                break;
        }

        return $this;
    }

    protected static function generateTransactionNumber(): string
    {
        do {
            $number = generateNumberString(10);
        } while (self::where('nr', $number)->exists());

        return $number;
    }

    public function generateInvoice(): static
    {
        $folder = 'pdf/credits';

        $transaction = $this;

        $client = $transaction->client;

        $contacts = Contacts::where('lang_id', $transaction->lang_id)->first();

        if (!empty($transaction->pdf)) {
            $storedPdf = ltrim(str_replace('/storage/', '', $transaction->pdf), '/');
            Storage::disk('public')->delete($storedPdf);
        }

        // рендерим Blade-шаблон в HTML
        $html = view('pdf.top_up_invoice', [
            'transaction' => $transaction,
            'client' => $client,
            'contacts' => $contacts,
        ])->render();

        $tmp = sys_get_temp_dir();
        $dompdf = new Dompdf([
            'logOutputFile' => '',
            // authorize DomPdf to download fonts and other Internet assets
            'isRemoteEnabled' => true,
            // all directories must exist and not end with /
            'fontDir' => $tmp,
            'fontCache' => $tmp,
            'tempDir' => $tmp,
            'chroot' => $tmp,
        ]);
        $options = $dompdf->getOptions();
        $options->setDefaultFont('helvetica');
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        $fileName = 'invoice-' . $transaction->nr . '.pdf';
        $addFolder = date('Y/m/');
        $storagePath = $folder . '/' . $addFolder . $fileName;

        Storage::disk('public')->put($storagePath, $output);

        $transaction->update(['pdf' => '/storage/' . $storagePath]);

        return $this;
    }

    public function sendMailToClient(): array
    {
        $to = $this->client->mail;
//    $to = 'test-6dubxk0hg@srv1.mail-tester.com';

        $mail_vars = [
            '%client_name%' => $this->client->name,
            '%order_nr%' => $this->nr,
            '%minutes%' => $this->credits,
            '%order_sum%' => currency($this->price_in_currency, $this->currency_code)->format(withStrongInt: false),
        ];

        if (empty($this->pdf)) {
            $this->generateInvoice();
        }
        $pdf[] = resource_path($this->pdf);
        return Mail::send_mail_template($this->lang_id, 'top_up_confirm', $to, $mail_vars, $pdf);
    }

    /**
     * @return Collection<StatusLog>|null
     */
    public function getStatusLogsAttribute(): ?Collection
    {
        return \App\Models\StatusLog::category(\App\Models\StatusCategory::findByLabel('credits'))->where('order_id', $this->id)->orderBy('id', 'desc')->get();
    }
}
