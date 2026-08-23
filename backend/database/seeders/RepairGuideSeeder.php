<?php

namespace Database\Seeders;

use App\Models\RepairGuide;
use App\Models\Symptom;
use App\Models\Component;
use Illuminate\Database\Seeder;

class RepairGuideSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer quelques symptômes et composants existants
        $symptoms = Symptom::all();
        $components = Component::all();

        if ($symptoms->isEmpty() || $components->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter SymptomSeeder et ComponentSeeder.');
            return;
        }

        $guides = [
            [
                'symptom_id' => $symptoms->where('category', 'display')->first()?->id,
                'component_id' => $components->where('category', 'display')->first()?->id,
                'title' => 'Remplacement complet de l\'écran LCD + Tactile',
                'description' => 'Guide complet pour remplacer l\'ensemble écran sur un smartphone moderne. Nécessite un kit d\'outils de précision.',
                'difficulty_level' => 3,
                'estimated_time_minutes' => 45,
                'required_tools' => [
                    'Tournevis cruciforme PH00',
                    'Tournevis pentalobe P2',
                    'Ventouse démontage écran',
                    'Spudger en plastique',
                    'Pince à épiler ESD',
                    'Sèche-cheveux ou station air chaud',
                    'Tweezers de précision',
                ],
                'required_parts' => [
                    ['name' => 'Dalle LCD complète', 'quantity' => 1, 'optional' => false],
                    ['name' => 'Adhésif double face 3M', 'quantity' => 1, 'optional' => false],
                    ['name' => 'Joint d\'étanchéité', 'quantity' => 1, 'optional' => true],
                ],
                'steps' => [
                    [
                        'order' => 1,
                        'title' => 'Préparation et sécurité',
                        'description' => 'Éteignez complètement l\'appareil. Retirez la carte SIM et le tiroir. Placez-vous sur une surface ESD.',
                        'image_url' => null,
                        'estimated_time' => 5,
                    ],
                    [
                        'order' => 2,
                        'title' => 'Retrait des vis inférieures',
                        'description' => 'Utilisez le tournevis pentalobe P2 pour retirer les 2 vis de chaque côté du connecteur de charge.',
                        'image_url' => null,
                        'estimated_time' => 3,
                    ],
                    [
                        'order' => 3,
                        'title' => 'Décollement de l\'écran',
                        'description' => 'Chauffez les bords de l\'écran avec le sèche-cheveux (2-3 minutes). Posez la ventouse sur l\'écran et tirez doucement tout en insérant le spudger.',
                        'image_url' => null,
                        'estimated_time' => 10,
                    ],
                    [
                        'order' => 4,
                        'title' => 'Déconnexion de la batterie',
                        'description' => 'Retirez le cache métallique des nappes avec le tournevis cruciforme. Débranchez TOUJOURS la batterie en premier pour éviter les courts-circuits.',
                        'image_url' => null,
                        'estimated_time' => 5,
                    ],
                    [
                        'order' => 5,
                        'title' => 'Déconnexion des nappes de l\'écran',
                        'description' => 'Débranchez délicatement les 3 nappes de l\'écran (LCD, tactile, capteur proximité) avec le spudger. Ne forcez pas sur les connecteurs.',
                        'image_url' => null,
                        'estimated_time' => 5,
                    ],
                    [
                        'order' => 6,
                        'title' => 'Retrait de l\'ancien adhésif',
                        'description' => 'Nettoyez complètement le châssis de l\'ancien adhésif avec de l\'alcool isopropylique 99%. Le châssis doit être parfaitement propre.',
                        'image_url' => null,
                        'estimated_time' => 8,
                    ],
                    [
                        'order' => 7,
                        'title' => 'Installation du nouvel écran',
                        'description' => 'Posez le nouvel adhésif 3M sur le châssis. Connectez les 3 nappes de l\'écran neuf. Rebranchez la batterie. Testez l\'écran AVANT de coller définitivement.',
                        'image_url' => null,
                        'estimated_time' => 10,
                    ],
                    [
                        'order' => 8,
                        'title' => 'Test et remontage',
                        'description' => 'Allumez l\'appareil et testez : affichage, tactile, proximité, luminosité, caméra frontale. Si tout fonctionne, appuyez fermement sur l\'écran et revissez.',
                        'image_url' => null,
                        'estimated_time' => 7,
                    ],
                ],
                'warnings' => [
                    'Ne forcez jamais sur les nappes flexibles - elles se déchirent facilement',
                    'Déconnectez TOUJOURS la batterie avant toute manipulation',
                    'Faites attention aux petites vis de tailles différentes',
                    'Ne perdez pas les joints d\'étanchéité si vous voulez garder la résistance à l\'eau',
                    'Testez l\'écran neuf avant de coller définitivement',
                ],
                'video_url' => 'https://www.youtube.com/watch?v=example-screen-replacement',
                'image_urls' => [
                    'https://example.com/guides/screen-step1.jpg',
                    'https://example.com/guides/screen-step4.jpg',
                    'https://example.com/guides/screen-step7.jpg',
                ],
                'success_rate' => 87.5,
                'view_count' => 3420,
                'is_published' => true,
            ],
            [
                'symptom_id' => $symptoms->where('category', 'battery')->first()?->id,
                'component_id' => $components->where('category', 'battery')->first()?->id,
                'title' => 'Remplacement de la batterie Li-Ion',
                'description' => 'Guide pour remplacer une batterie usée ou gonflée. Attention aux batteries gonflées qui sont dangereuses.',
                'difficulty_level' => 2,
                'estimated_time_minutes' => 30,
                'required_tools' => [
                    'Tournevis cruciforme PH00',
                    'Tournevis pentalobe P2',
                    'Spudger en plastique',
                    'Pince à épiler',
                    'Sèche-cheveux',
                    'Carte plastique (type carte bancaire)',
                ],
                'required_parts' => [
                    ['name' => 'Batterie Li-Ion compatible', 'quantity' => 1, 'optional' => false],
                    ['name' => 'Adhésif batterie', 'quantity' => 1, 'optional' => false],
                    ['name' => 'Joints adhésifs écran', 'quantity' => 1, 'optional' => true],
                ],
                'steps' => [
                    [
                        'order' => 1,
                        'title' => 'Vérification de sécurité',
                        'description' => 'Si la batterie est gonflée : ne la percez PAS, ne la pliez PAS. Portez des lunettes de protection. Travaillez dans un endroit ventilé.',
                        'image_url' => null,
                        'estimated_time' => 3,
                    ],
                    [
                        'order' => 2,
                        'title' => 'Accès à la batterie',
                        'description' => 'Suivez les étapes 1 à 4 du guide "Remplacement d\'écran" pour ouvrir l\'appareil et déconnecter la batterie.',
                        'image_url' => null,
                        'estimated_time' => 15,
                    ],
                    [
                        'order' => 3,
                        'title' => 'Retrait de l\'ancienne batterie',
                        'description' => 'Tirez doucement sur les languettes d\'adhésif (si présentes). Si elles se cassent, utilisez la carte plastique pour décoller l\'adhésif. Ne percez jamais la batterie.',
                        'image_url' => null,
                        'estimated_time' => 10,
                    ],
                    [
                        'order' => 4,
                        'title' => 'Installation de la nouvelle batterie',
                        'description' => 'Retirez le film de protection de l\'adhésif neuf. Positionnez la batterie correctement (connecteur vers le haut). Appuyez fermement. Connectez la nappe.',
                        'image_url' => null,
                        'estimated_time' => 5,
                    ],
                    [
                        'order' => 5,
                        'title' => 'Test et remontage',
                        'description' => 'Allumez l\'appareil. Vérifiez le pourcentage de batterie. Laissez charger 30 minutes. Vérifiez qu\'il ne chauffe pas anormalement.',
                        'image_url' => null,
                        'estimated_time' => 5,
                    ],
                ],
                'warnings' => [
                    '⚠️ BATTERIE GONFLÉE = DANGER D\'INCENDIE',
                    'Ne percez JAMAIS une batterie au lithium',
                    'Ne pliez pas ou ne tordz pas la batterie',
                    'Recyclez l\'ancienne batterie en déchetterie spécialisée',
                    'Si la batterie chauffe pendant la charge, débranchez immédiatement',
                ],
                'video_url' => null,
                'image_urls' => [
                    'https://example.com/guides/battery-safety.jpg',
                    'https://example.com/guides/battery-removal.jpg',
                ],
                'success_rate' => 92.0,
                'view_count' => 5180,
                'is_published' => true,
            ],
            [
                'symptom_id' => $symptoms->where('category', 'charging')->first()?->id,
                'component_id' => $components->where('category', 'charging_port')->first()?->id,
                'title' => 'Remplacement du connecteur de charge USB-C',
                'description' => 'Remplacement de la nappe complète du connecteur de charge avec micros intégrés.',
                'difficulty_level' => 3,
                'estimated_time_minutes' => 40,
                'required_tools' => [
                    'Tournevis cruciforme PH00',
                    'Tournevis pentalobe P2',
                    'Spudger en plastique',
                    'Pince à épiler',
                    'Ventouse',
                    'Sèche-cheveux',
                ],
                'required_parts' => [
                    ['name' => 'Nappe connecteur de charge USB-C', 'quantity' => 1, 'optional' => false],
                    ['name' => 'Adhésif nappe', 'quantity' => 1, 'optional' => false],
                ],
                'steps' => [
                    [
                        'order' => 1,
                        'title' => 'Démontage initial',
                        'description' => 'Ouvrez l\'appareil en suivant les étapes du guide écran. Déconnectez la batterie.',
                        'image_url' => null,
                        'estimated_time' => 15,
                    ],
                    [
                        'order' => 2,
                        'title' => 'Accès au connecteur de charge',
                        'description' => 'Retirez le haut-parleur principal et le Taptic Engine pour accéder à la nappe du connecteur de charge.',
                        'image_url' => null,
                        'estimated_time' => 10,
                    ],
                    [
                        'order' => 3,
                        'title' => 'Retrait de l\'ancienne nappe',
                        'description' => 'Déconnectez la nappe de la carte mère. Retirez les vis de fixation du connecteur. Décollez délicatement la nappe du châssis.',
                        'image_url' => null,
                        'estimated_time' => 8,
                    ],
                    [
                        'order' => 4,
                        'title' => 'Installation de la nouvelle nappe',
                        'description' => 'Positionnez la nouvelle nappe. Vissez le connecteur. Reconnectez à la carte mère. Testez la charge avant remontage complet.',
                        'image_url' => null,
                        'estimated_time' => 10,
                    ],
                    [
                        'order' => 5,
                        'title' => 'Remontage et test final',
                        'description' => 'Remontez le Taptic Engine, le haut-parleur, puis l\'écran. Testez la charge, le transfert de données et le micro.',
                        'image_url' => null,
                        'estimated_time' => 10,
                    ],
                ],
                'warnings' => [
                    'Les micros sont intégrés à la nappe - faites attention',
                    'Vérifiez l\'alignement du connecteur dans le châssis',
                    'Testez la charge rapide après remplacement',
                ],
                'video_url' => null,
                'image_urls' => [],
                'success_rate' => 85.0,
                'view_count' => 2100,
                'is_published' => true,
            ],
            [
                'symptom_id' => $symptoms->where('category', 'audio')->where('name', 'like', '%haut-parleur%')->first()?->id,
                'component_id' => $components->where('category', 'speaker')->first()?->id,
                'title' => 'Remplacement du haut-parleur principal',
                'description' => 'Remplacement du haut-parleur de sonnerie et média. Opération simple et rapide.',
                'difficulty_level' => 1,
                'estimated_time_minutes' => 15,
                'required_tools' => [
                    'Tournevis cruciforme PH00',
                    'Spudger en plastique',
                    'Pince à épiler',
                ],
                'required_parts' => [
                    ['name' => 'Haut-parleur principal', 'quantity' => 1, 'optional' => false],
                ],
                'steps' => [
                    [
                        'order' => 1,
                        'title' => 'Accès au haut-parleur',
                        'description' => 'Ouvrez l\'appareil (voir guide écran). Le haut-parleur est visible en bas à droite.',
                        'image_url' => null,
                        'estimated_time' => 8,
                    ],
                    [
                        'order' => 2,
                        'title' => 'Retrait de l\'ancien haut-parleur',
                        'description' => 'Retirez les 2 vis de fixation. Soulevez délicatement avec le spudger. Déconnectez la nappe.',
                        'image_url' => null,
                        'estimated_time' => 3,
                    ],
                    [
                        'order' => 3,
                        'title' => 'Installation du nouveau',
                        'description' => 'Connectez la nappe. Positionnez le haut-parleur dans son logement. Vissez. Testez le son avant de refermer.',
                        'image_url' => null,
                        'estimated_time' => 4,
                    ],
                ],
                'warnings' => [
                    'Vérifiez que les contacts sont propres',
                    'Ne confondez pas avec l\'écouteur interne (en haut)',
                ],
                'video_url' => null,
                'image_urls' => [],
                'success_rate' => 95.0,
                'view_count' => 1890,
                'is_published' => true,
            ],
            [
                'symptom_id' => $symptoms->where('category', 'connectivity')->where('name', 'like', '%WiFi%')->first()?->id,
                'component_id' => $components->where('category', 'antenna')->where('sub_category', 'wifi_antenna')->first()?->id,
                'title' => 'Remplacement de l\'antenne WiFi / Bluetooth',
                'description' => 'Remplacement de la nappe antenne pour restaurer la connectivité sans fil.',
                'difficulty_level' => 2,
                'estimated_time_minutes' => 20,
                'required_tools' => [
                    'Tournevis cruciforme PH00',
                    'Spudger en plastique',
                    'Pince à épiler',
                    'Ventouse',
                ],
                'required_parts' => [
                    ['name' => 'Nappe antenne WiFi/Bluetooth', 'quantity' => 1, 'optional' => false],
                ],
                'steps' => [
                    [
                        'order' => 1,
                        'title' => 'Accès à l\'antenne',
                        'description' => 'Ouvrez l\'appareil. L\'antenne WiFi est généralement en haut à gauche, fixée sur le châssis.',
                        'image_url' => null,
                        'estimated_time' => 10,
                    ],
                    [
                        'order' => 2,
                        'title' => 'Remplacement de la nappe',
                        'description' => 'Déconnectez la nappe du connecteur sur la carte mère. Retirez l\'ancienne nappe. Collez la nouvelle. Reconnectez.',
                        'image_url' => null,
                        'estimated_time' => 7,
                    ],
                    [
                        'order' => 3,
                        'title' => 'Test de connectivité',
                        'description' => 'Allumez l\'appareil. Testez le WiFi à différentes distances. Testez le Bluetooth avec un périphérique.',
                        'image_url' => null,
                        'estimated_time' => 5,
                    ],
                ],
                'warnings' => [
                    'Ne pliez pas excessivement la nappe antenne',
                    'Vérifiez que le connecteur est bien enclenché',
                ],
                'video_url' => null,
                'image_urls' => [],
                'success_rate' => 88.0,
                'view_count' => 980,
                'is_published' => true,
            ],
        ];

        foreach ($guides as $guide) {
            if ($guide['symptom_id'] && $guide['component_id']) {
                RepairGuide::create($guide);
            }
        }

        // Compléter avec des guides aléatoires
    // RepairGuide::factory()->count(15)->create([
    //     'difficulty' => null,
    //     'estimated_time' => null,
    // ]);
    }
}