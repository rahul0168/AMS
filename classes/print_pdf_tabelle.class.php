<?php
// classes/print_pdf_tabelle.class.php
require_once __DIR__ . '/../vendor/autoload.php'; // adjust path if needed
use Mpdf\Mpdf;

class PrintPdfTabelle {
        private $data = [];

    public function add_row($row) {
        $this->data[] = $row; 
        echo "PDF Row: " . implode(" | ", $row) . "<br>";
    }

     public function output_pdf() {
        if (empty($this->data)) {
            echo "No data to generate PDF.";
            return;
        }

        $mpdf = new Mpdf();
        $html = '<h3 style="text-align:center;">Attendance Report</h3>';
        $html .= '<table border="1" cellspacing="0" cellpadding="6" width="100%">
                    <thead>
                        <tr style="background-color:#f2f2f2;">';

        // Table header from first row
        foreach ($this->data[0] as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        // Table rows
        for ($i = 1; $i < count($this->data); $i++) {
            $html .= '<tr>';
            foreach ($this->data[$i] as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $mpdf->WriteHTML($html);
        $mpdf->Output('attendance_report.pdf', 'D'); // Display inline
    }
}
?>
