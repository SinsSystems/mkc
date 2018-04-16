<?php
    class errorResponse
    {
        public $res;
        public $errorMessage;
        
        public function errorResponse($errorMessage)
        {
            $this->res = false;
            $this->errorMessage = $errorMessage;
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }
    }
    
    class ticketSerial
    {
        public $serial;
    }
    
    class transactionResponse
    {
        public $res;
        public $errorMessage;
        public $eventName;
        public $eventDate;
        public $eventTime;
        public $eventName1;
        public $eventName2;
        public $reservationName;
        public $reservationEmail;
        public $serials;
        
        public function transactionResponse($id, $link, $log)
        {
            $this->errorMessage = '';
            $this->res = false;
            if ($link)
            {
                $sql = "select barkod as event_serial, reservation_name, reservation_email, naziv as event_name, rezija as event_name1, rezija2 as event_name2, sedista_old.cena as event_price  from sedista_old ";
                $sql .= "inner join seansi_old on seansi_old.id = sedista_old.id_pretstava inner join datum_old on datum_old.id = seansi_old.id_datum inner join nastani_old on nastani_old.id = datum_old.id_pretstava where sedista_old.reservation_order_id = " . $id;
                $res = mysql_query($sql, $link);
                if ($res)
                {
                    $row = mysql_fetch_assoc($res);
                    while ($row)
                    {
                        if ($this->serials == 0)
                        {
                            $this->eventName = $row['event_name'];                        
                            $this->eventName1 = $row['event_name1'];                        
                            $this->eventName2 = $row['event_name2'];                        
                            $this->eventDate = $row['event_date'];                        
                            $this->eventTime = $row['event_time'];                        
                            $this->reservationName = $row['reservation_name'];                        
                            $this->reservationEmail = $row['reservation_email'];                        
                        }
                        $barcode = $row['event_serial'];
                        $serial = new ticketSerial();
                        $serial->serial = $barcode;                        
                        $this->serials[] = $serial;
                        $row = mysql_fetch_assoc($res);                                                
                    }
                    $this->res = true;
                    mysql_free_result($res);
                }
                else
                {
                    $this->errorMessage = mysql_error($link);
                }
            }
            else
            {
                $this->errorMessage = 'There is no connection to database';
            }            
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }                
    }
    
    class eventResponse
    {
        public $res;
        public $errorMessage;
        public $token;
        public $id;
        public $id_room;
        public $name_room;
        public $seat_room;
        public $name;
        public $name2;
        public $price;
        public $price1;
        public $price2;
        public $price3;
        public $price4;
        public $date_from;
        public $date_to;
        public $info1;
        public $info2;
        public $info3;
        
        public function eventResponse($id, $link, $token, $sessionid, $ip, $log)
        {
            $this->errorMessage = '';
            $this->res = false;
            if ($link)
            {
                $res = mysql_query("select * from nastani_old inner join rooms on id_prostorija = rooms.room_id where nastani_old.id = " . $id, $link);
                if ($res)
                {
                    $row = mysql_fetch_assoc($res);
                    if ($row)
                    {
                        $this->token = 'Token';
                        $this->id = $id;
                        $this->id_room = $row['room_id'];                        
                        $this->name_room = $row['room_name'];
                        $this->seat_room = $row['room_isnumerated'];
                        $this->name = $row['Naziv'];
                        $this->name2 = $row['Naziv2'];
                        $this->price = $row['Cena'];
                        $this->price1 = $row['Cena1'];
                        $this->price2 = $row['Cena2'];
                        $this->price3 = $row['cena3'];
                        $this->price4 = $row['cena4'];                        
                        $this->date_from = $row['oddatum'];
                        $this->date_to = $row['dodatum'];
                        $this->info1 = $row['rezija'];
                        $this->info2 = $row['ulogi'];
                        $this->info3 = $row['ulogi2'];
                        $this->info3 = $row['ulogi2'];
                        // now generate new token
                        $newtoken = hash("sha512", $token . $sessionid . $ip);                        
                        $sql = "update online_session set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                        $log->log_message('Event response change token: ' . $sql);
                        $restoken = mysql_query($sql, $link);
                        if ($restoken) {
                            $this->token = $newtoken;
                            $this->res = true;                            
                        }
                        else {
                            $this->errorMessage = "Invalid validation operation";
                        }
                    }
                    else
                    {
                        $this->errorMessage = 'Event ID does not exist';
                    }
                    mysql_free_result($res);
                }
                else
                {
                    $this->errorMessage = mysql_error($link);
                }
            }
            else
            {
                $this->errorMessage = 'There is no connection to database';
            }            
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }        
    }
    
    
    class eventSeats
    {
        public $level_01;
        public $level_02;
        public $level_03;
        public $level_04;

        public function eventSeats($level_01, $level_02, $level_03, $level_04)
        {
            $this->level_01 = $level_01;    
            $this->level_02 = $level_02;    
            $this->level_03 = $level_03;    
            $this->level_04 = $level_04;                
        }
    }
    
    class eventSeatsResponse
    {
        public $res;
        public $errorMessage;
        public $token;
        public $id_event_sub;
        public $seats;
        
        public function eventSeatsResponse($id_event_sub, $link, $token, $sessionid, $ip, $log)
        {
            $this->errorMessage = '';
            $this->res = false;
            $this->token = 'Token';
            if ($link)
            {
                $sql = "select * from sedista_old where id_seansa = " . $id_event_sub . " and (status > 0 or (status = 0 and pay_type > 0) or (status = 0 and pay_type = 0 and time_to_sec(timediff(now(), tokendatetime)) < 420))";
                $res = mysql_query($sql, $link);
                if ($res)
                {
                    $rows_per_event = mysql_numrows($res);
                    if ($rows_per_event > 0) 
                    {
                        $this->event_sub = array();
                    }
                    $this->token = $token;
                    $this->id_event_sub = $id_event_sub;
                    
                    $i = 0;                    
                    $row = mysql_fetch_assoc($res);                    
                    while ($row)
                    {
                        $seats = new eventSeats($row['level_01'], $row['level_02'], $row['level_03'], $row['level_04']);
                        $this->seats[] = $seats;
                        $i++;                        
                        $row = mysql_fetch_assoc($res);
                    }
                    $this->res = ($i == $rows_per_event);
                    if (!$this->res)
                    {
                        $this->errorMessage = 'Event ID does not exist';
                    }
                    mysql_free_result($res);
                    if ($this->res)
                    {
                        // now generate new token
                        $newtoken = hash("sha512", $token . $sessionid . $ip);                        
                        $sql = "update online_session set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                        $restoken = mysql_query($sql, $link);
                        if ($restoken) {
                            $this->token = $newtoken;
                            $sql = "update sedista_old set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                            $restokenseats = mysql_query($sql, $link);
                            if ($restokenseats)
                            {
                                $this->res = true;                            
                            }
                            else
                            {
                                $this->res = false;
                                file_put_contents('error.log', 'Error updating own seats: ' . mysql_error($link));
                                $this->errorMessage = 'Error updating own seats: ' . mysql_error($link);
                            }
                        }
                        else {
                            $this->res = false;
                            $this->errorMessage = "Invalid validation operation";
                        }
                    }                                        
                }
                else
                {
                    $this->errorMessage = mysql_error($link);
                }                
            }
            else
            {
                $this->errorMessage = 'There is no connection to database';
            }
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }                
    }
    

    class eventSubDateHour
    {
        public $sub_id;
        public $date;
        public $time;

        public function eventSubDateHour($sub_id, $date, $time)
        {
            $this->sub_id = $sub_id;
            $this->date = $date;
            $this->time = $time;
        }
    }
    
    class eventSubDatesResponse
    {
        public $res;
        public $errorMessage;
        public $token;
        public $id;
        public $event_sub;
        
        public function eventSubDatesResponse($id, $link, $token, $sessionid, $ip, $log)
        {
            $this->errorMessage = '';
            $this->res = false;
            if ($link)
            {
                $sql = "select seansi_old.id as event_sub_id, seansi_old.datum as event_date, seansi_old.cas as event_hour from seansi_old left outer join datum_old on datum_old.id = seansi_old.id_datum where id_pretstava = " . $id;
                $sql .= " and str_to_date(concat(seansi_old.datum, ' ', seansi_old.cas), '%Y-%m-%d %H:%i') > (date_sub(now(), interval (300 * 86400) second))";
                $sql .= " order by seansi_old.datum, seansi_old.cas";
                $res = mysql_query($sql, $link);
                if ($res)
                {
                    $rows_per_event = mysql_numrows($res);
                    if ($rows_per_event > 0) 
                    {
                        $this->event_sub = array();
                    }
                    $i = 0;
                    $row = mysql_fetch_assoc($res);
                    while ($row)
                    {
                        $this->token = 'Token';
                        $this->id = $id;
                        $event_sub = new eventSubDateHour($row['event_sub_id'], $row['event_date'], $row['event_hour']);
                        $this->event_sub[] = $event_sub;
                        $this->res = true;
                        $i++;                        
                        $row = mysql_fetch_assoc($res);
                    }
                    $this->res = ($i == $rows_per_event);
                    if (!$this->res)
                    {
                        $this->errorMessage = 'Event ID does not exist';
                    }
                    mysql_free_result($res);
                    if ($this->res)
                    {
                        // now generate new token
                        $newtoken = hash("sha512", $token . $sessionid . $ip);                        
                        $sql = "update online_session set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                        $log->log_message('eventSubDatesResponse: Token Change: ' . $sql);
                        $restoken = mysql_query($sql, $link);
                        if ($restoken) {
                            $this->token = $newtoken;
                            $this->res = $restoken;                            
                        }
                        else {
                            $this->res = false;
                            $this->errorMessage = "Invalid validation operation";
                        }
                    }                    
                }
                else
                {
                    $this->errorMessage = mysql_error($link);
                }
            }
            else
            {
                $this->errorMessage = 'There is no connection to database';
            }
            
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }        
    }    
    
    
    // SELECT SEAT
    class eventSelectSeatResponse
    {
        public $res;
        public $errorMessage;
        public $sub_id;
        public $token;
        public $selected;
        public $data;
        
        public function eventSelectSeatResponse($data, $link, $token, $sessionid, $ip, $log)
        {
            $this->errorMessage = '';
            $this->res = false;
            $this->selected = false;
            $this->token = 'Token';
            $this->data = $data;
            
            // decompose $data
            $seat = json_decode($data);
            
            // start processing
            if ($link)
            {
                $sql_insert = "call p_seat_select " .
                              "(" .
                              $seat->sub_id . ", " .
                              "0, " . // status 0 for online
                              "0, " . // price for insert
                              "curdate(), " .
                              $seat->level_1 . "," .
                              $seat->level_2 . "," . 
                              $seat->level_3 . "," .
                              $seat->level_4 . "," .
                              "'" . $token . "', " .
                              "now(), " .
                              "@o_res" .
                              ")";
                // update the database
                $res_insert = mysql_query($sql_insert, $link);
                $res_insert = mysql_query("select @o_res", $link);    
                if ($res_insert) 
                { 
                    $row = mysql_fetch_assoc($res_insert);
                    if ($row)
                    {
                        $o_res = $row['@o_res'];
                        if ($o_res > 0)
                        {
                            $this->selected = true; $this->res = true; $this->sub_id = $seat->sub_id; 
                            // now generate new token
                            $newtoken = hash("sha512", $token . $sessionid . $ip);                        
                            $sql = "update online_session set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                            $log->log_message('eventSelectSeatResponse: Token Change: ' . $sql);
                            $restoken = mysql_query($sql, $link);
                            if ($restoken) {
                                $sql = "update sedista_old set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                                $log->log_message('eventSelectSeatResponse: Token Change: ' . $sql);
                                $restoken = mysql_query($sql, $link);
                                if ($restoken)
                                {
                                    $this->token = $newtoken;
                                    $this->res = $restoken;  
                                    $seatFree = true;                          
                                }
                                else
                                {
                                    $this->res = false;
                                    $this->errorMessage = 'Token not updated for seats';
                                }
                            }
                            else {
                                $this->res = false;
                                $this->errorMessage = "select_seat.php: Invalid validation operation";
                            }
                        }
                        else
                        {
                            $this->res = true;
                            $seatFree = false;
                            $this->selected = false;
                            // now generate new token
                            $newtoken = hash("sha512", $token . $sessionid . $ip);                        
                            $sql = "update online_session set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                            $log->log_message('eventSelectSeatResponse: Token Change: ' . $sql);
                            $restoken = mysql_query($sql, $link);
                            if ($restoken) {
                                $sql = "update sedista_old set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                                $log->log_message('eventSelectSeatResponse: Token Change: ' . $sql);
                                $restoken = mysql_query($sql, $link);
                                if ($restoken)
                                {
                                    $this->token = $newtoken;
                                    $this->res = $restoken;  
                                }
                                else
                                {
                                    $this->res = false;
                                    $this->errorMessage = 'Token not updated for seats';
                                }
                            }
                            else {
                                $this->res = false;
                                $this->errorMessage = "select_seat.php: Invalid validation operation";
                            }                                                        
                            $this->errorMessage = 'Seat Already Selected';
                        }
                    }
                    else
                    {
                        $this->res = false;
                        $this->errorMessage = 'select_seat.php: Invalid result from procedure call for selection seat';
                    }                                
                } 
            }
            else 
            { 
                $this->res = false; 
                $this->errorMessage = "select_seat.php: Invalid selection: " . mysql_error($link);                                
            }
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }        
    }    
    
    // DELETE SEAT
    class eventDeleteSeatResponse
    {
        public $res;
        public $errorMessage;
        public $sub_id;
        public $token;
        public $selected;
        public $data;
        
        public function eventDeleteSeatResponse($data, $link, $token, $sessionid, $ip, $log)
        {
            $this->errorMessage = '';
            $this->res = false;
            $this->token = 'Token';
            $this->data = $data;
            
            // decompose $data
            $seat = json_decode($data);
            
            // start processing
            if ($link)
            {
                $res = mysql_query("delete from sedista_old where id_seansa = " . $seat->sub_id . " and level_01 = " . $seat->level_1 . " and level_02 = " . $seat->level_2 . " and level_03 = " . $seat->level_3 . " and level_04 = " . $seat->level_4, $link);
                if ($res)
                {
                    // now generate new token
                    $newtoken = hash("sha512", $token . $sessionid . $ip);                        
                    $sql = "update online_session set tokendatetime = now(), token = '" . $newtoken . "' where token = '" . $token . "'";
                    $log->log_message('eventDeleteSeatResponse: Token Change: ' . $sql);
                    $restoken = mysql_query($sql, $link);
                    if ($restoken) {
                        $this->token = $newtoken;
                        $this->res = $restoken;                            
                    }
                    else {
                        $this->res = false;
                        $this->errorMessage = "Invalid validation operation";
                    }                                
                }
                else
                {      
                    $this->errorMessage = mysql_error($link);
                }
            }
            else
            {
                $this->errorMessage = 'There is no connection to database';
            }            
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }        
    }        
    
    class eventProcessTransactionResponse
    {
        public $res;
        public $errorMessage;
        public $order_id;
        public $clientid;
        public $amount;
        public $oid;
        public $okurl;
        public $failurl;
        public $currencyval;
        public $storekey;
        public $lang;
        public $transactiontype;
        public $rnd;
        public $hash;
        
        public function eventProcessTransactionResponse()
        {
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }        
    }            
    
    class eventOrderTransactionSerials
    {
        public $serial;
        public $event_sub_id;
        public $level_01;
        public $level_02;
        public $level_03;
        public $level_04;
        public $amount;
        public $date;
        public $time;        
    }
    
    class eventOrderTransactionResponse
    {
        public $res;
        public $errorMessage;
        public $order_id;
        public $naslov;
        public $autor;
        public $director;
        public $scena;
        public $serials;
        
        public function eventOrderTransactionResponse($order_id, $link, $log)
        {
            if ($link)
            {
                $sql = "select barkod as serial, nastani_old.naziv as event_name, nastani_old.naziv2 as event_name2, rezija as event_name3, seansi_old.datum as event_date, seansi_old.cas as event_time, nastani_old.id_prostorija as event_room_id, level_01, level_02, level_03, level_04, (sedista_old.kol * sedista_old.cena) as amount, sedista_old.id_seansa as event_sub_id from sedista_old " .
                       "inner join seansi_old on seansi_old.id = sedista_old.id_seansa " .
                       "inner join datum_old on datum_old.id = seansi_old.id_datum " .
                       "inner join nastani_old on nastani_old.id = datum_old.id_pretstava " .
                       "where reservation_order_id = '" . $order_id . "'";
                $log->log_message("EventOrderTransactionResponse: " . $sql);
                $res_sql = mysql_query($sql, $link);
                if ($res_sql)
                {
                    while ($row = mysql_fetch_assoc($res_sql))                    
                    {
                        $serial = new eventOrderTransactionSerials();
                        $serial->serial = '1234567890'; // $row['serial'];
                        $serial->event_sub_id = $row['event_sub_id'];                        
                        $serial->level_01 = $row['level_01'];
                        $serial->level_02 = $row['level_02'];
                        $serial->level_03 = $row['level_03'];
                        $serial->level_04 = $row['level_04'];                        
                        $serial->amount = $row['amount'];
                        $serial->date = $row['event_date'];
                        $serial->time = $row['event_time'];                        
                        $this->serials[] = $serial;
                        $this->naslov = $row['event_name'];
                        $this->autor = $row['event_name2'];
                        $this->director = $row['event_name3'];
                        $this->scena = $row['event_room_id'];                        
                    }
                    $log->log_message("EventOrderTransactionResponse: " . $this->jsonResponse());
                }
                else
                {
                    $this->errorMessage = "Error retrieving additional event data: " . mysql_error($link);
                }
            }
            else
            {
                $this->errorMessage = "Cannot retrieve information from database";
            }
        }
        
        // return json string from this object
        public function jsonResponse()
        {
            $sRes = '';
            $sRes = json_encode($this);
            return $sRes;
        }        
    }                
?>