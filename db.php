<?php
    abstract class db_type
    {

        const MySql = 1;
        const Oracle = 2;
        const ODBC = 3;
    }
    
    abstract class db_connection_parameters
    {
        const username = 'root';
        const password = 'Arpadzik2@';
        const host = '192.168.0.204';
        const database = 'db_booking1';
    }
    
    abstract class settings_parameters
    {
        // const ip = '77.28.27.138';
        // const ip = '77.28.80.130';
        // const ip = '92.55.94.20';
        const ip = '23.227.129.242';
    }
    
    class db
    {
        public $type;
        public $link;
        
        public function db($type)
        {
            $this->type = $type;
            $link = null;
        }
        
        public function db_close()
        {
            mysql_close($this->link);
        }
        
        public function db_connect(&$errorMessage)
        {
            $res = false;
            $errorMessage = '';
            
            switch ($this->type)
            {
                case db_type::MySql:
                {
                    $this->link = mysql_connect(db_connection_parameters::host, db_connection_parameters::username, db_connection_parameters::password);
                    if ($this->link)
                    {
                        mysql_query("SET NAMES 'utf8'", $this->link);
                        if (mysql_selectdb(db_connection_parameters::database, $this->link))
                        {
                            $res = true;
                        }
                        else
                        {
                            if ($this->link)
                            {
                                $errorMessage = mysql_error($this->link);                                
                                mysql_close($this->link);
                                $this->link = null;
                            }
                            else
                            {
                                $this->link = null;
                                $errorMessage = mysql_error();
                            }
                        }
                    }
                    else
                    {                                 
                        $this->link = null;
                        $errorMessage = mysql_error();
                    }
                    break;
                }
                default:
                {
                    $errorMessage = 'Database type not specified';
                }
            }
            
            return $res;
        }
        
        public function validate_token($token, $ip, $sessionid, $log) {
            $res = false;
            $res = $this->link;
            if ($res) {
                $res = false;
                if (($ip == NULL) && ($sessionid == NULL))
                {
                    $sql = "select os_id from online_session where token = '" . $token . "' and time_to_sec(timediff(now(), tokendatetime)) < 420";
                    $log->log_message('ip==null sessionid==null ' . $sql);
                    $res_sql = mysql_query($sql, $this->link);
                    if ($res_sql)
                    {
                        $row = mysql_fetch_assoc($res_sql);
                        if ($row) {
                            $res = true;
                        }
                        mysql_free_result($res_sql);
                    }
                }
                else 
                {
                    $sql = "select os_id from online_session where token = '" . $token . "'";
                    $log->log_message($sql);                    
                    $res_sql = mysql_query($sql, $this->link);
                    if ($res_sql)
                    {
                        $row = mysql_fetch_assoc($res_sql);
                        if ($row) {
                            $log->log_message('res=true ' . $sql);                            
                            $res = true;
                        }
                        else
                        {
                            $sql = "insert into online_session (remoteip, sessionid, token, tokendatetime) values ('" . $ip . "', '" . $sessionid . "', '" . $token . "', now())";
                            $log->log_message($sql);                            
                            $res_insert_sql = mysql_query($sql, $this->link);
                            if ($res_insert_sql)
                            {
                                $res = true;
                            }
                        }                                            
                        mysql_free_result($res_sql);
                    }
                }
            }
            return $res;
        }            
        
    }
    
    class eventDates
    {
        public $idHall;
        public $date;
        public $time;
        
        function eventDates($idHall, $date, $time)
        {
            $this->idHall = $idHall;
            $this->date = $date;
            $this->time = $time;
        }
    }
    
    class event 
    {
        public $responseStatus = 'OK';
        public $responseMessage = 'Test Message';
        public $eventName = "Test Event";
        public $eventDates;
        
        function event($eventName, $eventDates)
        {
            $this->eventName = $eventName;
            $this->eventDates = new eventDates();
            $this->eventDates = $eventDates;
        }
    }
?>
