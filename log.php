<?php   
    class log
    {
        private $filename = 'error.log';
        
        public function log()
        {
            return;
        }
                
        public function log_message($message)
        {
            $datetimestamp = gmdate("Y-m-d H:i:s ");
            try
            {
                file_put_contents($this->filename, $datetimestamp . ' ' . $message . PHP_EOL, FILE_APPEND);                                        
            }
            catch (Exception $e)
            {
                // do nothing 
            }
            return;
        }        
    }    
?>
