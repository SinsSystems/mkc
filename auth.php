<?php
    require_once('include/jwt.php');
    require_once('log.php');
    require_once('db.php');
    
    $log = new log();
    
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
    
    class Response 
    {
        public $message;
        public $res;
        
        public function Response($res, $message)
        {
            $this->res = $res;
            $this->message = $message;
            $this->message = json_encode($this);
        }
    }
    
    if (isset($_GET['auth']))
    {
        $jwt = $_GET['auth'];
        $key = base64_encode(settings_parameters::ip);

        $log->log_message('REMOTE ADDRESS:' . $_SERVER['REMOTE_ADDR']);
        $log->log_message('SERVER ADDRESS:' . $_SERVER['SERVER_ADDR']);            
        if ($_SERVER['REMOTE_ADDR'] == '77.28.13.93')
        {
            $key = base64_encode($_SERVER['REMOTE_ADDR']);
            $log->log_message($key);
        }
        
        try
        {
            $auth = JWT::decode($jwt, $key);
            $res = true;
        }
        catch (Exception $ex)
        {
            $res = false;
        }
        
        if ($res)
        {        
            $db = new db(db_type::MySql);
            
            $res = $db->db_connect($errorMessage);
            if ($res)
            {
                $res = $db->validate_token($auth->token, $auth->ip, $auth->sessionid, $log);
                if ($res)
                {
                    $token = new Response($res, $auth->token);
                    echo $token->message;
                }
                else
                {
                    $err = new Response($res, 'Invalid Auth');
                    echo $err->message;                
                }
            }
            else
            {
                $err = new Response($res, $errorMessage);
                echo $err->message;            
            }
        }
        else
        {
            $err = new Response($res, 'JWT error:' . $key);
            echo $err->message;                        
        }
    }
    else
    {
        $err = new Response(false, 'Error Auth'); 
        echo $err->message;
    }    
?>