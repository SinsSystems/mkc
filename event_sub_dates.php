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
        $mess = 'Invalid Request Source: event_sub_dates.php from ip: ' . $ip . ' required ip: ' . settings_parameters::ip;
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
        $log->log_message("Event_sub_dates.php: Received IP: " . $ip . ' Token: ' . $token);
        $log->log_message("Event_sub_dates.php: Received ID:" . $id);

        if (($id) && ($token))
        {
            $dbConnection = new db(db_type::MySql);
            $errorMessage = '';
            if ($dbConnection->db_connect($errorMessage))
            {
                $log->log_message('event_sub_dates.php Validate token: ' . $token);
                if ($dbConnection->validate_token($token, null, null, $log))
                {                
                    $json = new eventSubDatesResponse($id, $dbConnection->link, $token, $sessionid, $ip, $log);
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
                    $mess = 'Invalid Request: event_sub_dates.php: Token invalid/ not found';
                    $log->log_message($mess);
                    $json = new errorResponse($mess);
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
                $mess = 'No ID specified: event_sub_dates.php';
                $log->log_message($mess);            
                $json = new errorResponse($mess);
                $jsonString = $json->jsonResponse();        
        }
    }
    echo $jsonString;
?>