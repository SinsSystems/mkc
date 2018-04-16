<?php
    session_start();
    
    $data = file_get_contents("php://input");
    
    $orderid = $_POST['ReturnOid'];
?>
<!DOCTYPE html>
<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>МКЦ › Младински Културен Центар </title>
  <link rel="shortcut icon" href="http://mkc.mk/wp-content/uploads/2013/12/favicon.ico">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="./node_modules/jquery-ui/jquery-ui.min.css">
  <link rel="stylesheet" href="./node_modules/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="./css/bootstrap-changed.css">
  <link rel="stylesheet" href="./css/main.css">


</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="http://mkc.mk/mk">
      <img src="http://mkc.mk/wp-content/uploads/2016/01/MKC_80cCa.png" alt="МКЦ" class="logoImage">

    </a>
    <h3 class="ml-auto">Купи Карти</h3>
  </nav>
    <div class="errorContainer" style="padding-top: 45px;">
      <div class="col-sm-12 text-center">
        <h4>ВАШАТА ТРАНСАКЦИЈА Е УСПЕШНА!</h4>
        <a href="http://mkc.mk/mk/" class="btn btn-secondary btn-lg" role="button">Назад</a>
      </div>
    </div>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="./node_modules/jquery/dist/jquery.min.js"></script>
    <script src="./node_modules/jquery-ui/jquery-ui.min.js"></script>
    <script src="./node_modules/popper.js/dist/umd/popper.min.js"></script>
    <script src="./node_modules/block-ui/jquery.blockUI.js"></script>
    <script src="./node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="./node_modules/validator/validator.js"></script>
    <script src="./js/main.js"></script>
    <script src="./js/charts.js"></script>
    <script src="./js/checking_process.js"></script>
</body>
</html>