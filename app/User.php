<?php
namespace App;

class User {
    public const DATE = 'date';
    public const ID = 'id';
    public const TYPE = 'type';
    public const OPERATION_TYPE = 'operation_type';
    public const OPERATION_AMOUNT = 'operation_amount';
    public const OPERATION_CURRENCY = 'operation_currency';

    public const OPERATION_TYPE_DEPOSIT = 'deposit';
    public const OPERATION_TYPE_WITHDRAW = 'withdraw';

    public const USER_TYPE_BUSINESS = 'business';
    public const USER_TYPE_PRIVATE = 'private';

    public const USER_LIMIT = 1000;
    public const CURRENCY = "EUR";

    public const COMMISSIONS = [
        self::OPERATION_TYPE_DEPOSIT => 0.03,
        self::OPERATION_TYPE_WITHDRAW => [
            self::USER_TYPE_BUSINESS => 0.5,
            self::USER_TYPE_PRIVATE => 0.3,
        ],
    ];

    private array $data;
    private array $rates;

    function __construct($data){
        $currency = new Currency(self::CURRENCY);
        $this->rates = $currency->getRates();

        foreach ($data as $one) {
            $this->data[] = [
                self::DATE                  => $one[0],
                self::ID                    => $one[1],
                self::TYPE                  => $one[2],
                self::OPERATION_TYPE        => $one[3],
                self::OPERATION_AMOUNT      => $one[4],
                self::OPERATION_CURRENCY    => $one[5],
            ];
        }
    }

    public function calculate(): array
    {
        $commissionFee = [];
        $usedOperation = [];
        foreach ($this->data as $one) {
            $amount = $one[self::OPERATION_AMOUNT];
            $operationType = $one[self::OPERATION_TYPE];
            $type = $one[self::TYPE];
            $currency = $one[self::OPERATION_CURRENCY];
            $date = $one[self::DATE];
            $user = $one[self::ID];

            $commission = self::COMMISSIONS[$operationType];
            if(is_array($commission)) {
                $commission = $commission[$type];
            }

            if($operationType === self::OPERATION_TYPE_WITHDRAW && $type === self::USER_TYPE_PRIVATE) {
                $rate = $this->rates[$currency];
                $dateTime = new \DateTime($date);
                $week = $dateTime->format("oW");
                if(!empty($usedOperation[$week][$user])){
                    $usedOperation[$week][$user]['sum'] += $amount / $rate;
                    $usedOperation[$week][$user]['count'] += 1;
                } else {
                    $usedOperation[$week][$user] = [
                        'sum' => $amount / $rate,
                        'used' => 0,
                        'count' => 1,
                    ];
                }

                if(!$usedOperation[$week][$user]['used'] && $usedOperation[$week][$user]['count'] <= 3) {
                    if($usedOperation[$week][$user]['sum'] >= self::USER_LIMIT) {
                        $usedOperation[$week][$user]['used'] = 1;
                        $amount -= self::USER_LIMIT * $rate;
                    } else {
                        $amount -= $usedOperation[$week][$user]['sum'] * $rate;
                    }
                }

                if($amount < 0) {
                    $amount = 0;
                }
            }

            $commissionFee[] = round($amount * $commission / 100, 2);
        }

        return $commissionFee;
    }
}