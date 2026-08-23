<?php

namespace App\MCP\Servers;

class ToolServer
{
    public function handle(string $action, array $params): array
    {
        return match ($action) {
            'list' => $this->listTools($params),
            'execute' => $this->executeTool($params),
            'calculator' => $this->runCalculator($params),
            'converter' => $this->runConverter($params),
            default => ['error' => 'Action non supportée']
        };
    }

    private function listTools(array $params): array
    {
        $tools = [
            ['id' => 'imei_validator', 'name' => 'Validateur IMEI', 'category' => 'hardware', 'icon' => 'hash'],
            ['id' => 'battery_calculator', 'name' => 'Calculateur Batterie', 'category' => 'hardware', 'icon' => 'battery'],
            ['id' => 'resistor_decoder', 'name' => 'Décodeur Résistances', 'category' => 'hardware', 'icon' => 'git-commit'],
            ['id' => 'voltage_tester', 'name' => 'Testeur Tensions', 'category' => 'hardware', 'icon' => 'activity'],
            ['id' => 'adb_commands', 'name' => 'Commandes ADB', 'category' => 'software', 'icon' => 'terminal'],
            ['id' => 'backup_guide', 'name' => 'Guide Sauvegarde', 'category' => 'software', 'icon' => 'save'],
            ['id' => 'firmware_checker', 'name' => 'Vérificateur Firmware', 'category' => 'software', 'icon' => 'shield'],
            ['id' => 'screen_tester', 'name' => 'Testeur Écran', 'category' => 'diagnostic', 'icon' => 'smartphone'],
            ['id' => 'sensor_tester', 'name' => 'Testeur Capteurs', 'category' => 'diagnostic', 'icon' => 'radio']
        ];

        $category = $params['category'] ?? null;
        if ($category) {
            $tools = array_filter($tools, fn($t) => $t['category'] === $category);
        }

        return [
            'tools' => array_values($tools),
            'categories' => [
                ['id' => 'hardware', 'name' => 'Hardware', 'count' => 4],
                ['id' => 'software', 'name' => 'Software', 'count' => 3],
                ['id' => 'diagnostic', 'name' => 'Diagnostic', 'count' => 2]
            ]
        ];
    }

    private function executeTool(array $params): array
    {
        $toolId = $params['tool_id'] ?? '';
        $input = $params['input'] ?? [];

        return match ($toolId) {
            'imei_validator' => $this->validateIMEI($input['imei'] ?? ''),
            'battery_calculator' => $this->calculateBattery($input),
            default => ['error' => 'Outil non trouvé']
        };
    }

    private function validateIMEI(string $imei): array
    {
        $clean = preg_replace('/[^0-9]/', '', $imei);
        $valid = strlen($clean) === 15 && $this->luhnCheck($clean);

        return [
            'imei' => $clean,
            'valid' => $valid,
            'message' => $valid ? 'IMEI valide' : 'IMEI invalide'
        ];
    }

    private function luhnCheck(string $number): bool
    {
        $sum = 0;
        $alternate = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];
            if ($alternate) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
            $alternate = !$alternate;
        }
        return $sum % 10 === 0;
    }

    private function calculateBattery(array $input): array
    {
        $designCapacity = $input['design_capacity'] ?? 4000;
        $currentCapacity = $input['current_capacity'] ?? 3200;
        $cycles = $input['charge_cycles'] ?? 500;

        $healthPercent = round(($currentCapacity / $designCapacity) * 100, 1);

        return [
            'health_percentage' => $healthPercent,
            'status' => $healthPercent > 80 ? 'Bon' : ($healthPercent > 60 ? 'Moyen' : 'À remplacer'),
            'design_capacity' => $designCapacity,
            'current_capacity' => $currentCapacity,
            'charge_cycles' => $cycles,
            'recommendation' => $healthPercent < 70 ? 'Remplacement recommandé' : 'Batterie en bon état'
        ];
    }

    private function runCalculator(array $params): array
    {
        $type = $params['type'] ?? '';
        return match ($type) {
            'margin' => $this->calculateMargin($params),
            default => ['error' => 'Type de calcul non supporté']
        };
    }

    private function calculateMargin(array $params): array
    {
        $cost = $params['cost'] ?? 0;
        $price = $params['price'] ?? 0;
        $margin = $price - $cost;
        $marginPercent = $price > 0 ? round(($margin / $price) * 100, 2) : 0;

        return [
            'cost' => $cost,
            'price' => $price,
            'margin' => $margin,
            'margin_percent' => $marginPercent,
            'is_profitable' => $margin > 0
        ];
    }

    private function runConverter(array $params): array
    {
        $type = $params['type'] ?? '';
        $value = $params['value'] ?? 0;

        return match ($type) {
            'torque' => ['nm' => $value, 'in_lbs' => round($value * 8.851, 2)],
            'temperature' => ['celsius' => $value, 'fahrenheit' => round($value * 9/5 + 32, 1)],
            default => ['error' => 'Type de conversion non supporté']
        };
    }
}              