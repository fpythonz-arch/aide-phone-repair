<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepannageController extends Controller
{
    /**
     * Liste toutes les catégories de dépannage (hardware/software)
     */
    public function categories(): JsonResponse
    {
        $categories = [
            // Hardware
            ['id' => 1, 'slug' => 'ecran', 'name' => "Problème d'écran", 'icon' => '📱', 'description' => 'Écran noir, lignes, tactile mort, pixels défectueux...', 'color' => '#3b82f6', 'type' => 'hardware'],
            ['id' => 2, 'slug' => 'batterie', 'name' => 'Problème de batterie', 'icon' => '🔋', 'description' => 'Ne charge pas, décharge rapide, surchauffe, gonflement...', 'color' => '#22c55e', 'type' => 'hardware'],
            ['id' => 3, 'slug' => 'charge', 'name' => 'Problème de charge', 'icon' => '🔌', 'description' => 'Connecteur loose, charge intermittente, port cassé...', 'color' => '#f59e0b', 'type' => 'hardware'],
            ['id' => 4, 'slug' => 'audio', 'name' => 'Problème audio', 'icon' => '🔊', 'description' => 'Pas de son, grésillement, micro coupé...', 'color' => '#8b5cf6', 'type' => 'hardware'],
            ['id' => 5, 'slug' => 'reseau', 'name' => 'Problème réseau', 'icon' => '📶', 'description' => 'Pas de signal, WiFi faible, Bluetooth, NFC...', 'color' => '#ef4444', 'type' => 'hardware'],
            ['id' => 6, 'slug' => 'demarrage', 'name' => 'Problème de démarrage', 'icon' => '⚡', 'description' => "Ne s'allume pas, bootloop, redémarrages intempestifs...", 'color' => '#ec4899', 'type' => 'hardware'],
            ['id' => 7, 'slug' => 'camera', 'name' => 'Problème caméra', 'icon' => '📷', 'description' => 'Caméra floue, noire, ne focus pas...', 'color' => '#06b6d4', 'type' => 'hardware'],
            ['id' => 8, 'slug' => 'boutons', 'name' => 'Boutons défectueux', 'icon' => '🔘', 'description' => 'Power, volume, home cassés ou non réactifs...', 'color' => '#f97316', 'type' => 'hardware'],
            ['id' => 9, 'slug' => 'eau', 'name' => 'Dégâts des liquides', 'icon' => '💧', 'description' => "Chute dans l'eau, oxydation, corrosion...", 'color' => '#0ea5e9', 'type' => 'hardware'],
            ['id' => 10, 'slug' => 'chauffe', 'name' => 'Chauffe excessive', 'icon' => '🌡️', 'description' => 'Téléphone très chaud, surchauffe en charge...', 'color' => '#dc2626', 'type' => 'hardware'],
            ['id' => 11, 'slug' => 'vibreur', 'name' => 'Vibreur / Moteur Taptic', 'icon' => '📳', 'description' => 'Vibreur ne fonctionne pas, bruit anormal...', 'color' => '#a855f7', 'type' => 'hardware'],
            ['id' => 12, 'slug' => 'capteurs', 'name' => 'Capteurs défectueux', 'icon' => '👆', 'description' => 'Proximité, luminosité, accéléromètre, gyroscope...', 'color' => '#14b8a6', 'type' => 'hardware'],
            ['id' => 13, 'slug' => 'carte-mere', 'name' => 'Problème carte mère', 'icon' => '🧠', 'description' => 'Court-circuit, IC power mort, baseband...', 'color' => '#be123c', 'type' => 'hardware'],
            ['id' => 14, 'slug' => 'faceid', 'name' => 'Face ID / Touch ID', 'icon' => '👁️', 'description' => 'Reconnaissance faciale non disponible...', 'color' => '#7c3aed', 'type' => 'hardware'],
            ['id' => 15, 'slug' => 'sim-sd', 'name' => 'Port SIM / SD', 'icon' => '💳', 'description' => 'SIM non détectée, port SD cassé...', 'color' => '#059669', 'type' => 'hardware'],
            
            // Software
            ['id' => 101, 'slug' => 'lenteur', 'name' => 'Smartphone lent', 'icon' => '🐌', 'description' => 'Ralentissements, freeze, applications lentes...', 'color' => '#f59e0b', 'type' => 'software'],
            ['id' => 102, 'slug' => 'plantages', 'name' => 'Applications qui plantent', 'icon' => '💥', 'description' => 'App crash, fermeture forcée, écran figé...', 'color' => '#ef4444', 'type' => 'software'],
            ['id' => 103, 'slug' => 'batterie-soft', 'name' => 'Batterie qui se vide (soft)', 'icon' => '🔋', 'description' => "Drain anormal causé par un logiciel...", 'color' => '#22c55e', 'type' => 'software'],
            ['id' => 104, 'slug' => 'mise-a-jour', 'name' => 'Mise à jour bloquée', 'icon' => '⬆️', 'description' => 'Mise à jour échoue, téléchargement bloqué...', 'color' => '#3b82f6', 'type' => 'software'],
            ['id' => 105, 'slug' => 'bootloop', 'name' => 'Bootloop / Redémarrage', 'icon' => '🔄', 'description' => 'Redémarrage en boucle, bootloop après MAJ...', 'color' => '#ec4899', 'type' => 'software'],
            ['id' => 106, 'slug' => 'ecran-noir-soft', 'name' => 'Écran noir (soft)', 'icon' => '⬛', 'description' => "Écran noir mais téléphone allumé...", 'color' => '#374151', 'type' => 'software'],
            ['id' => 107, 'slug' => 'virus', 'name' => 'Virus / Malware', 'icon' => '🦠', 'description' => 'Publicités intempestives, comportement suspect...', 'color' => '#dc2626', 'type' => 'software'],
            ['id' => 108, 'slug' => 'stockage-plein', 'name' => 'Stockage plein', 'icon' => '💾', 'description' => "Mémoire saturée, impossible d'installer...", 'color' => '#8b5cf6', 'type' => 'software'],
            ['id' => 109, 'slug' => 'wifi-4g-soft', 'name' => 'WiFi / 4G (soft)', 'icon' => '📡', 'description' => 'Connexion instable, déconnexions...', 'color' => '#06b6d4', 'type' => 'software'],
            ['id' => 110, 'slug' => 'compte-bloque', 'name' => 'Compte bloqué', 'icon' => '🔒', 'description' => 'Google FRP, Apple ID, iCloud, MDM...', 'color' => '#f97316', 'type' => 'software'],
            ['id' => 111, 'slug' => 'restauration', 'name' => 'Restauration échoue', 'icon' => '🏭', 'description' => 'Factory reset échoue, erreur lors du formatage...', 'color' => '#64748b', 'type' => 'software'],
            ['id' => 112, 'slug' => 'notifications', 'name' => 'Notifications', 'icon' => '🔔', 'description' => 'Notifications qui ne marchent pas...', 'color' => '#14b8a6', 'type' => 'software'],
            ['id' => 113, 'slug' => 'clavier', 'name' => 'Clavier / Saisie', 'icon' => '⌨️', 'description' => 'Clavier qui bug, autocorrect fou...', 'color' => '#a855f7', 'type' => 'software'],
            ['id' => 114, 'slug' => 'appareil-photo-soft', 'name' => 'Appareil photo (soft)', 'icon' => '📷', 'description' => 'App photo qui plante, écran noir en photo...', 'color' => '#0ea5e9', 'type' => 'software'],
            ['id' => 115, 'slug' => 'bluetooth-soft', 'name' => 'Bluetooth (soft)', 'icon' => '🔵', 'description' => 'Paire impossible, déconnexions...', 'color' => '#3b82f6', 'type' => 'software'],
            ['id' => 116, 'slug' => 'gps-soft', 'name' => 'GPS imprécis (soft)', 'icon' => '📍', 'description' => "Position erronée, GPS qui ne fixe pas...", 'color' => '#22c55e', 'type' => 'software'],
        ];

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Détail d'une catégorie de dépannage avec son guide
     */
    public function show(string $type): JsonResponse
    {
        $guides = [
            'ecran' => [
                'id' => 1,
                'category' => ['id' => 1, 'slug' => 'ecran', 'name' => "Problème d'écran", 'icon' => '📱', 'description' => 'Écran noir, lignes, tactile mort...', 'color' => '#3b82f6', 'type' => 'hardware'],
                'symptoms' => ['Écran noir', 'Lignes verticales/horizontales', 'Tactile non réactif', 'Pixels morts', 'Flickering/scintillement'],
                'commonCauses' => ['Chute ou impact', 'Pression excessive', 'Contact avec liquide', 'Usure connecteur FPC'],
                'steps' => [
                    ['id' => 1, 'order' => 1, 'title' => 'Redémarrage forcé', 'description' => 'Avant tout démontage, écartez un bug logiciel.', 'instruction' => 'Maintenez Power + Volume Bas 10-15s.', 'warning' => 'Ne pas confondre avec un simple redémarrage.', 'checkItems' => [['id' => 1, 'label' => 'Le téléphone vibre au démarrage', 'checked' => false], ['id' => 2, 'label' => 'Le logo apparaît', 'checked' => false]], 'tools' => [], 'estimatedTime' => 2],
                    ['id' => 2, 'order' => 2, 'title' => 'Vérification connecteur FPC', 'description' => 'Le connecteur peut se déloger.', 'instruction' => '1. Éteignez. 2. Retirez les vis. 3. Débranchez batterie FIRST. 4. Vérifiez le connecteur FPC.', 'warning' => 'DÉBRANCHEZ TOUJOURS LA BATTERIE AVANT.', 'checkItems' => [['id' => 3, 'label' => 'Connecteur bien en place', 'checked' => false], ['id' => 4, 'label' => 'Pas d\'oxydation', 'checked' => false]], 'tools' => ['Ventouse', 'Spudger', 'Tournevis Pentalobe'], 'estimatedTime' => 15],
                ],
                'solutions' => [
                    ['id' => 1, 'title' => 'Remplacement écran complet', 'description' => 'Écran physiquement endommagé. Remplacement obligatoire.', 'difficulty' => 'hard', 'estimatedCost' => 85, 'needsReplacement' => true, 'replacementPart' => 'Écran OLED/LCD complet', 'guideUrl' => null],
                    ['id' => 2, 'title' => 'Réparation connecteur FPC', 'description' => 'Connecteur oxydé ou nappe pliée.', 'difficulty' => 'expert', 'estimatedCost' => 45, 'needsReplacement' => false, 'guideUrl' => null],
                ],
            ],
            'batterie' => [
                'id' => 2,
                'category' => ['id' => 2, 'slug' => 'batterie', 'name' => 'Problème de batterie', 'icon' => '🔋', 'description' => 'Ne charge pas, décharge rapide...', 'color' => '#22c55e', 'type' => 'hardware'],
                'symptoms' => ['Ne charge pas', 'Décharge rapide', 'Surchauffe', 'Gonflement', 'Pourcentage erratique'],
                'commonCauses' => ['Usure normale', 'Chargeur non original', 'Surchauffe', 'Défaut logiciel'],
                'steps' => [
                    ['id' => 10, 'order' => 1, 'title' => 'Vérification chargeur', 'description' => 'Écartez un problème de chargeur.', 'instruction' => 'Testez avec un chargeur original. Nettoyez le port.', 'warning' => 'N\'utilisez JAMAIS de chargeur endommagé.', 'checkItems' => [['id' => 11, 'label' => 'Câble OK sur autre appareil', 'checked' => false]], 'tools' => ['Aiguille', 'Brosse douce'], 'estimatedTime' => 5],
                ],
                'solutions' => [
                    ['id' => 4, 'title' => 'Remplacement batterie', 'description' => 'Batterie usée. Remplacement obligatoire.', 'difficulty' => 'medium', 'estimatedCost' => 35, 'needsReplacement' => true, 'replacementPart' => 'Batterie Li-Ion', 'guideUrl' => null],
                ],
            ],
        ];

        $guide = $guides[$type] ?? null;

        if (!$guide) {
            // Retourner une réponse générique pour les types non encore implémentés
            $allCategories = $this->categories()->getData(true)['data'];
            $category = collect($allCategories)->firstWhere('slug', $type);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Type de dépannage non trouvé',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $category['id'],
                    'category' => $category,
                    'symptoms' => [],
                    'commonCauses' => [],
                    'steps' => [],
                    'solutions' => [],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $guide,
        ]);
    }
}