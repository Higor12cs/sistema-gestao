<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;

class BaseReportService
{
    /**
     * Generate PDF from HTML view
     */
    public function generatePdf(string $viewName, array $data = [], string $orientation = 'portrait'): string
    {
        $pdf = Pdf::loadView($viewName, $data)
            ->setOption('isPhpEnabled', true)
            ->setOption('isJson', false);

        if ($orientation === 'landscape') {
            $pdf->setPaper('A4', 'landscape');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        return $pdf->output();
    }

    /**
     * Download PDF response
     */
    public function downloadPdf(string $viewName, array $data = [], string $filename = 'report.pdf', string $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($viewName, $data)
            ->setOption('isPhpEnabled', true)
            ->setOption('isJson', false);

        if ($orientation === 'landscape') {
            $pdf->setPaper('A4', 'landscape');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        return $pdf->download($filename);
    }

    /**
     * Stream PDF response (inline)
     */
    public function streamPdf(string $viewName, array $data = [], string $filename = 'report.pdf', string $orientation = 'portrait')
    {
        $pdf = Pdf::loadView($viewName, $data)
            ->setOption('isPhpEnabled', true)
            ->setOption('isJson', false);

        if ($orientation === 'landscape') {
            $pdf->setPaper('A4', 'landscape');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        return $pdf->stream($filename);
    }

    /**
     * Format currency
     */
    public function formatCurrency(float $value): string
    {
        return 'R$ '.number_format($value, 2, ',', '.');
    }

    /**
     * Format percentage
     */
    public function formatPercentage(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.').'%';
    }

    /**
     * Format date
     */
    public function formatDate($date, string $format = 'd/m/Y'): string
    {
        return $date ? $date->format($format) : 'N/A';
    }
}
