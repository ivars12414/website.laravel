<?php

namespace App\Models;

class BalanceLog extends BaseModel
{
  use \App\Models\Traits\BelongsToClient;

  const int TYPE_CREDIT_TRANSACTION = 1;
  const int TYPE_GAME = 2;
  const int TYPE_DAILY_BONUS = 3;
  const int TYPE_ORDER = 4;
  const int TYPE_ADMIN = 5;
  const array TYPES = [
          self::TYPE_CREDIT_TRANSACTION => [
                  'title_code' => 'Balance Top Up',
          ],
//          self::TYPE_GAME => [
//                  'title_code' => 'Game played',
//          ],
//          self::TYPE_DAILY_BONUS => [
//                  'title_code' => 'Daily bonus',
//          ],
          self::TYPE_ORDER => [
                  'title_code' => 'Order',
          ],
          self::TYPE_ADMIN => [
                  'title_code' => 'Gift by admin',
          ],
  ];

  const int OPERATION_DEPOSIT = 1;
  const int OPERATION_WITHDRAW = 2;
  const array OPERATIONS = [
          self::OPERATION_DEPOSIT => [
                  'title_code' => 'Deposited',
                  'color' => '#d0ffd0',
          ],
          self::OPERATION_WITHDRAW => [
                  'title_code' => 'Withdrawn',
                  'color' => '#ffd0d0',
          ],
  ];

  protected $table = 'clients_balance_log';

  protected $guarded = ['id'];

  public static function saveLog(int $client_id, int $type, int $type_id, int $operation, int|float $credits): void
  {
    self::create([
            'client_id' => $client_id,
            'type' => $type,
            'type_id' => $type_id,
            'operation' => $operation,
            'credits' => $credits,
    ]);
  }
}
