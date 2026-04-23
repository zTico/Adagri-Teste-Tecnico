<?php

namespace App\Services;

use App\Models\Herd;

class HerdService
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Herd
    {
        return Herd::create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Herd $herd, array $data): Herd
    {
        $herd->update($data);

        return $herd->refresh();
    }
}
