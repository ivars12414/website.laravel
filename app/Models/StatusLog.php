<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLog extends BaseModel
{
  protected $table = 'statuses_log';

  protected $guarded = ['id'];

  public $timestamps = false;

  protected $casts = [
          'add_tm' => 'datetime',
  ];

  /**
   * @throws \Exception
   */
  public static function logChange(int $orderId, Status $status, int $user_id, int $source, string $reason_hash = '', array $add_data = []): void
  {
    self::create([
            'order_id' => $orderId,
            'status_id' => $status->id,
            'add_tm' => time(),
            'user_id' => $user_id,
            'source' => $source,
            'reason_hash' => $reason_hash,
            'add_data' => json_encode($add_data, JSON_UNESCAPED_UNICODE),
    ]);
  }

  public function status(): BelongsTo
  {
    return $this->belongsTo(Status::class);
  }

  public function scopeCategory($query, StatusCategory|int $category)
  {
    $categoryId = $category instanceof StatusCategory
            ? $category->getKey()
            : $category;

    return $query->whereHas('status.category', fn($q) => $q->whereKey($categoryId));
  }
}
