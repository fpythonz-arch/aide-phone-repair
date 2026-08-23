<?php

namespace App\Exceptions;

use Exception;

class DiagnosticException extends Exception
{
    protected ?string $step = null;
    protected ?string $suggestion = null;
    protected int $statusCode = 422;

    public function __construct(
        string $message = 'Erreur de diagnostic',
        ?string $step = null,
        ?string $suggestion = null,
        int $statusCode = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->step = $step;
        $this->suggestion = $suggestion;
        $this->statusCode = $statusCode;
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function getSuggestion(): ?string
    {
        return $this->suggestion;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Erreur de symptôme non trouvé.
     */
    public static function symptomNotFound(array $inputs): self
    {
        return new self(
            'Aucun symptôme correspondant trouvé dans notre base de données.',
            'symptom_identification',
            'Essayez de décrire le problème avec d\'autres mots-clés ou consultez la liste des symptômes connus.',
            404
        );
    }

    /**
     * Erreur de session de diagnostic invalide.
     */
    public static function invalidSession(string $sessionId): self
    {
        return new self(
            "La session de diagnostic '{$sessionId}' est invalide ou a expiré.",
            'session_validation',
            'Veuillez réinitialiser le diagnostic avec les informations de votre appareil.',
            410
        );
    }

    /**
     * Erreur de sévérité incohérente.
     */
    public static function inconsistentSeverity(): self
    {
        return new self(
            'Les niveaux de sévérité fournis sont incohérents.',
            'severity_assessment',
            'Vérifiez que tous les niveaux de sévérité sont entre 1 et 5.',
            422
        );
    }

    /**
     * Erreur de composant non mappable.
     */
    public static function unmappedComponents(array $symptomIds): self
    {
        return new self(
            'Impossible de mapper des composants pour les symptômes fournis.',
            'component_mapping',
            'Ces symptômes sont rares ou non documentés. Contactez un professionnel.',
            404
        );
    }

    /**
     * Erreur de validation échouée.
     */
    public static function validationFailed(array $errors): self
    {
        return new self(
            'La validation du diagnostic a échoué.',
            'validation',
            'Vérifiez les résultats de vos tests et réessayez.',
            422
        );
    }

    /**
     * Erreur de données insuffisantes.
     */
    public static function insufficientData(string $missingField): self
    {
        return new self(
            "Données insuffisantes : le champ '{$missingField}' est requis.",
            'data_input',
            "Veuillez fournir le champ '{$missingField}' pour continuer.",
            400
        );
    }
}