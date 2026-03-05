<?php
declare(strict_types=1);

namespace App\Core;

use Dompdf\Dompdf;
use Dompdf\Options;

final class Pdf
{
    public static function download(string $html, string $filename): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($output));
        echo $output;
        exit;
    }
}
