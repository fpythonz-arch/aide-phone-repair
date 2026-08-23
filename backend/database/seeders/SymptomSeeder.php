<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    public function run(): void
    {
        $symptoms = [
            // Display
            [
                'name' => 'Écran noir / ne s\'allume pas',
                'description' => 'L\'appareil semble allumé (vibre, sonne) mais l\'écran reste noir.',
                'category' => 'display',
                'severity_level' => 5,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['écran noir', 'no display', 'backlight', 'LCD mort'],
            ],
            [
                'name' => 'Lignes verticales sur l\'écran',
                'description' => 'Des lignes verticales de couleur apparaissent sur l\'affichage.',
                'category' => 'display',
                'severity_level' => 4,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['lignes', 'artefacts', 'display', 'écran cassé'],
            ],
            [
                'name' => 'Écran tactile qui ne répond pas',
                'description' => 'L\'affichage fonctionne mais le tactile ne réagit pas au doigt.',
                'category' => 'display',
                'severity_level' => 4,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['touch', 'tactile', 'digitizer', 'non réactif'],
            ],
            [
                'name' => 'Taches sombres / pixels morts',
                'description' => 'Zones sombres ou points noirs visibles sur l\'écran.',
                'category' => 'display',
                'severity_level' => 2,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['dead pixel', 'tache', 'LCD', 'défaut'],
            ],

            // Battery
            [
                'name' => 'Batterie qui se décharge très vite',
                'description' => 'La batterie perd plus de 50% en quelques heures sans usage intensif.',
                'category' => 'battery',
                'severity_level' => 3,
                'common_devices' => ['smartphone', 'tablet', 'smartwatch', 'laptop'],
                'keywords' => ['drain', 'autonomie', 'décharge', 'chaude'],
            ],
            [
                'name' => 'Batterie qui gonfle',
                'description' => 'La batterie est visiblement déformée, l\'écran est soulevé.',
                'category' => 'battery',
                'severity_level' => 5,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['gonflement', 'swelling', 'dangereux', 'feu'],
            ],
            [
                'name' => 'Appareil qui s\'éteint à 20-30%',
                'description' => 'Le téléphone s\'éteint brusquement alors qu\'il affiche encore du pourcentage.',
                'category' => 'battery',
                'severity_level' => 3,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['shutdown', 'calibration', 'tension', 'vieux'],
            ],

            // Charging
            [
                'name' => 'Ne charge plus du tout',
                'description' => 'Aucun signe de charge même avec plusieurs câbles et chargeurs.',
                'category' => 'charging',
                'severity_level' => 5,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['charge', 'USB', 'connecteur', '0%'],
            ],
            [
                'name' => 'Charge intermittente / se déconnecte',
                'description' => 'La charge s\'active et se désactive constamment en bougeant le câble.',
                'category' => 'charging',
                'severity_level' => 3,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['intermittent', 'mauvais contact', 'câble', 'loose'],
            ],
            [
                'name' => 'Charge très lente',
                'description' => 'Met plusieurs heures pour atteindre 50% même avec chargeur rapide.',
                'category' => 'charging',
                'severity_level' => 2,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['lent', 'slow charge', 'ampérage', 'optimisation'],
            ],

            // Audio
            [
                'name' => 'Pas de son du haut-parleur',
                'description' => 'Aucun son ne sort du haut-parleur principal lors des appels ou médias.',
                'category' => 'audio',
                'severity_level' => 3,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['speaker', 'mute', 'silencieux', 'audio'],
            ],
            [
                'name' => 'Son distordu / grésillant',
                'description' => 'Le son est présent mais de mauvaise qualité, grésille ou craque.',
                'category' => 'audio',
                'severity_level' => 2,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['distorsion', 'grésillement', 'buzz', 'qualité'],
            ],
            [
                'name' => 'Écouteur interne inaudible',
                'description' => 'Impossible d\'entendre l\'interlocuteur lors des appels.',
                'category' => 'audio',
                'severity_level' => 4,
                'common_devices' => ['smartphone'],
                'keywords' => ['earpiece', 'appel', 'voix', 'inaudible'],
            ],

            // Connectivity
            [
                'name' => 'WiFi qui se déconnecte constamment',
                'description' => 'La connexion WiFi tombe et se reconnecte en permanence.',
                'category' => 'connectivity',
                'severity_level' => 3,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['WiFi', 'déconnexion', 'réseau', 'instable'],
            ],
            [
                'name' => 'Pas de réseau mobile / "Pas de service"',
                'description' => 'L\'appareil ne détecte aucun opérateur, impossible d\'appeler.',
                'category' => 'connectivity',
                'severity_level' => 5,
                'common_devices' => ['smartphone'],
                'keywords' => ['SIM', 'réseau', 'antenne', 'baseband'],
            ],
            [
                'name' => 'Bluetooth non détectable',
                'description' => 'L\'appareil n\'apparaît pas ou ne détecte pas les périphériques Bluetooth.',
                'category' => 'connectivity',
                'severity_level' => 2,
                'common_devices' => ['smartphone', 'tablet', 'smartwatch', 'laptop'],
                'keywords' => ['Bluetooth', 'appairage', 'détection', 'wireless'],
            ],

            // Performance
            [
                'name' => 'Lenteur extrême / freezes',
                'description' => 'L\'appareil est très lent, les applications mettent plusieurs secondes à répondre.',
                'category' => 'performance',
                'severity_level' => 3,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['lent', 'freeze', 'lag', 'RAM pleine'],
            ],
            [
                'name' => 'Redémarrages spontanés',
                'description' => 'L\'appareil redémarre tout seul sans raison apparente.',
                'category' => 'performance',
                'severity_level' => 4,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['reboot', 'redémarrage', 'kernel panic', 'aléatoire'],
            ],
            [
                'name' => 'Surchauffe anormale',
                'description' => 'L\'appareil devient brûlant même sans usage intensif.',
                'category' => 'performance',
                'severity_level' => 4,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['chaud', 'overheating', 'température', 'processeur'],
            ],

            // Camera
            [
                'name' => 'Appareil photo flou / ne fait pas le focus',
                'description' => 'Les photos sont floues, le focus ne fonctionne pas correctement.',
                'category' => 'camera',
                'severity_level' => 2,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['focus', 'flou', 'camera', 'lentille'],
            ],
            [
                'name' => 'Caméra qui ne s\'ouvre pas',
                'description' => 'L\'application caméra plante ou affiche un écran noir.',
                'category' => 'camera',
                'severity_level' => 3,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['crash', 'caméra', 'appareil photo', 'module'],
            ],

            // Buttons
            [
                'name' => 'Bouton Power non fonctionnel',
                'description' => 'Le bouton d\'allumage ne répond plus ou est enfoncé.',
                'category' => 'buttons',
                'severity_level' => 4,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['power', 'allumage', 'bouton', 'flex'],
            ],
            [
                'name' => 'Boutons volume inactifs',
                'description' => 'Les boutons de volume +/- ne changent plus le son.',
                'category' => 'buttons',
                'severity_level' => 2,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['volume', 'bouton', 'flex', 'contrôle'],
            ],

            // Water damage
            [
                'name' => 'Chute dans l\'eau / oxydation',
                'description' => 'L\'appareil est tombé dans l\'eau ou présente des traces d\'oxydation.',
                'category' => 'water_damage',
                'severity_level' => 5,
                'common_devices' => ['smartphone', 'tablet', 'smartwatch', 'laptop'],
                'keywords' => ['eau', 'oxydation', 'corrosion', 'humidité', 'IP68'],
            ],

            // Software
            [
                'name' => 'Bloqué sur le logo / Bootloop',
                'description' => 'L\'appareil redémarre en boucle sur le logo du fabricant.',
                'category' => 'software',
                'severity_level' => 4,
                'common_devices' => ['smartphone', 'tablet'],
                'keywords' => ['bootloop', 'logo', 'système', 'OS corrompu'],
            ],
            [
                'name' => 'Applications qui plantent',
                'description' => 'Les applications se ferment brutalement sans message d\'erreur.',
                'category' => 'software',
                'severity_level' => 2,
                'common_devices' => ['smartphone', 'tablet', 'laptop'],
                'keywords' => ['crash', 'fermeture', 'application', 'bug'],
            ],
        ];

        foreach ($symptoms as $symptom) {
            Symptom::create($symptom);
        }

        // Compléter avec des données aléatoires
        Symptom::factory()->count(15)->create();
    }
}