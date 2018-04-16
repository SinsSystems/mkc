<?php
    session_start();
    
    require_once('log.php');
    
    $log = new log();
    $data = file_get_contents("php://input");
    
    $log->log_message($data);

    $orderid = $_GET['order_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Билети - Македонски Народен Театар</title>
    <!-- Bootstrap -->
    <link href="components/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="components/font_awesome/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/media_size_styles.css">
    <style>
    body, html {
        background-color: #fff;
    }
    </style>
</head>

<body>
<?php echo "<script> var roid = '" . $orderid . "'</script>"; ?>


<div class="container-fluid">
<div class="container" id="success-pay">
    <div id="success-display">
        <div class="panel panel-default" id="panelPrint" style="margin-top: 20px;">
             <div class="panel-body" id="">
                <div id="ticketInformations">
                <h3><u>ПОТВРДА ЗА КУПЕНИ КАРТИ - ЕЛЕКТРОНСКА КАРТА</u></h3><br>
                    <h4><strong><span id="naslov"></span></strong></h4>
                    <div><span id="autorOfPlay"></span></div>
                    <div><span id="directorOfPlay"></span></div>
                    <div><span id="scenaOfPlay"></span></div>
                    <hr>
                    <div class="row" id="ticketRow"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<form id="infoForm" action="print_ticket_pdf.php" method="post" target="_blank">
    <input type="hidden" name="printData" id="printData" value="">
</form>



<!-- jQuery  -->
<script src="js/jQuery.js"></script>
<!-- Bootstrap -->
<script src="components/bootstrap/js/bootstrap.min.js"></script>
<script src="js/jquery-qrcode.min.js"></script>
<script src="js/barcode.min.js"></script>
<script src="components/blockUI/blockUI.js"></script>

<script src="js/templates.js"></script>
<script src="js/print_tickets.js"></script>
</body>

</html>
