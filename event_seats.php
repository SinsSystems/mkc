<?php
    session_start();

    require_once('log.php');    
    require_once('response.php');
    require_once('db.php');
    
    $log = new log();    
    
    $sessionid = session_id();
    $ip = $_SERVER['REMOTE_ADDR'];    
    // if ($ip != settings_parameters::ip) {
    if (!$ip) {
        $mess = 'Invalid Request Source: event_seats.php from ip: ' . $ip . ' required ip: ' . settings_parameters::ip;
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
        if (($id) && ($token))
        {
            $dbConnection = new db(db_type::MySql);
            $errorMessage = '';
            if ($dbConnection->db_connect($errorMessage))
            {
                if ($dbConnection->validate_token($token, null, null, $log))
                {
                    $json = new eventSeatsResponse($id, $dbConnection->link, $token, $sessionid, $ip);
                    if ($json->res)
                    {
                        $jsonString = $json->jsonResponse();
                    }
                    else
                    {
                        $log->log_message($json->errorMessage);
                        $json = new errorResponse($json->errorMessage);
                        $jsonString = $json->jsonResponse();
                    }
                }
                else
                {
                    $log->log_message('Invalid Request: event_seats.php: Token');                    
                    $json = new errorResponse('Invalid Request: event_seats.php: Token');
                    $jsonString = $json->jsonResponse();                                                
                }
                $dbConnection->db_close();
            }
            else
            {
                $log->log_message($errorMessage);                
                $json = new errorResponse($errorMessage);
                $jsonString = $json->jsonResponse();        
            }        
        }
        else
        {
                $log->log_message('Invalid Request: event_seats.php: id: ' . $id . ' Token: ' . $token);
                $json = new errorResponse('Invalid Request: event_seats.php: id: ' . $id . ' Token: ' . $token);
                $jsonString = $json->jsonResponse();        
        }
    }
    echo $jsonString;
?>