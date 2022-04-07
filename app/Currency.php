<?php
namespace App;

class Currency {
    private string $url = 'https://developers.paysera.com/tasks/api/currency-exchange-rates';
    private array $rates;

    /**
     * @throws \JsonException
     */
    function __construct($currency){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        $result = curl_exec($ch);
        curl_close($ch);

        $obj = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        $this->rates = $obj['rates'];
    }

    public function getRates(): array
    {
        return $this->rates;
    }
}