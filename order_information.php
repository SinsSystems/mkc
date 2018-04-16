<?php
    session_start();
    
    require_once('log.php');
    require_once('response.php');
    require_once('db.php');
    
    $log = new log();
    
    
    $data = file_get_contents("php://input");
    $log->log_message('order_information.php: ' . $data);
    
    $sessionid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    //if ($ip != settings_parameters::ip) {
    if (!$ip) {        
        $mess = 'Invalid Request Source: order_information.php from ip: ' . $ip . ' required ip: ' . settings_parameters::ip;
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
                        $sql = "update sedista_old set pay_type = 3 where reservation_order_id = '" . $orderid . "'";
                        $ressql = mysql_query($sql, $dbConnection->link);
                        if ($ressql)
                        {
                            $response = new eventOrderTransactionResponse($orderid, $dbConnection->link, $log);
                            $response->res = true;
                            $response->order_id = $orderid;                                
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