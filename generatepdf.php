<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('memory_limit', '256M');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

require_once '../vendor/autoload.php';
use Mpdf\Mpdf;

$tempDir = __DIR__ . '/tmp/mpdf';
if (!file_exists($tempDir) && !mkdir($tempDir, 0777, true)) {
    die("Failed to create temp directory!");
}

$mpdf = new \Mpdf\Mpdf(['tempDir' => $tempDir]);

// Define the directory to store images
$localImageDir = __DIR__ . '/DownloadedFabricImages/';

// Create the directory if it doesn't exist
if (!file_exists($localImageDir)) {
    mkdir($localImageDir, 0777, true);
}

try {
    // Read JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['name']) || !isset($data['image']) || !isset($data['group_order'])) {
        throw new Exception("Invalid input");
    }

    $fabricName = $data['name'];
    $fabricImage = $data['image'];
    $groupOrder = $data['group_order'];

    // Initialize MPDF
    $mpdf = new Mpdf(['dpi' => 100, 'tempDir' => $tempDir]);

    // Watermark
    $mpdf->setWatermarkImage("sparshwater.png", 0.3, '', [0, 0]);
    $mpdf->showWatermarkImage = true;

    // Load images
    $Logodata = 'data:image/png;base64,' . base64_encode(file_get_contents('logo.png'));
    $fabricdata = 'data:image/png;base64,' . base64_encode(file_get_contents('fabric.png'));

    // Write initial HTML content
    $mpdf->WriteHTML('
    <style>
       
        body { font-family: Arial, sans-serif; margin: 20px; font-size:20px;}
        img { max-width: 100%; height: auto; }
        table { width: 100%; }
    </style>
  
    <div class="container" >
      
 <table class="table table-bordered" >
            <thead>
                <tr>
                    <th  class="text-center"><img src="' . $Logodata . '" style="width: 100%; max-width: 200px; height: auto;"></th>
                </tr>
            </thead>
            </table>
   
        <table class="table table-bordered" style="padding-top:8rem;margin-right:8rem;">
            <thead>
             
            </thead>
            <tbody>
                <tr>
                    <td rowspan="6" class="text-center">
            <img src="' .$fabricImage. '" style="width: 100%; max-width:500px;  height: 750px;    mix-blend-mode: screen;">
                    </td>
                </tr>
    ');
	
	//var_dump($Logodata); exit;

    // Write group order details in chunks
if (!empty($groupOrder)) {
    $localImageDir = __DIR__ . '/DownloadedFabricImages/'; // Local directory for images
    if (!file_exists($localImageDir)) {
        mkdir($localImageDir, 0777, true); // Create directory if it doesn't exist
    }

    foreach ($groupOrder as $order) {
        if ($order != null) {
            $fabricImageUrl = $order['fabric_img'];
            $imageName = basename(parse_url($fabricImageUrl, PHP_URL_PATH)); // Extract filename from URL
            $localImagePath = $localImageDir . $imageName;

            // Download the image if it doesn't already exist locally
            if (!file_exists($localImagePath)) {
                $ch = curl_init($fabricImageUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $imageContents = curl_exec($ch);

                if (curl_errno($ch)) {
                    throw new Exception('cURL Error: ' . curl_error($ch));
                }

                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($httpCode !== 200) {
                    throw new Exception('HTTP Error: Received response code ' . $httpCode);
                }

                curl_close($ch);

                file_put_contents($localImagePath, $imageContents); // Save image locally
            }

            // Use local image path directly in PDF
            $imageData = $localImagePath;

            $chunkHTML = '
                <tr class="text-center">
                    <td style="width: 250px;text-align:right;">
                        <p class="text-center" ><strong>Fabric Name:</strong> ' . htmlspecialchars($order['fabric_object']['designCode']) . '</p>
                        <p class="text-center" ><strong>Product:</strong> ' . htmlspecialchars($order['fabric_object']['products']) . '</p>
                    </td>
                    <td class="text-center">
                        <img src="' . $imageData . '" style="width: 70%; max-width: 200px; height: 200px;">
                    </td>
                </tr>';

            if (count($groupOrder) === 1) {
                $chunkHTML .= '
                <tr class="text-center">
                    <td><p class="text-center"><strong>&nbsp;</strong> &nbsp;</p></td>
                    <td class="text-center">
                        <img src="' . $imageData . '" style="width: 70%; max-width: 200px; height: 200px;">
                    </td>
                </tr>
                <tr class="text-center">
                    <td><p><strong>&nbsp;</strong> &nbsp;</p></td>
                    <td class="text-center">
                        <img src="' . $imageData . '" style="width: 70%; max-width: 200px; height: 200px;">
                    </td>
                </tr>';
            } elseif (count($groupOrder) === 2) {
                $chunkHTML .= '
                <tr class="text-center">
                    <td><p><strong>&nbsp;</strong> &nbsp;</p></td>
                    <td class="text-center">
                        <img src="' . $imageData . '" style="width: 70%; max-width: 200px; height: 200px;">
                    </td>
                </tr>';
            }

            $mpdf->WriteHTML($chunkHTML);
        }
    }
}
    // Close the table
    $mpdf->WriteHTML('
            </tbody>
        </table>
    </div>');

	
    // Generate PDF
    $fileName = 'sparsh_fabric_' . time() . '.pdf';
    $directory = __DIR__ . '/pdf';
    $filePath = $directory . '/' . $fileName;

    if (!file_exists($directory) && !mkdir($directory, 0777, true)) {
        die("Failed to create directory.");
    }

    // Save PDF
    $mpdf->Output($filePath, 'F');

    // Verify file exists before outputting
    if (!file_exists($filePath)) {
        die('PDF file not found.');
    }

    // Send the file for download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Content-Length: ' . filesize($filePath));

    ob_clean();
    flush();
    readfile($filePath);
	
	// Delete the PDF after download
	unlink($filePath);
	exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
