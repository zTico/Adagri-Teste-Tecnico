<?php

namespace App\Services\Exports;

use App\Domain\Farms\FarmFilters;
use App\Infra\Db\FarmDb;
use App\Models\Farm;
use App\Support\Exports\SimpleXlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FarmExportService
{
    public function __construct(
        private readonly FarmDb $farms,
        private readonly SimpleXlsxWriter $writer,
    ) {
    }

    public function export(array $filters): BinaryFileResponse
    {
        $farms = $this->farms->allForExport(new FarmFilters($filters));

        $rows = $farms->map(fn (Farm $farm): array => [
            $farm->name,
            $farm->city,
            $farm->state,
            $farm->state_registration ?? '',
            (float) $farm->total_area,
            $farm->ruralProducer?->name ?? '',
            (int) ($farm->herds_count ?? 0),
            (int) ($farm->total_animals ?? 0),
        ])->all();

        $path = $this->writer->create(
            'Fazendas',
            [
                'Nome',
                'Cidade',
                'Estado',
                'Inscrição Estadual',
                'Área Total',
                'Produtor',
                'Quantidade de Rebanhos',
                'Total de Animais'
            ],
            $rows,
        );

        return response()
            ->download($path, 'fazendas.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true)
        ;
    }
}
