<?php
/**
 * Simple PDF Generator Class
 * Basic PDF generation for reports without external dependencies
 */

class PDFGenerator {
    private $title;
    private $headers;
    private $data;
    private $filename;
    private $report_info;
    private $filter_info;
    private $summary;

    public function __construct($title = 'Report') {
        $this->title = $title;
        $this->headers = [];
        $this->data = [];
        $this->filename = 'report_' . date('Y-m-d_H-i-s') . '.pdf';
        $this->report_info = '';
        $this->filter_info = '';
        $this->summary = [];
    }
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function setFilename($filename) {
        $this->filename = $filename;
    }
    
    public function setHeaders($headers) {
        $this->headers = $headers;
    }
    
    public function addRow($row) {
        $this->data[] = $row;
    }
    
    public function setData($data) {
        $this->data = $data;
    }

    public function setReportInfo($report_info, $filter_info = '') {
        $this->report_info = $report_info;
        $this->filter_info = $filter_info;
    }

    public function setSummary($summary) {
        $this->summary = $summary;
    }
    
    /**
     * Generate and output PDF using HTML to PDF conversion
     * This creates a print-friendly HTML page that can be converted to PDF
     */
    public function output() {
        // Generate HTML content for PDF
        $html = $this->generateHTML();

        // Output HTML that can be printed as PDF
        echo $html;

        // // Add JavaScript to automatically trigger print dialog
        // echo '<script>
        //     window.onload = function() {
        //         // Show instructions to user
        //         document.body.innerHTML += "<div style=\"position: fixed; top: 10px; right: 10px; background: #007bff; color: white; padding: 10px; border-radius: 5px; z-index: 1000;\">Press Ctrl+P to save as PDF</div>";

        //         // Optional: Auto-trigger print dialog after a short delay
        //         setTimeout(function() {
        //             window.print();
        //         }, 1000);
        //     }
        // </script>';
    }

    /**
     * Generate beautiful HTML-based PDF that downloads directly
     */
    public function outputAsBeautifulPDF() {
        // Generate beautiful HTML
        $html = $this->generateBeautifulHTML();

        // Set headers for HTML display with print functionality
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

        echo $html;

        // Add enhanced auto-print JavaScript with download simulation
        // echo '<script>
        //     window.onload = function() {
        //         // Add download instruction
        //         var downloadMsg = document.createElement("div");
        //         downloadMsg.style.cssText = "position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 15px 20px; border-radius: 8px; z-index: 10000; font-family: Arial, sans-serif; box-shadow: 0 4px 12px rgba(0,0,0,0.3);";
        //         downloadMsg.innerHTML = "<strong>📄 PDF Ready!</strong><br>Press <kbd>Ctrl+P</kbd> then choose <em>Save as PDF</em><br><small>This window will auto-close after printing</small>";
        //         document.body.appendChild(downloadMsg);

        //         // Auto-trigger print dialog
        //         setTimeout(function() {
        //             window.print();

        //             // Close window after print dialog (if opened in new window)
        //             setTimeout(function() {
        //                 if (window.opener) {
        //                     window.close();
        //                 }
        //             }, 1000);
        //         }, 800);

        //         // Handle print completion
        //         window.addEventListener("afterprint", function() {
        //             if (window.opener) {
        //                 window.close();
        //             }
        //         });
        //     }
        // </script>';
    }
    
    private function generateHTML() {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($this->title) . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .date {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">' . htmlspecialchars($this->title) . '</div>
        <div class="date">Generated on: ' . date('Y-m-d H:i:s') . '</div>
    </div>
    
    <table>
        <thead>
            <tr>';
        
        foreach ($this->headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        
        $html .= '</tr>
        </thead>
        <tbody>';
        
        foreach ($this->data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p>Report generated by ERP System - ' . date('Y-m-d H:i:s') . '</p>
    </div>
</body>
</html>';
        
        return $html;
    }

    /**
     * Generate beautiful, professional HTML for PDF
     */
    private function generateBeautifulHTML() {
        $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($this->title) . '</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .page-break { page-break-before: always; }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #fff;
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px 0;
            border-bottom: 3px solid #007bff;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .title {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .subtitle {
            font-size: 16px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .date {
            font-size: 14px;
            color: #495057;
            background: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            border: 1px solid #dee2e6;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
            background: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .report-table thead {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }

        .report-table th {
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-right: 1px solid rgba(255,255,255,0.2);
        }

        .report-table th:last-child {
            border-right: none;
        }

        .report-table tbody tr {
            transition: background-color 0.3s ease;
        }

        .report-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .report-table tbody tr:hover {
            background-color: #e3f2fd;
        }

        .report-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            font-size: 13px;
            vertical-align: middle;
        }

        .report-table tbody tr:last-child td {
            border-bottom: none;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-top: 3px solid #007bff;
        }

        .footer p {
            color: #6c757d;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .company-info {
            font-weight: bold;
            color: #007bff;
            font-size: 14px;
        }

        .stats-summary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }

        .stats-summary h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .no-print {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .summary-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .summary-section h3 {
            color: #495057;
            margin-bottom: 20px;
            text-align: center;
            font-size: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .summary-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .summary-value {
            font-weight: bold;
            color: #007bff;
            font-size: 16px;
        }

        .filter-info {
            color: #6c757d;
            font-size: 14px;
            margin-top: 5px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <strong>📄 PDF Export Ready!</strong> Press <kbd>Ctrl+P</kbd> (Windows) or <kbd>Cmd+P</kbd> (Mac) to save as PDF, or use your browser\'s print function.
    </div>

    <div class="header">
        <div class="title">' . htmlspecialchars($this->title) . '</div>
        <div class="subtitle">Professional Report Generated by ERP System</div>
        <div class="date">📅 ' . ($this->report_info ?: 'Generated on: ' . date('F j, Y \a\t g:i A')) . '</div>';

        if ($this->filter_info) {
            $html .= '<div class="filter-info">🔍 ' . htmlspecialchars($this->filter_info) . '</div>';
        }

        $html .= '</div>';

        // Add enhanced summary stats if it's an item report
        if (strpos(strtolower($this->title), 'item') !== false) {
            $total_items = count($this->data);
            $total_quantity = 0;
            $total_value = 0;
            $out_of_stock = 0;
            $low_stock = 0;
            $good_stock = 0;

            foreach ($this->data as $row) {
                // Quantity is in column 4 (index 4)
                if (isset($row[4])) {
                    $qty_str = str_replace(',', '', $row[4]);
                    if (is_numeric($qty_str)) {
                        $quantity = intval($qty_str);
                        $total_quantity += $quantity;

                        // Count stock levels
                        if ($quantity == 0) {
                            $out_of_stock++;
                        } elseif ($quantity < 10) {
                            $low_stock++;
                        } else {
                            $good_stock++;
                        }
                    }
                }

                // Total value is in column 6 (index 6)
                if (isset($row[6])) {
                    $value_str = str_replace(['LKR ', ','], '', $row[6]);
                    if (is_numeric($value_str)) {
                        $total_value += floatval($value_str);
                    }
                }
            }

            $html .= '<div class="stats-summary">
                <h3>📊 Inventory Summary Dashboard</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px;">
                    <div style="text-align: center; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
                        <div style="font-size: 24px; font-weight: bold;">' . $total_items . '</div>
                        <div style="font-size: 14px;">Total Items</div>
                    </div>
                    <div style="text-align: center; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
                        <div style="font-size: 24px; font-weight: bold;">' . number_format($total_quantity) . '</div>
                        <div style="font-size: 14px;">Total Quantity</div>
                    </div>
                    <div style="text-align: center; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
                        <div style="font-size: 24px; font-weight: bold;">LKR ' . number_format($total_value, 2) . '</div>
                        <div style="font-size: 14px;">Total Value</div>
                    </div>
                    <div style="text-align: center; background: rgba(255,255,255,0.2); padding: 15px; border-radius: 8px;">
                        <div style="font-size: 18px;">🟢 ' . $good_stock . ' | 🟡 ' . $low_stock . ' | 🔴 ' . $out_of_stock . '</div>
                        <div style="font-size: 14px;">Stock Status</div>
                    </div>
                </div>
            </div>';
        }

        $html .= '<table class="report-table">
        <thead>
            <tr>';

        foreach ($this->headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }

        $html .= '</tr>
        </thead>
        <tbody>';

        foreach ($this->data as $row) {
            $html .= '<tr>';
            foreach ($row as $index => $cell) {
                $cell_content = htmlspecialchars($cell);

                // Special formatting for item reports
                if (strpos(strtolower($this->title), 'item') !== false) {
                    // Stock Status column (last column)
                    if ($index == count($row) - 1) {
                        if (strpos($cell, '🔴') !== false) {
                            $cell_content = '<span style="color: #dc3545; font-weight: bold;">' . $cell_content . '</span>';
                        } elseif (strpos($cell, '🟡') !== false) {
                            $cell_content = '<span style="color: #ffc107; font-weight: bold;">' . $cell_content . '</span>';
                        } elseif (strpos($cell, '🟢') !== false) {
                            $cell_content = '<span style="color: #28a745; font-weight: bold;">' . $cell_content . '</span>';
                        } elseif (strpos($cell, '🔵') !== false) {
                            $cell_content = '<span style="color: #007bff; font-weight: bold;">' . $cell_content . '</span>';
                        }
                    }
                    // Monetary values (Unit Price and Total Value columns)
                    elseif (strpos($cell, 'LKR') !== false) {
                        $cell_content = '<span style="color: #28a745; font-weight: 600;">' . $cell_content . '</span>';
                    }
                    // Item Code column
                    elseif ($index == 1) {
                        $cell_content = '<code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 11px;">' . $cell_content . '</code>';
                    }
                    // Quantity column
                    elseif ($index == 4) {
                        $cell_content = '<span style="font-weight: 600; color: #495057;">' . $cell_content . '</span>';
                    }
                }

                $html .= '<td>' . $cell_content . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody>
    </table>';

        // Add summary section if available
        if (!empty($this->summary)) {
            $html .= '
    <div class="summary-section">
        <h3>📊 Report Summary</h3>
        <div class="summary-grid">';

            foreach ($this->summary as $label => $value) {
                $html .= '
            <div class="summary-item">
                <span class="summary-label">' . htmlspecialchars($label) . ':</span>
                <span class="summary-value">' . htmlspecialchars($value) . '</span>
            </div>';
            }

            $html .= '
        </div>
    </div>';
        }

        $html .= '
    <div class="footer">
        <p class="company-info">🏢 Csquare Technologies ERP System</p>
        <p>Report generated on ' . date('Y-m-d H:i:s') . '</p>
        <p>© ' . date('Y') . ' All rights reserved</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Alternative output method that creates a proper PDF file
     * Uses a simple text-based PDF generation approach
     */
    public function outputAsPDF() {
        try {
            // Create a simple PDF using basic PDF structure
            $pdf_content = $this->generateSimplePDF();

            // Set proper PDF headers
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $this->filename . '"');
            header('Content-Length: ' . strlen($pdf_content));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');

            echo $pdf_content;
        } catch (Exception $e) {
            // Fallback to HTML output if PDF generation fails
            error_log("PDF generation failed: " . $e->getMessage());
            $this->outputAsBeautifulPDF();
        }
    }

    /**
     * Generate a simple PDF file content
     * This creates a basic but valid PDF document
     */
    private function generateSimplePDF() {
        // Basic PDF structure
        $pdf = "%PDF-1.4\n";
        $pdf .= "1 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Catalog\n";
        $pdf .= "/Pages 2 0 R\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n\n";

        // Create content for the PDF
        $content = $this->title . "\n\n";
        $content .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";

        // Add headers
        $content .= implode(" | ", $this->headers) . "\n";
        $content .= str_repeat("-", 80) . "\n";

        // Add data rows
        foreach ($this->data as $row) {
            $content .= implode(" | ", $row) . "\n";
        }

        // Continue building PDF structure
        $pdf .= "2 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Pages\n";
        $pdf .= "/Kids [3 0 R]\n";
        $pdf .= "/Count 1\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n\n";

        $pdf .= "3 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Page\n";
        $pdf .= "/Parent 2 0 R\n";
        $pdf .= "/MediaBox [0 0 612 792]\n";
        $pdf .= "/Contents 4 0 R\n";
        $pdf .= "/Resources <<\n";
        $pdf .= "/Font <<\n";
        $pdf .= "/F1 5 0 R\n";
        $pdf .= ">>\n";
        $pdf .= ">>\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n\n";

        $content_length = strlen($content);
        $pdf .= "4 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Length $content_length\n";
        $pdf .= ">>\n";
        $pdf .= "stream\n";
        $pdf .= "BT\n";
        $pdf .= "/F1 12 Tf\n";
        $pdf .= "50 750 Td\n";

        // Add content line by line
        $lines = explode("\n", $content);
        $y_position = 750;
        foreach ($lines as $line) {
            $pdf .= "($line) Tj\n";
            $y_position -= 15;
            $pdf .= "0 -15 Td\n";
        }

        $pdf .= "ET\n";
        $pdf .= "endstream\n";
        $pdf .= "endobj\n\n";

        $pdf .= "5 0 obj\n";
        $pdf .= "<<\n";
        $pdf .= "/Type /Font\n";
        $pdf .= "/Subtype /Type1\n";
        $pdf .= "/BaseFont /Helvetica\n";
        $pdf .= ">>\n";
        $pdf .= "endobj\n\n";

        $pdf .= "xref\n";
        $pdf .= "0 6\n";
        $pdf .= "0000000000 65535 f \n";
        $pdf .= "0000000009 65535 n \n";
        $pdf .= "0000000074 65535 n \n";
        $pdf .= "0000000120 65535 n \n";
        $pdf .= "0000000179 65535 n \n";
        $pdf .= "0000000364 65535 n \n";
        $pdf .= "trailer\n";
        $pdf .= "<<\n";
        $pdf .= "/Size 6\n";
        $pdf .= "/Root 1 0 R\n";
        $pdf .= ">>\n";
        $pdf .= "startxref\n";
        $pdf .= "492\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }
}
