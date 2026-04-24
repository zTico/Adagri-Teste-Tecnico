<?php

namespace App\Services\Exports;

use App\Models\RuralProducer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;

class ProducerHerdPdfExportService
{
    public function export(RuralProducer $ruralProducer): Response
    {
        $ruralProducer->load(['farms.herds']);

        $farms = $ruralProducer->farms
            ->map(function ($farm): array {
                $herds = $farm->herds->map(fn ($herd): array => [
                    'species' => $herd->species?->label(),
                    'purpose' => $herd->purpose?->label(),
                    'quantity' => $herd->quantity,
                    'updated_at' => $herd->updated_at?->format('Y-m-d H:i'),
                ])->all();

                return [
                    'name' => $farm->name,
                    'city' => $farm->city,
                    'state' => $farm->state,
                    'total_animals' => $farm->herds->sum('quantity'),
                    'herds' => $herds,
                ];
            })
            ->all()
        ;

        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
        ]);

        $dompdf = new Dompdf($options);
        $html = view('pdf.producer-herds', [
            'ruralProducer' => $ruralProducer,
            'farms' => $farms,
            'generatedAt' => now(),
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('a4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="producer-herds-'.$ruralProducer->id.'.pdf"',
        ]);
    }
}
