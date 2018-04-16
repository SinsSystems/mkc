<?php
    session_start();
    
    require_once('log.php');
    require_once('response.php');
    require_once('db.php');
    require_once('include/pear/Mail.php');
    require_once('include/pear/Mail/mime.php');    
    require_once('mail.php');
    
    $log = new log();
    
    
    $data = file_get_contents("php://input");
    $log->log_message('mail_information.php: ' . $data);
    
    $sessionid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    //if ($ip != settings_parameters::ip) {
    if (!$ip) {        
        $mess = 'Invalid Request Source: mail_information.php from ip: ' . $ip . ' required ip: ' . settings_parameters::ip;
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
                $order = json_decode($data);
                $res = true;
            }
            catch (Exception $e)
            {
                $res = false;
            }
            if ($res)            
            {
                $orderid = $order->order_id;
                $log->log_message($orderid);                
                if ($orderid)
                {
                    // continue
                    $dbConnection = new db(db_type::MySql);
                    $errorMessage = '';
                    if ($dbConnection->db_connect($errorMessage))
                    {
                        $ressql = true;
                        if ($ressql)
                        {
                            $response = new eventOrderTransactionResponse($orderid, $dbConnection->link, $log);
                            $response->res = true;
                            $response->order_id = $orderid;                                
                            $jsonString = $response->jsonResponse();
                            // send email now
                            $sql = "select n.id as event_id, n.naziv as event_name, s.datum as event_date, s.cas as event_time, sedista_old.kol as qty, (sedista_old.kol * sedista_old.cena) as price, level_01, level_02, level_03, level_04, reservation_name, reservation_email from sedista_old";
                            $sql = $sql . " inner join seansi_old as s on s.id = sedista_old.id_seansa";
                            $sql = $sql . " inner join datum_old as d on d.id = s.id_datum";
                            $sql = $sql . " inner join nastani_old as n on n.id = d.id_pretstava";
                            $sql = $sql . " where reservation_order_id = '" . $orderid . "'";
                            $ressqlemail = mysql_query($sql, $dbConnection->link);
                            if ($ressqlemail)
                            {
                                $mailMessage = new mailMessage();
                                $mailMessage->order_id = $orderid;
                                $event_id = -1;
                                while ($row = mysql_fetch_assoc($ressqlemail))
                                {
                                    $to = $row['reservation_email'];
                                    if ($event_id != $row['event_id'])
                                    {
                                        if ($event_id > 0)
                                        {
                                            $mailMessage->events = $event;
                                        }
                                        $event = new mailMessageEvents();
                                        $event_id = $row['event_id'];
                                        $event->event_name = $row['event_name'];
                                        $event->datetime = $row['event_date'] . ' ' . $row['event_time'];
                                        $event->qty = $row['qty'];
                                        $event->amount = $row['price'];
                                    }
                                    else
                                    {
                                        $event->qty = $event->qty + $row['qty'];
                                        $event->amount = $event->amount + $row['price'];
                                    }
                                }
                                if ($event != null)
                                {
                                    $mailMessage->events = $event;
                                }
                                mysql_free_result($ressqlemail);
                                $mail = new mailResponse($to, $mailMessage);     
                                $mail->mailSend($emailRes);
                                if ($emailRes) { $log->log_message('Mail sent'); }
                            }
                            
                        }
                        else
                        {
                            $json = new errorResponse('No valid request: Error saving order');
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
    echo $jsonString;
?>