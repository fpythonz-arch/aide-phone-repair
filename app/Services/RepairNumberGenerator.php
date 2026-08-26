<?php

namespace App\Services;

use App\Models\RepairCounter;
use Illuminate\Support\Facades\DB;

class RepairNumberGenerator
{
    /**
     * Génère le prochain numéro de réparation (ex: REP-2026-001) de façon
     * atomique via un verrou transactionnel sur le compteur de l'année en cours.
     */
    public function next(): string
    {
        return DB::transaction(function () {
            $year = (int) now()->year;

            $counter = RepairCounter::query()
                ->lockForUpdate()
                ->firstOrCreate(['year' => $year], ['last_number' => 0]);

            $counter->increment('last_number');

            return sprintf('REP-%d-%03d', $year, $counter->last_number);
        });
    }
}
