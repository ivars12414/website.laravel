<?php

namespace App\Models;

use App\Models\Traits\BelongsToClient;
use Illuminate\Database\Eloquent\Collection;

class Payment extends BaseModel
{

  // тип оплаты (вручную или авто)
  const string STATUS_CHANGE_TYPE_AUTO = 'auto';
  const string STATUS_CHANGE_TYPE_MANUAL = 'manual';
  const array STATUS_CHANGE_TYPES = [
          self::STATUS_CHANGE_TYPE_AUTO => [
                  'title_code' => 'auto',
          ],
          self::STATUS_CHANGE_TYPE_MANUAL => [
                  'title_code' => 'manual',
          ],
  ];

// тип сущности, которую оплачиваем
  const string ORDER_TYPE_ORDER = 'Order';
  const string ORDER_TYPE_TOP_UP = 'CreditsTransaction';
  const array ORDER_TYPES = [
          self::ORDER_TYPE_ORDER => ['title_code' => 'Order', 'table' => 'orders', 'main_table' => 'orders'],
          self::ORDER_TYPE_TOP_UP => ['title_code' => 'Top Up', 'table' => 'credits_transactions', 'main_table' => 'credits_transaction'],
  ];

  // Указываем имя таблицы в базе данных
  protected $table = 'payments';

  use BelongsToClient;

  public $timestamps = false;
  protected $guarded = ['id'];

  // Храним даты как unix-timestamp в БД:
  protected $dateFormat = 'U';

  // Кастим к Carbon:
  protected $casts = [
          'paid_tm' => 'datetime',
          'refund_tm' => 'datetime',
  ];


  public function status(): \Illuminate\Database\Eloquent\Relations\BelongsTo
  {
    return $this->belongsTo(Status::class);
  }

  /**
   * @return Collection<StatusLog>|null
   */
  public function getStatusLogs(): ?Collection
  {
    return \App\Models\StatusLog::category(\App\Models\StatusCategory::findByLabel('payments'))->where('order_id', $this->id)->orderBy('id', 'desc')->get();
  }
}
