<?php
session_start();

session_regenerate_id();

require_once 'include/jwt.php';

class authtoken
{
    public $ip;
    public $sessionid;
    public $token;

    public function authtoken($ip, $sessionid, $token)
    {
        $this->ip = $ip;
        $this->sessionid = $sessionid;
        $this->token = $token;
    }
}

// initialize session
$sessionid = session_id();
$ip = $_SERVER['REMOTE_ADDR'];
if (!$ip) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (!$ip) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
}
$token = hash("sha512", $sessionid . $ip);

if ($_SERVER['SERVER_ADDR'] == '') {
    $key = base64_encode('77.28.13.93');
} else {
    $key = base64_encode($_SERVER['SERVER_ADDR']);
}

$jwt = JWT::encode(new authtoken($ip, $sessionid, $token), $key);

// REQUEST
$curl_handle = curl_init();

$options = array(
    CURLOPT_URL => 'http://sins.dyndns.info/mkc/auth.php?auth=' . $jwt,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPGET => true,
    CURLOPT_CONNECTTIMEOUT => 10,
);
curl_setopt_array($curl_handle, $options);

$data = curl_exec($curl_handle);

$res = (!curl_error($curl_handle));

curl_close($curl_handle);

$token = '';

if ($res) {

    $data = json_decode($data);

    if ($data->res) {
        $token = $data->message;
    }
}
?>
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
  <?php echo '<script> var id = '.$_GET['id'].'</script>'; ?>
  <?php echo '<script> var token = "'.$token .'"</script>'; ?>
  <nav class="navbar navbar-expand-lg navbar-light">
    <a class="navbar-brand" href="http://mkc.mk/mk">
      <img src="http://mkc.mk/wp-content/uploads/2016/01/MKC_80cCa.png" alt="МКЦ" class="logoImage">
    </a>
    <h3 class="ml-auto">Купи Карти</h3>
  </nav>
  <div class="container-fluid mainContentContainer" style="display: none">
    <div class="row">
      <div class="col-sm-12 col-md-4">
        <div class="card">
          <img class="card-img-top" src="http://mkc.mk/wp-content/uploads/2017/08/taksirat-promo.jpg" alt="Event Image" id="eventImage">
          <div class="card-body">
            <h5 class="card-title" id="eventName"></h5>
            <h6 class="card-subtitle mb-2 text-muted" id="eventDirector"></h6>
            <h6 class="card-subtitle mb-2 text-muted eventPlace"></h6>
            <p class="card-text">Цена:
              <span id="eventPrice"></span>ден</p>
          </div>
        </div>
      </div>
      <div class="col-sm-12">
        <form data-toggle="validator" role="form" method="post" action="https://entegrasyon.asseco-see.com.tr/fim/est3Dgate" id="payFormSmall">
          <div class="card" id="payTicketsCard" style="display: none">
            <h3 class="card-header">Информации за купување</h3>
            <div class="card-body">
              <div class="form-group">
                <label for="imeForma" class="control-label">Име и презиме</label>
                <input type="text" class="form-control input-sm" id="imeForma" data-error="Задожително поле" autocomplete="off" required>
                <div class="help-block with-errors text-danger"></div>
              </div>
              <div class="form-group">
                <label for="telefonForma" class="control-label">Телефон</label>
                <input type="text" class="form-control input-sm" id="telefonForma" data-error="Задожително поле (најмалку 9 карактери)" data-minlength="9"
                  autocomplete="off" required>
                <div class="help-block with-errors text-danger"></div>
              </div>
              <div class="form-group">
                <label for="emailForma" class="control-label">Електронска пошта</label>
                <input type="email" class="form-control input-sm" id="emailForma" data-error="Електронската пошта е невалидна" autocomplete="off"
                  required>
                <div class="help-block with-errors text-danger"></div>
              </div>
              <div class="form-group">
                <label for="emailForma2" class="control-label">Внесете ја повторно електронската пошта</label>
                <input type="email" class="form-control input-sm" id="emailForma2" data-error=" " data-match="#emailForma" data-match-error="Грешка. Покушајте поново"
                  autocomplete="off" required>
                <div class="help-block with-errors text-danger"></div>
              </div>
              <input type="hidden" name="clientid" id="clientid" value="" />
              <input type="hidden" name="oid" id="oid" value="" />
              <input type="hidden" name="amount" id="amount" value="" />
              <input type="hidden" name="okUrl" id="okUrl" value="http://sins.dyndns.info/mkc/success.php" />
              <input type="hidden" name="failUrl" id="failUrl" value="http://sins.dyndns.info/mkc/fail.php" />
              <input type="hidden" name="trantype" id="trantype" value="" />
              <input type="hidden" name="instalment" id="instalment" value="" />
              <input type="hidden" name="rnd" value="" id="rnd" />
              <input type="hidden" name="storekey" value="" id="storekey" />
              <input type="hidden" name="hash" value="" id="hash" />
              <input type="hidden" name="storetype" value="" id="storetype" />
              <input type="hidden" name="lang" value="" id="lang" />
              <input type="hidden" name="currency" value="" id="currency" />
              <input type="hidden" name="refreshtime" value="5" />
            </div>
            <hr style="margin: 0px;">
            <div class="card-body">
              <h5 class="card-title">Резервирано:</h5>
              <ul class="list-unstyled orderedTickets">

              </ul>
              <hr>
              <h6>Вкупен број на билети:
                <strong>
                  <span class="totalTicketNum"></span>
                </strong>
              </h6>
              <h6>Вкупно за плаќање:
                <strong>
                  <span class="totalSum"></span>
                </strong> ден</h6>
              <hr>
              <button type="button" class="btn btn btn-outline-info float-left" id="cancelPaying">Назад</button>
              <button type="submit" class="btn btn-outline-success float-right" onclick="event.preventDefault(); payRequest('S');">Плати</button>
            </div>
          </div>
        </form>
        <div class="card" id="selectTicketsCard" style="display: block">
          <div class="card-header">
            <h4>Резервирај
              <button class="btn btn-link btn-sm pull-right trigerCollapse" type="button" data-toggle="collapse" data-target="#seatChartPreviewCollapse"
                aria-expanded="false" style="padding-right: 0px;">
                Распоред на седење &nbsp;
                <span>
                  <i class="fa fa-caret-down"></i>
                </span>
              </button>
              <button class="btn btn-link pull-right trigerModal" type="button" data-toggle="modal" data-target="#seatChartPreviewDialog"
                aria-expanded="false" style="padding-right: 0px;">
                Распоред на седење
              </button>
            </h4>
          </div>
          <div class="collapse" id="seatChartPreviewCollapse">
            <img src="./images/skica.png" class="img-fluid seatChartPreviewImage" alt="Кино Фросина">
          </div>
          <div class="card-body byeTicketsCard">
            <form>
              <div class="form-row">
                <div class="form-group col-sm-12 col-lg-3 selectTicketsFormGroup">
                  <label for="eventDates">Избери Датум</label>
                  <select name="" id="eventDates" class="form-control">

                  </select>
                </div>
                <div class="form-group col-sm-12 col-lg-3 selectTicketsFormGroup">
                  <label for="eventTime">Избери Време</label>
                  <select name="" id="eventTime" class="form-control">

                  </select>
                </div>
                <div class="form-group col-sm-12 col-lg-3 selectTicketsFormGroup" id="mainAreaSelect" style="display: none;">
                  <label for="" class="form-label">Избери Област</label>
                  <div id="selectBlockSection">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                      <label class="btn btn-info optionsGAreaBtn" id="balkonBB">
                        <input type="radio" name="options" id="option1" autocomplete="off" onchange="checkedArea(1)"> Балкон
                      </label>
                      <label class="btn btn-info optionsGAreaBtn" id="parterBB">
                        <input type="radio" name="options" id="option2" autocomplete="off" onchange="checkedArea(0)"> Партер
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group col-sm-12 col-lg-3" id="blockSelect" style="display: none;">
                  <label for="" class="form-label">Избери Блок</label>
                  <div id="selectAreaButtonsDiv">
                    
                  </div>
                </div>
                <div id="noAreaSelect" class="form-group form-row col-sm-12 col-lg-3" style="display: none; margin-left: 0px;">
                      <label for="" class="col-sm-12">Избери Количина</label>
                      <select name="" id="selectTicketNr" class="form-control col-sm-8 col-8" style="max-width: 150px;">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                      </select>
                      <div class="col-sm-4 col-4">
                      <button type="button" class="btn btn-outline-success" onclick="byNonNumberedTickets()">Купи</button>
        </div>
                </div>
                <div class="form-group col-sm-12 col-lg-3 selectTicketsFormGroup" id="blockMileniumSelect" style="display: none;">
                  <label for="" class="form-label">Избери Блок</label>
                  <div id="selectAreaButtonsDiv">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                      <label class="btn btn-info optionsAreaBtn">
                        <input type="radio" class="optionsArea" name="optionsAreaM" id="option1" autocomplete="off"> А
                      </label>
                      <label class="btn btn-info optionsAreaBtn">
                        <input type="radio" class="optionsArea" name="optionsAreaM" id="option2" autocomplete="off"> Б
                      </label>
                      <label class="btn btn-info optionsAreaBtn">
                        <input type="radio" class="optionsArea" name="optionsAreaM" id="option3" autocomplete="off"> Ц
                      </label>
                      <label class="btn btn-info optionsAreaBtn">
                        <input type="radio" class="optionsArea" name="optionsAreaM" id="option4" autocomplete="off"> Д
                      </label>
                      <label class="btn btn-info optionsAreaBtn">
                        <input type="radio" class="optionsArea" name="optionsAreaM" id="option5" autocomplete="off"> Е
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group col-sm-12 col-lg-3 smallScrSelect rowSelect" style="display: none">
                  <label for="displayAvRows" class="control-label">Избери Ред</label>
                  <select class="form-control" id="displayAvRows">
                    <option> - </option>
                  </select>
                </div>
                <div class="form-group col-sm-12 col-lg-3 smallScrSelect seatSelect" style="display: none">
                  <label for="displayAvSeets" class="control-label">Избери Седиште</label>
                  <select class="form-control" id="displayAvSeats">
                    <option> - </option>
                  </select>
                </div>
                <div class="col-sm-12">
                  <button type="button" class="btn btn-success smallScrSelect" style="display: none" id="selectSeatSmallDev" onclick="selectSeat($('#displayAvSeats option:selected').attr('id').slice(5))">Избери</button>
                  <button type="button" class="btn btn-outline-success d-lg-none selectedSeatsBuyBtn" id="goToPayingSection" style="display: none">Купи</button>
                </div>
              </div>
            </form>
            <hr>
            <div class="row">
              <div class="d-block mx-auto seatChartsContainer" style="display: none">

              </div>
            </div>
          </div>
          <div class="card-body selectedSeatsPreviewCOntainer" style="display: none">
            <hr>
            <h5 class="card-title">Резервирано:</h5>
            <ul class="list-unstyled orderedTickets">

            </ul>
            <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#largescreenShoppingModal" aria-expanded="false">Купи</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="errorContainer" style="display: none; padding-top: 45px;">
    <div class="col-sm-12 text-center">
      <h4>СЕРВИСОТ МОМЕНТАЛНО НЕ Е ДОСТАПЕН!</h4>
      <a href="http://mkc.mk/mk/" class="btn btn-secondary btn-lg" role="button">Назад</a>
    </div>
  </div>

  <!-- Modal for seat chart image -->
  <div class="modal fade" id="seatChartPreviewDialog" tabindex="-1" role="dialog">
    <div class="modal-dialog  modal-lg" role="document">
      <div class="modal-content">
        <img src="./images/skica.png" class="img-fluid seatChartPreviewImage" alt="Кино Фросина">
      </div>
    </div>
  </div>

  <!-- Modal for general error -->
  <div class="modal fade" id="selectErrorDialog" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Грешка</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Ве молиме обидете се повторно.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Затвори</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Dialog for Large Screen Orders -->
  <div class="modal fade" id="largescreenShoppingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Информации за купување</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form data-toggle="validator" role="form" method="post" action="https://entegrasyon.asseco-see.com.tr/fim/est3Dgate" id="payFormLarge">
            <div class="form-group">
              <label for="imeForma1" class="control-label">Име и презиме</label>
              <input type="text" class="form-control input-sm" id="imeForma1" data-error="Задожително поле" autocomplete="off" required>
              <div class="help-block with-errors text-danger"></div>
            </div>
            <div class="form-group">
              <label for="telefonForma1" class="control-label">Телефон</label>
              <input type="text" class="form-control input-sm" id="telefonForma1" data-error="Задожително поле (најмалку 9 карактери)"
                data-minlength="9" autocomplete="off" required>
              <div class="help-block with-errors text-danger"></div>
            </div>
            <div class="form-group">
              <label for="emailForma1" class="control-label">Електронска пошта</label>
              <input type="email" class="form-control input-sm" id="emailForma1" data-error="Електронската пошта е невалидна" autocomplete="off"
                required>
              <div class="help-block with-errors text-danger"></div>
            </div>
            <div class="form-group">
              <label for="emailForma21" class="control-label">Внесете ја повторно електронската пошта</label>
              <input type="email" class="form-control input-sm" id="emailForma21" data-error=" " data-match="#emailForma1" data-match-error="Грешка. Покушајте повторно"
                autocomplete="off" required>
              <div class="help-block with-errors text-danger"></div>
            </div>
            <input type="hidden" name="clientid" id="clientid" value="" />
            <input type="hidden" name="oid" id="oid" value="" />
            <input type="hidden" name="amount" id="amount" value="" />
            <input type="hidden" name="okUrl" id="okUrl" value="http://sins.dyndns.info/mkc/success.php" />
            <input type="hidden" name="failUrl" id="failUrl" value="http://sins.dyndns.info/mkc/fail.php" />
            <input type="hidden" name="trantype" id="trantype" value="" />
            <input type="hidden" name="instalment" id="instalment" value="" />
            <input type="hidden" name="rnd" value="" id="rnd" />
            <input type="hidden" name="storekey" value="" id="storekey" />
            <input type="hidden" name="hash" value="" id="hash" />
            <input type="hidden" name="storetype" value="" id="storetype" />
            <input type="hidden" name="lang" value="" id="lang" />
            <input type="hidden" name="currency" value="" id="currency" />
            <input type="hidden" name="refreshtime" value="5" />
            <hr>
            <h5 class="card-title hiddenWhenNoSeats">Резервирано:</h5>
            <ul class="list-unstyled orderedTickets hiddenWhenNoSeats">

            </ul>
            <hr class="hiddenWhenNoSeats">
            <h6>Вкупен број на билети:
              <strong>
                <span class="totalTicketNum"></span>
              </strong>
            </h6>
            <h6>Вкупно за плаќање:
              <strong>
                <span class="totalSum"></span>
              </strong> ден</h6>
            <hr>
            <button type="submit" class="btn btn-outline-success float-right" onclick="event.preventDefault(); payRequest('L');">Плати</button>
            <button type="button" class="btn btn btn-outline-info float-left" data-dismiss="modal">Назад</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal for error ven selectiog ocupied seat -->
  <div class="modal fade" id="faildSelectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Зафатено седиште</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Седиште е веќе зафатено. Ве молиме обидите се повторно.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Затвори</button>
        </div>
      </div>
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