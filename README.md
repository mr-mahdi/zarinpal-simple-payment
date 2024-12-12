## ZarinPal Payment Class

This class provides a simple and secure way to integrate with the ZarinPal payment gateway in your PHP projects.

### Features

*   Supports both sandbox and live environments.
*   Handles payment requests and verifications.
*   Manages different currency units (IRR, IRT, IRHR, IRHT).
*   Provides comprehensive error messages in Persian.

### Installation

1.  Download the `ZarinPalPayment.php` file.
2.  Include the file in your project:

```php
require_once 'ZarinPalPayment.php';
```

### Usage

1.  **Create an instance of the class:**

```php
$zarinpal = new ZarinPalPayment('YOUR_MERCHANT_ID', true); // Use `true` for sandbox mode
```

2.  **Request a payment:**

```php
$result = $zarinpal->request(
    1000, // Amount
    'IRT', // Currency (optional, defaults to IRR)
    'YOUR_CALLBACK_URL', // Callback URL
    'Payment for order #123', // Description
    'user@example.com', // Email (optional)
    '09123456789' // Mobile (optional)
);
```

3.  **Handle the payment request result:**

```php
if ($result['status'] == 100) {
    // Redirect the user to the payment gateway
    header('Location: ' . $result['url']);
} else {
    // Display an error message
    echo $result['message'];
}
```

4.  **Verify the payment:**

```php
$result = $zarinpal->verify(1000); // Amount in rials

if ($result['status'] == 100) {
    // Payment was successful
    $refId = $result['ref_id'];
    // ... process the payment
} else {
    // Payment failed
    echo $result['message'];
}
```

### Error Handling

The `error_message()` method provides detailed error messages in Persian for all ZarinPal status codes.

### Contributing

Feel free to submit pull requests or report issues on GitHub.

### License

This code is licensed under the MIT License.
