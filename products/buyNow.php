<?php
	session_start();
	require '../database/db.php';
    $pid = $_GET['pid'];
    if($_SERVER['REQUEST_METHOD'] == "POST")
    {
        $name = $_POST['name'];
        $city = $_POST['city'];
        $mobile = $_POST['mobile'];
        $email = $_POST['email'];
        $pincode = $_POST['pincode'];
        $addr = $_POST['addr'];
        $bid = $_SESSION['id'];

        $sql = "INSERT INTO transaction (bid, pid, name, city, mobile, email, pincode, addr)
                VALUES ('$bid', '$pid', '$name', '$city', '$mobile', '$email', '$pincode', '$addr')";
        $result = mysqli_query($conn, $sql);
        if($result)
        {
            $_SESSION['message'] = "Order Succesfully placed! <br /> Thanks for shopping with us!!!";
            header('Location: ../authenticate/success.php');
        }
        else {
           // echo $result->mysqli_error();
            $_SESSION['message'] = "Sorry!<br />Order was not placed";
            header('Location: ../authenticate/error.php');
        }
    }
?>


<!DOCTYPE html>
<html>
<head>
	<title>Horticulture: Transaction</title>
	<meta lang="eng">
	<meta charset="UTF-8">
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.css">
    <link rel="stylesheet" href="../assets/css/footer.css" />
    <link rel="stylesheet" href="../assets/css/menu.css" />
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script> 
</head>
<body>

    <?php
        require 'menu.php';
    ?>
<div class="bg-img">
 <form action="buyNow.php" method='POST' class="auth-card">
        <h2>Transaction Details</h2>
        <div class="form-group">
            <input class="form-control" type="text" name="name" id="name" value="" placeholder="Name" required/>
        </div>
        <div class="form-group">
            <input class="form-control" type="text" name="city" id="city" value="" placeholder="City" required/>
        </div>
        <div class="form-group">
            <input class="form-control" type="text" name="mobile" id="mobile" value="" placeholder="Mobile Number" required/>
        </div>
        <div class="form-group">
            <input class="form-control" type="email" name="email" id="email" value="" placeholder="Email" required/>
        </div>
        <div class="form-group">
            <input class="form-control" type="text" name="pincode" id="pincode" value="" placeholder="Pincode" required/>
        </div>
        <div class="form-group">
            <input class="form-control" type="text" name="addr" id="addr" value="" placeholder="Address" required/>
        </div>
        <p>
          <input type="submit" value="Confirm Order" class="btn-submit" style="cursor:pointer;" />
          <a href="../mpesa/index.php">Mpesa Payment</a>
        </p>

</form>
</div>   

</body>
<?php	require '../includes/footer.php';?>
</html>