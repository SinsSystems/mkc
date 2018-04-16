<?php
    session_start();
    
    require_once('log.php');
    require_once('response.php');
    require_once('db.php');
    
    $log = new log();    
    
    $data = file_get_contents("php://input");
    $log->log_message('process_transaction1.php: ' . $data);
    
    $sessionid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    // if ($ip != settings_parameters::ip) {
    if (!$ip) {
        $mess = 'Invalid Request Source: event.php from ip: ' . $ip . ' required ip: ' . settings_parameters::ip;
        $log->log_message($mess);
        $json = new errorResponse($mess);
        $jsonString = $json->jsonResponse();
    }
    else
    {
        // process
        if ($data)
        {
            try
            {
                $process = json_decode($data);
                $res = true;
            }
            catch (Exception $e)
            {
                $res = false;
            }
            if ($res)            
            {
                $token = $process->token;
                $log->log_message($token);                
                if (($token))
                {
                    // continue
                    $dbConnection = new db(db_type::MySql);
                    $errorMessage = '';
                    if ($dbConnection->db_connect($errorMessage))
                    {
                        $log->log_message('Validate token: ' . $token);                        
                        if ($dbConnection->validate_token($token, null, null, $log))
                        {   
                            $order_id = date('ymdhis');
                            $sql = "update sedista_old set reservation_name = '" . $process->name . "', reservation_phone = '" . $process->telephon . "', reservation_order_id = '" . $order_id . "', reservation_email = '" . $process->email . "' where token = '" . $process->token . "'";
                            $ressql = mysql_query($sql, $dbConnection->link);
                            if ($ressql)
                            {
                                $clientId = "180000085";            //Merchant Id defined by bank to user
                                // $clientId = "180000183";
                                $amount = 1;//$process->price;                  //Transaction amount
                                $oid = $order_id;                          //Order Id. Must be unique. If left blank, system will generate a unique one.
                                //  92.55.94.20 mnt ip
                                // sins.dyndns.info/mnt_booking
                                // $okUrl = "https://www.mnt.mk/online/success.php";       //URL which client be redirected if authentication is successful
                                //  $failUrl = "https://www.mnt.mk/online/fail.php";     //URL which client be redirected if authentication is not successful
                                // sins ip                                
                                $okUrl = "http://sins.dyndns.info/mnt_booking/fail.php";       //URL which client be redirected if authentication is successful
                                $failUrl = "http://sins.dyndns.info/mnt_booking/success.php";     //URL which client be redirected if authentication is not successful
                                $rnd = $sessionid;                 //A random number, such as date/time
                                $currencyVal = "807";           //Currency code, 949 for TL, ISO_4217 standard
                                $storekey = "SKEY0183";             //Store key value, defined by bank.
                                $storetype = "3d_pay_hosting";  //3D authentication model
                                $lang = "mk";                   //Language parameter, "tr" for Turkish (default), "en" for English
                                $transactionType = "Auth"; 
                                $instalment ="";    //transaction type
                                
                                $oid = $order_id;

                                $hashstr = $clientId . $oid . $amount . $okUrl . $failUrl . $transactionType . $instalment . $rnd . $storekey;
                                $sha1 = sha1($hashstr);
                                $hash = base64_encode(pack('H*', sha1($hashstr)));
                                
                                $response = new eventProcessTransactionResponse();
                                $response->res = true;
                                $response->errorMessage = '';
                                $response->order_id = $oid;                                
                                $response->clientid = $clientId;
                                $response->amount = $amount;
                                $response->oid = $oid;
                                $response->okurl = $okUrl;
                                $response->failurl = $failUrl;
                                $response->currencyval = $currencyVal;
                                $response->storekey = $storekey;
                                $response->lang = $lang;
                                $response->transactiontype = $transactionType;
                                $response->rnd = $rnd;
                                $response->hash = $hash;
                                $jsonString = $response->jsonResponse();
                            }
                            else
                            {
                                $json = new errorResponse('No valid request: Error saving order');
                                $jsonString = $json->jsonResponse();                                                                                                                
                            }
                        }
                        else
                        {
                            $json = new errorResponse('No valid request: Invalid Token');
                            $jsonString = $json->jsonResponse();                                                                                
                        }
                    }
                    else
                    {
                        $json = new errorResponse('No valid request: ' . $errorMessage);
                        $jsonString = $json->jsonResponse();                                                                                                        
                    }
                }
                else
                {   
                    $json = new errorResponse('No valid request: Invalid Token');
                    $jsonString = $json->jsonResponse();                                                                                                    
                }                
            }
            else
            {
                $json = new errorResponse('No valid request: data');
                $jsonString = $json->jsonResponse();                                                    
            }
        }
        else
        {
            $json = new errorResponse('No valid request: data');
            $jsonString = $json->jsonResponse();                                    
        }
    }
    $log->log_message('Process transaction 1: ' . $jsonString);
    echo $jsonString;
?>