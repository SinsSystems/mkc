<?php
    session_start();    

    /*
    {"sub_id":"282","token":"Token","level_1":"1","level_2":"0","level_3":"1","level_4":"1"}    
    */   
    require_once('log.php');
    require_once('response.php');
    require_once('db.php');
    
    $log = new log();    
    
    
    $data = file_get_contents("php://input");
    $log->log_message('select_seat.php: ' . $data);
    
    $sessionid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
    //if ($ip != settings_parameters::ip) {
    if (!$ip) {        
        $mess = 'Invalid Request Source: event.php from ip: ' . $ip . ' required ip: ' . settings_parameters::ip;
        $log->log_message($mess);
        $json = new errorResponse($mess);
        $jsonString = $json->jsonResponse();
    }
    else
    {    
        if ($data)
        {
            $log->log_message("Select seat: Received data: " . $data);
            // check for token and then conitnue
            try
            {
                $seat = json_decode($data);
                $res = true;
            }
            catch (Exception $e)
            {
                $res = false;
            }
            if ($res)
            {
                $token = $seat->token;
                $id = $seat->sub_id;
                $log->log_message('Select seat: IP: ' . $ip . ' Token: ' . $token);                
                if (($id) && ($token))
                {
                    // continue
                    $dbConnection = new db(db_type::MySql);
                    $errorMessage = '';
                    if ($dbConnection->db_connect($errorMessage))
                    {
                        $log->log_message('Validate token: ' . $token);                        
                        if ($dbConnection->validate_token($token, null, null, $log))
                        {
                            $json = new eventSelectSeatResponse($data, $dbConnection->link, $token, $sessionid, $ip, $log);
                            if ($json->res)
                            {
                                $jsonString = $json->jsonResponse();
                            }
                            else
                            {
                                $json = new errorResponse($json->errorMessage);
                                $jsonString = $json->jsonResponse();
                            }
                        }
                        else
                        {
                            
                        }
                        $dbConnection->db_close();
                    }
                    else
                    {
                        $json = new errorResponse($errorMessage);
                        $jsonString = $json->jsonResponse();        
                    }        
                }
                else
                {
                    $json = new errorResponse('Invalid Request: select_seat.php: id: ' . $id . ' Token: ' . $token);
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
                $json = new errorResponse('No valid request');
                $jsonString = $json->jsonResponse();        
        }
    }
    echo $jsonString;
?>