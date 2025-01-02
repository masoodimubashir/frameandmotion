<?php

namespace App\Service;

use Fpdf\Fpdf;

class PdfService extends Fpdf
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        parent::__construct(); // Call the parent constructor
    }

    function Header()
    {
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(81, 177, 225);
        $this->cell(47 * 2, 10, 'Name:' . ' Frame And Motion', 0, 0, 'L');
        $this->cell(47 * 2, 10, 'Contact No: ' . +919797230468, 0, 0, 'R');
        $this->Ln(20);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(81, 177, 225);
        $this->Cell(47 * 2, 10, 'Developed By Py.Sync PVT LTD ', 0, 0, 'L');
        $this->Cell(47 * 2, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
}
