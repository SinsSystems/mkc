<?php 
    require_once('./include/tcpdf/config/tcpdf_config.php');
    require_once('./include/tcpdf/tcpdf.php');   
    $file_name ="bilet.pdf";

    $printData = $_POST['printData'];
    
    $html = '';
    $html .= '<!DOCTYPE html>';
    $html .= '<html lang="en">';
    $html .= '  <head>';
    $html .= '<meta charset="utf-8">';
    $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
    $html .= '</head>';
    $html .= '<body>';
    $html .=  $printData;
    $html .= '</body>';
    $html .= '</html>';
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['vraboten_ime']);
    $pdf->SetTitle("Bilet");

    $pdf->SetFont('dejavusans', '', 10);    
    
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    
    
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->SetPageOrientation('p');
    $pdf->AddPage();
    
    // write body
    // Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->setBarcode(date('Y-m-d H:i:s'));
    $style = array(
    'position' => '',
    'align' => 'C',
    'stretch' => false,
    'fitwidth' => true,
    'cellfitalign' => '',
    'border' => true,
    'hpadding' => 'auto',
    'vpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => false //array(255,255,255),
    );
    $pdf->Cell(0, 0, 'CODE 128 A', 0, 1);
    $pdf->write1DBarcode('CODE 128 A', 'C128A', '', '', '', 18, 0.4, $style, 'N');
    
    // export pdf
    $pdf->Output($file_name, 'I');    
?>