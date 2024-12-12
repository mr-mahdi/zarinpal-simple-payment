<?php

require_once 'ZarinPalPayment.php';

$zarinpal = new ZarinPalPayment('YOUR_MERCHANT_ID', true); // Use `true` for sandbox mode

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $currency = $_POST['currency'];
    $description = $_POST['description'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];

    $result = $zarinpal->request($amount, $currency, 'YOUR_CALLBACK_URL', $description, $email, $mobile);

    if ($result['status'] == 100) {
        header('Location: ' . $result['url']);
        exit;
    } else {
        $errorMessage = $result['message'];
    }
}

?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZarinPal Payment Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        پرداخت با زرین‌پال
                    </div>
                    <div class="card-body">
                        <?php if (isset($errorMessage)): ?>
                            <div class="alert alert-danger">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label for="amount" class="form-label">مبلغ:</label>
                                <input type="number" class="form-control" id="amount" name="amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="currency" class="form-label">واحد پول:</label>
                                <select class="form-select" id="currency" name="currency">
                                    <option value="IRR">ریال</option>
                                    <option value="IRT">تومان</option>
                                    <option value="IRHR">هزار ریال</option>
                                    <option value="IRHT">هزار تومان</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">توضیحات:</label>
                                <input type="text" class="form-control" id="description" name="description">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">ایمیل:</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">موبایل:</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile">
                            </div>
                            <button type="submit" class="btn btn-primary">پرداخت</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
