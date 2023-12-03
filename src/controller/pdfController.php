<?php

class PDFController
{
    public function descargarPDFConLogo($request, $response)
    {
        $pdf = new TCPDF();
    
        $pdf->SetFont('helvetica', '', 12);    
        $pdf->AddPage();
    
        $logo = 'E:\Programas\xamp\htdocs\LaComanda\src\descargas\logo.jpg';
        
        list($anchoImg, $largoImg) = getimagesize($logo);
    
        $x = ($pdf->getPageWidth() - $anchoImg) / 2;
        $y = ($pdf->getPageHeight() - $largoImg) / 2;
    
        $pdf->Image($logo, $x, $y, $anchoImg, $largoImg, 'JPG');
        
        $pdf->Cell(0, 10, 'La Comanda - TP FINAL', 0, 1, 'C'); 
        $pdfName = 'pdf_con_logo.pdf';
        
        // 'D' download modo para descargar el pdf. 
        $pdf->Output($pdfName, 'D');
    
        return $response->withHeader('Content-Type', 'application/pdf');
    }
}