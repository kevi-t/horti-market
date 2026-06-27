<?php
    session_start();
    require 'database/db.php';

    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        header("Location: loginpage.php");
        exit;
    }

    $bid = $_SESSION['id'];

    /* ── Add to cart ── */
    if (isset($_GET['flag'])) {
        $pid = (int)$_GET['pid'];
        mysqli_query($conn, "INSERT INTO mycart (bid,pid) VALUES ('$bid','$pid')");
        header("Location: myCart.php");
        exit;
    }

    /* ── Remove from cart ── */
    if (isset($_GET['remove'])) {
        $pid = (int)$_GET['pid'];
        mysqli_query($conn, "DELETE FROM mycart WHERE bid='$bid' AND pid='$pid'");
        header("Location: myCart.php");
        exit;
    }

    /* ── Place order (form POST) ── */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name    = htmlspecialchars(trim($_POST['name']));
        $city    = htmlspecialchars(trim($_POST['city']));
        $mobile  = htmlspecialchars(trim($_POST['mobile']));
        $email   = htmlspecialchars(trim($_POST['email']));
        $pincode = htmlspecialchars(trim($_POST['pincode']));
        $addr    = htmlspecialchars(trim($_POST['addr']));

        $cartSql    = "SELECT pid FROM mycart WHERE bid='$bid'";
        $cartResult = mysqli_query($conn, $cartSql);
        $allOk      = true;

        while ($cartRow = $cartResult->fetch_assoc()) {
            $pid = (int)$cartRow['pid'];
            $ins = "INSERT INTO transaction (bid,pid,name,city,mobile,email,pincode,addr)
                    VALUES ('$bid','$pid','$name','$city','$mobile','$email','$pincode','$addr')";
            if (!mysqli_query($conn, $ins)) { $allOk = false; break; }
        }

        if ($allOk) {
            mysqli_query($conn, "DELETE FROM mycart WHERE bid='$bid'");
            $_SESSION['message'] = "Order Successfully placed!<br/>Thanks for shopping with us!";
            header('Location: authenticate/success.php');
        } else {
            $_SESSION['message'] = "Sorry!<br/>Order could not be placed. Please try again.";
            header('Location: authenticate/error.php');
        }
        exit;
    }

    /* ── Load cart items ── */
    $sql    = "SELECT f.pid, f.pimage, f.product, f.pcat, f.price, f.quantity
               FROM mycart c JOIN fproduct f ON c.pid = f.pid
               WHERE c.bid = '$bid'";
    $result = mysqli_query($conn, $sql);
    $cartItems = [];
    $subtotal  = 0;
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $subtotal   += $row['price'];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Horticulture: My Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/footer.css" />
    <link rel="stylesheet" href="assets/css/menu.css" />
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <style>
        .cart-page-wrapper {
            padding: 40px 20px 60px;
            max-width: 1140px;
            margin: 0 auto;
        }
        .cart-page-wrapper h1 {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 32px;
            color: #1a1a1a;
        }

        /* ── Cart items panel ── */
        .cart-panel, .checkout-form-panel {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.10);
            padding: 28px 28px;
            margin-bottom: 24px;
        }
        .cart-panel h2, .checkout-form-panel h2 {
            font-size: 17px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 20px;
            color: #1a1a1a;
            border-bottom: 2px solid #04AA6D;
            padding-bottom: 10px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .cart-item:last-child { border-bottom: none; }
        .cart-item img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .cart-item-info { flex: 1; }
        .cart-item-info .item-name {
            font-weight: 600;
            font-size: 14px;
            color: #1a1a1a;
        }
        .cart-item-info .item-meta {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
        }
        .cart-item-price {
            font-weight: 700;
            font-size: 14px;
            color: #04AA6D;
            white-space: nowrap;
            margin-right: 12px;
        }
        .btn-remove {
            background: none;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            color: #c0392b;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: background 0.15s, border-color 0.15s;
        }
        .btn-remove:hover {
            background: #fdf0ef;
            border-color: #c0392b;
            color: #c0392b;
            text-decoration: none;
        }

        .cart-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            padding-top: 14px;
            border-top: 2px solid #e8e8e8;
        }
        .cart-total-row .label {
            font-size: 15px;
            font-weight: 700;
            color: #1a1a1a;
        }
        .cart-total-row .amount {
            font-size: 20px;
            font-weight: 800;
            color: #04AA6D;
        }

        /* ── Shipping form ── */
        .checkout-form-panel .form-control {
            background: #f0f4f8;
            border: 1px solid #dde3ea;
            border-radius: 5px;
            height: 44px;
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
        .checkout-form-panel .form-group { margin-bottom: 16px; }
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
        .btn-confirm:hover { background-color: #038a57; }
        .btn-confirm:disabled {
            background-color: #aaa;
            cursor: not-allowed;
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
            margin-top: 10px;
            text-decoration: none;
            transition: background-color 0.2s, color 0.2s;
        }
        .btn-mpesa:hover {
            background-color: #04AA6D;
            color: white;
            text-decoration: none;
        }

        /* ── Empty state ── */
        .cart-empty {
            text-align: center;
            padding: 60px 20px;
            color: #888;
            font-size: 18px;
        }
        .cart-empty a { color: #04AA6D; font-weight: 600; }
    </style>
</head>
<body>

<?php require 'includes/menu2.php'; ?>

<div class="cart-page-wrapper">
    <h1>My Cart</h1>

    <?php if (empty($cartItems)): ?>

    <div class="cart-empty">
        Your cart is empty. <a href="home.php">Continue shopping</a>
    </div>

    <?php else: ?>

    <div class="row">

        <!-- Left: Cart Items -->
        <div class="col-md-7">
            <div class="cart-panel">
                <h2>Cart Items (<?= count($cartItems) ?>)</h2>

                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <a href="products/review.php?pid=<?= $item['pid'] ?>">
                        <img src="assets/images/productImages/<?= htmlspecialchars($item['pimage']) ?>"
                             alt="<?= htmlspecialchars($item['product']) ?>" />
                    </a>
                    <div class="cart-item-info">
                        <div class="item-name"><?= htmlspecialchars($item['product']) ?></div>
                        <div class="item-meta">
                            <?= htmlspecialchars($item['pcat']) ?> &bull; <?= htmlspecialchars($item['quantity']) ?> Kg
                        </div>
                    </div>
                    <span class="cart-item-price"><?= number_format($item['price'], 2) ?> Ksh</span>
                    <a href="myCart.php?remove=1&pid=<?= $item['pid'] ?>" class="btn-remove">Remove</a>
                </div>
                <?php endforeach; ?>

                <div class="cart-total-row">
                    <span class="label">Total</span>
                    <span class="amount"><?= number_format($subtotal, 2) ?> Ksh</span>
                </div>
            </div>
        </div>

        <!-- Right: Shipping Details -->
        <div class="col-md-5">
            <div class="checkout-form-panel">
                <h2>Shipping Details</h2>
                <form action="myCart.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input class="form-control" type="text" name="name" id="name" placeholder="Full Name" required />
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input class="form-control" type="text" name="city" id="city" placeholder="City" required />
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label for="mobile">Mobile</label>
                                <input class="form-control" type="text" name="mobile" id="mobile" placeholder="07XXXXXXXX" required />
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="form-group">
                                <label for="pincode">Pincode</label>
                                <input class="form-control" type="text" name="pincode" id="pincode" placeholder="Pincode" required />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
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

    </div><!-- .row -->

    <?php endif; ?>

</div><!-- .cart-page-wrapper -->

</body>
<?php require 'includes/footer.php'; ?>
</html>
