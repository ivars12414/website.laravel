<?php

namespace App\Models;

use App\Models\InfoBlocks\ClientLevel;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Client extends BaseModel implements Authenticatable
{
    // Указываем имя таблицы в базе данных
    protected $table = 'cl_clients';

    use SoftDeletes;
    use AuthenticatableTrait;
    use Notifiable;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'birthday' => 'date:Y-m-d',
        'password' => 'hashed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    const string CREATED_AT = 'reg_tm';

    public function transactions()
    {
        return $this->hasMany(CreditsTransaction::class);
    }

    public function balanceLogs()
    {
        return $this->hasMany(BalanceLog::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    /**
     * @throws \Exception
     */
    public function changeBalance(int|float $credits, int $log_type, int $type_id): static
    {
        if ($credits < 0 && $this->balance + $credits < 0) {
            // недостаточно средств
            throw new \Exception('Not enough balance');
        }

        $this->balance += $credits;
        $this->save();

        $this->balanceLogs()->create([
            'type' => $log_type,
            'type_id' => $type_id,
            'operation' => $credits < 0 ? BalanceLog::OPERATION_WITHDRAW : BalanceLog::OPERATION_DEPOSIT,
            'credits' => $credits,
        ]);

        return $this;
    }

    public function addNeurons(int $neurons): static
    {
        $this->neurons += $neurons;
        $this->save();
        return $this;
    }

    public function getMinutesPrice(int|float $minutes): float
    {
        return (float)($minutes * $this->getCurrentLevel()->price_per_minute);
    }

    public function getMinutesAvailable(int|float|null $credits = null): float
    {
        $credits ??= $this->balance;
        return (float)($credits / $this->getCurrentLevel()->price_per_minute);
    }

    /**
     * @throws \Exception
     */
    public function checkDailyBonus(): static
    {
        if (!isConfig('bonus_enabled')) return $this;

        $minutes_required = getConfig('bonus_minutes') > 0 ? getConfig('bonus_minutes') : 30;
        $minutes_reward = getConfig('bonus_reward') > 0 ? getConfig('bonus_reward') : 5;

        $minutes_played = (float)$this->gameResults()
                ->whereBetween('created_at', [
                    strtotime('00:00:00'),
                    strtotime('23:59:59')
                ])
                ->sum('time') / 60;

        if ($minutes_played >= $minutes_required) {
            if (!$this->balanceLogs()
                ->where('type', BalanceLog::TYPE_DAILY_BONUS)
                ->whereBetween('created_at', [
                    strtotime('00:00:00'),
                    strtotime('23:59:59')
                ])->exists()
            ) {
                $this->changeBalance(
                    credits: $this->getMinutesPrice($minutes_reward),
                    log_type: BalanceLog::TYPE_DAILY_BONUS,
                    type_id: 0,
                );
            }
        }

        return $this;
    }

    public function getFullName(): string
    {
        return trim("$this->name $this->surname");
    }
}
