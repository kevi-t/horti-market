<?php
    session_start();
    require 'database/db.php';

    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        header("Location: loginpage.php");
        exit;
    }

    $bid = $_SESSION['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name    = htmlspecialchars(trim($_POST['name']));
        $city    = htmlspecialchars(trim($_POST['city']));
        $mobile  = htmlspecialchars(trim($_POST['mobile']));
        $email   = htmlspecialchars(trim($_POST['email']));
        $pincode = htmlspecialchars(trim($_POST['pincode']));
        $addr    = htmlspecialchars(trim($_POST['addr']));

        $cartSql = "SELECT pid FROM mycart WHERE bid = '$bid'";
        $cartResult = mysqli_query($conn, $cartSql);

        $allOk = true;
        while ($cartRow = $cartResult->fetch_assoc()) {
            $pid = (int)$cartRow['pid'];
            $insertSql = "INSERT INTO transaction (bid, pid, name, city, mobile, email, pincode, addr)
                          VALUES ('$bid', '$pid', '$name', '$city', '$mobile', '$email', '$pincode', '$addr')";
            if (!mysqli_query($conn, $insertSql)) {
                $allOk = false;
                break;
            }
        }

        if ($allOk) {
            mysqli_query($conn, "DELETE FROM mycart WHERE bid = '$bid'");
            $_SESSION['message'] = "Order Successfully placed! <br />Thanks for shopping with us!";
            header('Location: authenticate/success.php');
        } else {
            $_SESSION['message'] = "Sorry!<br />Order could not be placed. Please try again.";
            header('Location: authenticate/error.php');
        }
        exit;
    }

    $sql = "SELECT f.pid, f.pimage, f.product, f.pcat, f.price, f.quantity
            FROM mycart c
            JOIN fproduct f ON c.pid = f.pid
            WHERE c.bid = '$bid'";
    $result = mysqli_query($conn, $sql);
    $cartItems = [];
    $subtotal = 0;
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $subtotal += $row['price'];
    }

    if (empty($cartItems)) {
        header("Location: myCart.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Horticulture: Checkout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/footer.css" />
    <link rel="stylesheet" href="assets/css/menu.css" />
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <style>
        .checkout-wrapper {
            padding: 40px 20px 60px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .checkout-wrapper h1 {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 32px;
            color: #1a1a1a;
        }
        /* Shipping form panel */
        .checkout-form-panel {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.10);
            padding: 36px 32px;
        }
        .checkout-form-panel h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #1a1a1a;
            border-bottom: 2px solid #04AA6D;
            padding-bottom: 10px;
        }
        .checkout-form-panel .form-control {
            background: #f0f4f8;
            border: 1px solid #dde3ea;
            border-radius: 5px;
            height: 46px;
            font-size: 14px;
            padding: 10px 14px;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .checkout-form-panel .form-control:focus {
            border-color: #04AA6D;
            background-color: #f7fff9;
            box-shadow: 0 0 0 3px rgba(4,170,109,0.15);
            outline: none;
        }
        .checkout-form-panel .form-group {
            margin-bottom: 18px;
        }
        .checkout-form-panel label {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            margin-bottom: 4px;
            display: block;
        }
        .btn-confirm {
            display: block;
            width: 100%;
            background-color: #04AA6D;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            padding: 13px;
            margin-top: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-confirm:hover {
            background-color: #038a57;
        }
        .btn-mpesa {
            display: block;
            width: 100%;
            text-align: center;
            border: 2px solid #04AA6D;
            color: #04AA6D;
            border-radius: 6px;
            font-weight: 700;
            font-size: 15px;
            padding: 11px;
            margin-top: 12px;
            text-decoration: none;
            transition: background-color 0.2s, color 0.2s;
        }
        .btn-mpesa:hover {
            background-color: #04AA6D;
            color: white;
            text-decoration: none;
        }
        /* Order summary panel */
        .order-summary-panel {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.10);
            padding: 36px 32px;
        }
        .order-summary-panel h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #1a1a1a;
            border-bottom: 2px solid #04AA6D;
            padding-bottom: 10px;
        }
        .order-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .order-item:last-of-type {
            border-bottom: none;
        }
        .order-item img {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .order-item-info {
            flex: 1;
        }
        .order-item-info .item-name {
            font-weight: 600;
            font-size: 14px;
            color: #1a1a1a;
        }
        .order-item-info .item-meta {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }
        .order-item-price {
            font-weight: 700;
            font-size: 14px;
            color: #04AA6D;
            white-space: nowrap;
        }
        .order-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 2px solid #e0e0e0;
        }
        .order-total-row .label {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
        }
        .order-total-row .amount {
            font-size: 22px;
            font-weight: 800;
            color: #04AA6D;
        }
        .back-to-cart {
            display: inline-block;
            margin-bottom: 24px;
            color: #04AA6D;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
        }
        .back-to-cart:hover {
            text-decoration: underline;
            color: #038a57;
        }
    </style>
</head>
<body>

<?php require 'includes/menu2.php'; ?>

<div class="checkout-wrapper">
    <a href="myCart.php" class="back-to-cart">&larr; Back to Cart</a>
    <h1>Checkout</h1>

    <div class="row">

        <!-- Shipping Details Form -->
        <div class="col-md-7">
            <div class="checkout-form-panel">
                <h2>Shipping Details</h2>
                <form action="checkout.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input class="form-control" type="text" name="name" id="name" placeholder="Full Name" required />
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input class="form-control" type="text" name="city" id="city" placeholder="City" required />
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="mobile">Mobile Number</label>
                                <input class="form-control" type="text" name="mobile" id="mobile" placeholder="07XXXXXXXX" required />
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pincode">Pincode</label>
                                <input class="form-control" type="text" name="pincode" id="pincode" placeholder="Pincode" required />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input class="form-control" type="email" name="email" id="email" placeholder="you@example.com" required />
                    </div>
                    <div class="form-group">
                        <label for="addr">Delivery Address</label>
                        <input class="form-control" type="text" name="addr" id="addr" placeholder="Street, Building, Area" required />
                    </div>

                    <button type="submit" class="btn-confirm">Confirm Order</button>
                    <a href="mpesa/index.php" class="btn-mpesa">Pay via Mpesa</a>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-5">
            <div class="order-summary-panel">
                <h2>Order Summary</h2>

                <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <img src="assets/images/productImages/<?= htmlspecialchars($item['pimage']) ?>" alt="<?= htmlspecialchars($item['product']) ?>" />
                    <div class="order-item-info">
                        <div class="item-name"><?= htmlspecialchars($item['product']) ?></div>
                        <div class="item-meta"><?= htmlspecialchars($item['pcat']) ?> &bull; <?= htmlspecialchars($item['quantity']) ?> Kg</div>
                    </div>
                    <div class="order-item-price"><?= number_format($item['price'], 2) ?> Ksh</div>
                </div>
                <?php endforeach; ?>

                <div class="order-total-row">
                    <span class="label">Total</span>
                    <span class="amount"><?= number_format($subtotal, 2) ?> Ksh</span>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
<?php require 'includes/footer.php'; ?>
</html>
