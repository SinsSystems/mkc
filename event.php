<?php
    session_start();
    
    require_once('log.php');
    require_once('response.php');
    require_once('db.php');    
    
    $log = new log();
    
    $sessionid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];
//    if ($ip != settings_parameters::ip) {
    if (!$ip) {
        $mess = 'Invalid Request Source: event.php from ip: ' . $ip . ' required ip: ' . settings_parameters::ip;
        $log->log_message($mess);
        $json = new errorResponse($mess);
        $jsonString = $json->jsonResponse();
    }
    else
    {            
        if (isset($_GET['id']))
        {
            $id = $_GET['id'];
        }
        if (isset($_GET['token'])) {
            $token = $_GET['token'];
        }
        
        $log->log_message($ip . ' ' . $token);
        $log->log_message("Event.php: Received IP: " . $ip . ' Token: ' . $token);
        $log->log_message("Event.php: Received ID:" . $id);
        
        if (($id) && ($token))
        {
            $dbConnection = new db(db_type::MySql);
            $errorMessage = '';
            if ($dbConnection->db_connect($errorMessage))
            {
                $log->log_message('Validate token: ' . $token);
                if ($dbConnection->validate_token($token, null, null, $log))
                {
                    $log->log_message('Event Reponse Token: ' . $token);                    
                    $json = new eventResponse($id, $dbConnection->link, $token, $sessionid, $ip, $log);
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
                    $json = new errorResponse('Invalid Request: event.php: Token');
                    $jsonString = $json->jsonResponse();                            
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
                $json = new errorResponse('Invalid Request: event.php: id: ' . $id . ' Token: ' . $token);
                $jsonString = $json->jsonResponse();        
        }
    }
    echo $jsonString;
?>