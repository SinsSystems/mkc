<?php
  class mailMessageEvents
  {
    public $event_name;
    public $datetime;
    public $qty;
    public $amount;    
  }
  
  class mailMessage
  {
    public $order_id;
    public $events;
  }
  
  class mailResponse
  {
      public $from;
      public $to;
      public $message;
      public $subject;
      public $headers;
      public $res;
      public $errorMessage;
      private $url;
      
    public function mailResponse($to, $message)
    {
        $this->to = $to;
        $this->message = $message;
        $this->from = "Makedonski Naroden Teatar Online <online@mnt.mk>";
        $this->subject = 'Potvrda za uspesna transakcija/ Successfull transaction confirmation';
        
        $this->res = false;
        $this->errorMessage = '';
        
        $this->url = 'http://sins.dyndns.info/mnt_booking/print_ticket.php';
    }
    
    public function mailSend()
    {
       
        $this->mailGenerate($html);
        
        if ($this->res)
        {
            $this->res = false;
            $host = "mail.mnt.mk";
            // $host = "mail.sins.com.mk";
            // $port = "465";
            $port = "25";
            $username = "online@mnt.mk";
            // $username = 'ljupco@sins.com.mk';
            $password = "cF4-pW9-3qf-0c2";
            // $password = "L!s!ns";
            
            $this->headers = array ('From' => $this->from,
                                    'To' => $this->to,
                                    'Subject' => $this->subject,
                                    'Content-Type' => 'text/html; charset=UTF-8'
                                    );
            $smtp = Mail::factory('smtp',
                                  array ('host' => $host,
                                         'port' => $port,
                                         'auth' => true,
                                         'username' => $username,
                                         'password' => $password));
                                         
            // Creating the Mime message
            $mime_params = array(
                'text_encoding' => '7bit',
                'text_charset' => 'UTF-8',
                'html_charset' => 'UTF-8',
                'head_charset' => 'UTF-8'                
            );
            $mime = new Mail_mime();

            // Setting the body of the email
            $mime->setTXTBody($html);
            $mime->setHTMLBody($html);

            $body = $mime->get($mime_params);
            $this->headers = $mime->headers($this->headers);                                         

            $mail = $smtp->send($this->to, $this->headers, $body);

            if (PEAR::isError($mail)) {
                $this->errorMessage = $mail->getMessage();
                $this->res = false;
            } else {
                $this->errorMessage = '';
                $this->res = true;
            }
        }
    }
    
    public function mailGenerate(&$html)
    {
        $total_qty = 0;
        $total_amount = 0;
        for ($i = 0; $i < count($this->message->events); $i++)
        {
            $total_qty += $this->message->events->qty;
            $total_amount += $this->message->events->price;            
        }
        $html = '';
//        $html = $html . '<!DOCTYPE html>';
        //$html = $html . '<html>';
//        $html = $html . '<head>';
        // $html = $html . '<meta charset="UTF-8">';
//        $html = $html . '<title>E-mail val</title>';
//        $html = $html . '</head>';
        $html = $html . '<body>';
//        $html = $html . '<style>';
//        $html = $html . 'table {';
//        $html = $html . 'width: 100%;';
//        $html = $html . 'border: 1px solid black;';
//        $html = $html . 'border-collapse: collapse;';
//        $html = $html . '}';
//        $html = $html . 'th,';
//        $html = $html . 'td {';
//        $html = $html . 'padding: 7px;';
//        $html = $html . 'border: 1px solid black;';
//        $html = $html . 'border-collapse: collapse;';
//        $html = $html . 'text-align: center;';
//        $html = $html . '}';
//        $html = $html . '</style>';        
        $html = $html . '<p>';
        $html = $html . 'Почитувани';
        $html = $html . '<br>';
        $html = $html . '<br> Ве информираме дека направивте успешна трансакција за нарачка број ' . $this->message->order_id;
        $html = $html . '<br>';
        $html = $html . '<br> Број на купени карти: ' . $total_qty;
        $html = $html . '<br>';
        $html = $html . '<br> Вкупен износ: ' . $total_amount;
        $html = $html . '<br>';
        $html = $html . '<br> Ве молиме, заради валидација, задолжително со Вас да ја донесете печатената карта.';
        $html = $html . '</p>';
        $html = $html . '<table style="width: 100%; border: 1px solid black; border-collapse: collapse;">';
        $html = $html . '<tr>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Настан</th>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Датум и време</th>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Количина</th>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Вкупно</th>';
        $html = $html . '</tr>';
        for ($i = 0; $i < count($this->message->events); $i++)
        {
        $html = $html . '<tr>';        
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->event_name . '</td>';
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->datetime . '</td>';
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->qty . '</td>';
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->amount . '</td>';
        $html = $html . '</tr>';
        }        
        $html = $html . '</table>';
        $html = $html . '<p>';
        $html = $html . 'Вашате карти можете да отпечатите на следниот линк: ';
        $html = $html . '<a href="' . $this->url . '?order_id=' . $this->message->order_id . '">Печатење на карти</a>';
        $html = $html . '<br> Со почит,';
        $html = $html . '<br> Македонски Народен Театар';
        $html = $html . '</p>';
        $html = $html . '<p>';
        $html = $html . 'Dear';
        $html = $html . '<br>';
        $html = $html . '<br> You have made successful transaction for order id: '  . $this->message->order_id;
        $html = $html . '<br>';
        $html = $html . '<br> Total Tickets: ' . $total_qty;
        $html = $html . '<br>';
        $html = $html . '<br> Total Amount: ' . $total_amount;
        $html = $html . '<br>';
        $html = $html . '<br> For validation purposed, please take printed card with you.';
        $html = $html . '</p>';        
        $html = $html . '<table style="width: 100%; border: 1px solid black; border-collapse: collapse;">';
        $html = $html . '<tr>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Event</th>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Date and time</th>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Quantity</th>';
        $html = $html . '<th style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">Total</th>';
        $html = $html . '</tr>';
        for ($i = 0; $i < count($this->message->events); $i++)
        {
        $html = $html . '<tr>';        
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->event_name . '</td>';
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->datetime . '</td>';
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->qty . '</td>';
        $html = $html . '<td style="padding: 7px; border: 1px solid black; border-collapse: collapse; text-align: center;">' . $this->message->events->amount . '</td>';
        $html = $html . '</tr>';
        }        
        $html = $html . '</table>';
        $html = $html . '<p>';
        $html = $html . 'You can print your online voucher by clicking following link: ';
        $html = $html . '<a href="' . $this->url . '?order_id=' . $this->message->order_id . '">Print tickets</a>';
        $html = $html . '<br> Regards,';
        $html = $html . '<br> MNT';
        $html = $html . '</p>';
        $html = $html . '</body>';
//        $html = $html . '</html>';
        
        $this->res = true;
    }
  }
?>