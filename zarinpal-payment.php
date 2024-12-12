<?php

/**
 * ZarinPal Payment Class
 *
 * This class provides a simple and secure way to integrate with the ZarinPal payment gateway.
 * It supports both sandbox and live environments, and provides methods for requesting payments,
 * verifying payments, and handling different currency units.
 *
 * @author Mahdi Jafarzadeh
 * @version 3.0
 */
class ZarinPalPayment
{
    /**
     * @var string The merchant ID provided by ZarinPal.
     */
    private $merchantId;

    /**
     * @var bool Whether to use the sandbox environment or the live environment.
     */
    private $sandbox;

    /**
     * @var string The base URL for the ZarinPal API.
     */
    private $baseUrl;

    /**
     * @var string The redirect URL for ZarinPal payments.
     */
    private $redirectUrl;

    /**
     * Constructor for the ZarinPal class.
     *
     * @param string $merchantId The merchant ID provided by ZarinPal.
     * @param bool $sandbox Whether to use the sandbox environment or the live environment.
     */
    public function __construct($merchantId, $sandbox = false)
    {
        $this->merchantId = $merchantId;
        $this->sandbox = $sandbox;
        $this->baseUrl = $sandbox ? 'https://sandbox.zarinpal.com/pg/v4/payment/' : 'https://api.zarinpal.com/pg/v4/payment/';
        $this->redirectUrl = $sandbox ? 'https://sandbox.zarinpal.com/pg/StartPay/' : 'https://www.zarinpal.com/pg/StartPay/';
    }

    /**
     * Sends a payment request to ZarinPal.
     *
     * @param int $amount The amount of the payment.
     * @param string $currency The currency of the payment (IRR, IRT, IRHR, IRHT). Default is IRR.
     * @param string $callbackUrl The URL to redirect to after the payment is completed.
     * @param string $description A description of the payment.
     * @param string $email The email address of the customer.
     * @param string $mobile The mobile number of the customer.
     * @return array An array containing the status of the request and other information.
     */
    public function request($amount, $currency = 'IRR', $callbackUrl, $description, $email = null, $mobile = null)
    {
        // Convert the amount to rials based on the currency
        switch ($currency) {
            case 'IRT':
                $amount *= 10;
                break;
            case 'IRHR':
                $amount *= 1000;
                break;
            case 'IRHT':
                $amount *= 10000;
                break;
            // Default is IRR, no conversion needed
        }

        $data = [
            'merchant_id'  => $this->merchantId,
            'amount'       => $amount,
            'callback_url' => $callbackUrl,
            'description'  => $description,
            'metadata'     => [
                'email'  => $email,
                'mobile' => $mobile,
            ],
        ];

        $response = $this->sendRequest('request.json', $data);

        if (isset($response['data']['code']) && $response['data']['code'] == 100) {
            return [
                'status'    => $response['data']['code'],
                'message'   => $this->error_message($response['data']['code']),
                'authority' => $response['data']['authority'],
                'url'       => $this->redirectUrl . $response['data']['authority'],
            ];
        } else {
            return [
                'status'  => $response['errors']['code'] ?? 0,
                'message' => $response['errors']['message'] ?? 'خطای ناشناخته',
            ];
        }
    }

    /**
     * Verifies a payment against ZarinPal.
     *
     * @param int $amount The amount of the payment in rials.
     * @return array An array containing the status of the verification and other information.
     */
    public function verify($amount)
    {
        $authority = $_GET['Authority'] ?? null;
        if (!$authority) {
            return [
                'status'  => -1,
                'message' => 'Authority parameter is missing.',
            ];
        }

        $data = [
            'merchant_id' => $this->merchantId,
            'authority'   => $authority,
            'amount'      => $amount,
        ];

        $response = $this->sendRequest('verify.json', $data);

        if (isset($response['data']['code']) && ($response['data']['code'] == 100 || $response['data']['code'] == 101)) {
            return [
                'status'  => $response['data']['code'],
                'message' => $this->error_message($response['data']['code']),
                'ref_id'  => $response['data']['ref_id'],
            ];
        } else {
            return [
                'status'  => $response['errors']['code'] ?? 0,
                'message' => $response['errors']['message'] ?? 'خطای ناشناخته',
            ];
        }
    }

    /**
     * Sends a request to the ZarinPal API.
     *
     * @param string $endpoint The API endpoint to send the request to.
     * @param array $data The data to send with the request.
     * @return array The response from the ZarinPal API.
     */
    private function sendRequest($endpoint, $data)
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data)),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

/**
     * Returns the error message for the given status code.
     *
     * @param int $code The status code returned by ZarinPal.
     * @return string The error message corresponding to the status code.
     */
    private function error_message($code)
    {
        $errors = [
            "-9"  => "خطای اعتبار سنجی",
            "-10" => "IP و یا مرچنت کد پذیرنده صحیح نیست",
            "-11" => "مرچنت کد فعال نیست، پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.",
            "-12" => "تلاش بیش از دفعات مجاز در یک بازه زمانی کوتاه به امور مشتریان زرین پال اطلاع دهید.",
            "-15" => "درگاه پرداخت به حالت تعلیق در آمده است، پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.",
            "-16" => "سطح تایید پذیرنده پایین تر از سطح نقره ای است.",
            "-17" => "محدودیت پذیرنده در سطح آبی.",
            "100" => "عملیات موفق.",
            "-30" => "پذیرنده اجازه دسترسی به سرویس تسویه اشتراکی شناور را ندارد.",
            "-31" => "حساب بانکی تسویه را به پنل اضافه کنید. مقادیر وارد شده برای تسهیم درست نیست. پذیرنده جهت استفاده از خدمات سرویس تسویه اشتراکی شناور، باید حساب بانکی معتبری به پنل کاربری خود اضافه نماید.",
            "-32" => "مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.",
            "-33" => "درصدهای وارد شده صحیح نیست.",
            "-34" => "مبلغ وارد شده از مبلغ کل تراکنش بیشتر است.",
            "-35" => "تعداد افراد دریافت کننده تسهیم بیش از حد مجاز است.",
            "-36" => "حداقل مبلغ جهت تسهیم باید ۱۰۰۰۰ ریال باشد.",
            "-37" => "یک یا چند شماره شبای وارد شده برای تسهیم از سمت بانک غیر فعال است.",
            "-38" => "خطا٬عدم تعریف صحیح شبا٬لطفا دقایقی دیگر تلاش کنید.",
            "-39" => "خطایی رخ داده است به امور مشتریان زرین پال اطلاع دهید.",
            "-40" => "Invalid extra params, expire_in is not valid.",
            "-41" => "حداکثر مبلغ پرداختی ۱۰۰ میلیون تومان است.",
            "-50" => "مبلغ پرداخت شده با مقدار مبلغ ارسالی در متد وریفای متفاوت است.",
            "-51" => "پرداخت ناموفق.",
            "-52" => "خطای غیر منتظره‌ای رخ داده است. پذیرنده مشکل خود را به امور مشتریان زرین‌پال ارجاع دهد.",
            "-53" => "پرداخت متعلق به این مرچنت کد نیست.",
            "-54" => "اتوریتی نامعتبر است.",
            "-55" => "تراکنش مورد نظر یافت نشد.",
            "-60" => "امکان ریورس کردن تراکنش با بانک وجود ندارد.",
            "-61" => "تراکنش موفق نیست یا قبلا ریورس شده است.",
            "-62" => "آی پی درگاه ست نشده است.",
            "-63" => "حداکثر زمان (۳۰ دقیقه) برای ریورس کردن این تراکنش منقضی شده است.",
            "101" => "تراکنش وریفای شده است.",
        ];

        return $errors[$code] ?? "خطای نامشخص هنگام اتصال به درگاه زرین پال";
    }
}

?>
