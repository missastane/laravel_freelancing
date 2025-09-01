<?php

namespace App\Http\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinPalService
{
    protected $merchantId;
    protected $callbackUrl;
    protected $baseUrl;
    public function __construct()
    {
        $this->merchantId = config('services.zarinpal.merchant_id');
        $this->callbackUrl = config('services.zarinpal.callback_url');
        $this->baseUrl = config('services.zarinpal.base_url');
    }

    public function request(int $amount, string $description, ?string $email = null, ?string $mobile = null)
    {
        $response = Http::post($this->baseUrl . '/request.json', [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'callback_url' => $this->callbackUrl,
            'description' => $description,
            'metadata' => [
                'email' => $email,
                'mobile' => $mobile
            ]
        ]);
        // $data = $response->json();
        if($response->successful() && $response->object()->data->code === 100){
            return[
                'success' => true,
                'authority' => $response->object()->data->authority,
                'payment_url' => 'https://www.zarinpal.com/pg/StartPay/'.$response->object()->data->authority,
            ];
        }
       
        return [
            'success' => false,
            'message' => $response->object()->errors->message ?? 'خطا در اتصال به زرین پال',
        ];
    }

    public function verify(string $authority,int $amount):array
    {
        $response = Http::post($this->baseUrl.'/verify.json',[
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'authority' => $authority
        ]);
        if($response->successful() && $response->object()->data->code === 100){
            return[
                'success' => true,
                'ref_id' => $response->object()->data->ref_id,
                'card_pan' => $response->object()->data->card_pan,
                'fee' => $response->object()->data->fee,
            ];
        }
          return [
            'success' => false,
            'message' => $response->object()->errors->message ?? 'تأیید پرداخت ناموفق بود',
        ];
    }

}