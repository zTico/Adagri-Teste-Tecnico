<?php

namespace App\Services\Exports;

use App\Models\Farm;
use App\QueryFilters\FarmFilters;
use App\Support\Exports\SimpleXlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FarmExportService
{
    public function __construct(
        private readonly SimpleXlsxWriter $writer,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function export(array $filters): BinaryFileResponse
    {
        $farms = (new FarmFilters($filters))
            ->apply(
                Farm::query()
                    ->with(['ruralProducer:id,name'])
                    ->withCount('herds')
                    ->withSum('herds as total_animals', 'quantity')
                    ->orderBy('name')
            )
            ->get()
        ;

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
            'Farms',
            ['Name', 'City', 'State', 'State Registration', 'Total Area', 'Producer', 'Herd Count', 'Total Animals'],
            $rows,
        );

        return response()
            ->download($path, 'farms.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true)
        ;
    }
}
