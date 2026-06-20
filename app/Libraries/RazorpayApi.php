<?php

namespace App\Libraries;

class RazorpayApi
{
    private $key;
    private $secret;
    private $apiUrl = 'https://api.razorpay.com/v1/';

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    public function createOrder($data)
    {
        $url = $this->apiUrl . 'orders';
        return $this->sendRequest($url, $data);
    }

    public function verifyPaymentSignature($attributes)
    {
        $expectedSignature = hash_hmac(
            'sha256',
            $attributes['razorpay_order_id'] . '|' . $attributes['razorpay_payment_id'],
            $this->secret
        );

        if ($expectedSignature !== $attributes['razorpay_signature']) {
            throw new \Exception('Invalid signature');
        }

        return true;
    }

    private function sendRequest($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->key . ":" . $this->secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            throw new \Exception(curl_error($ch));
        }
        curl_close($ch);
        return json_decode($response, true);
    }
}
