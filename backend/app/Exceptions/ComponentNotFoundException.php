<?php

namespace App\Exceptions;

use Exception;

class ComponentNotFoundException extends Exception
{
    protected ?int $componentId = null;
    protected ?array $alternatives = null;

    public function __construct(
        string $message = 'Composant non trouvé',
        ?int $componentId = null,
        ?array $alternatives = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->componentId = $componentId;
        $this->alternatives = $alternatives;
    }

    public function getComponentId(): ?int
    {
        return $this->componentId;
    }

    public function getAlternatives(): ?array
    {
        return $this->alternatives;
    }

    /**
     * Composant non trouvé par ID.
     */
    public static function byId(int $id): self
    {
        return new self(
            "Le composant avec l'ID {$id} n'existe pas.",
            $id,
            null
        );
    }

    /**
     * Composant non trouvé par slug.
     */
    public static function bySlug(string $slug): self
    {
        return new self(
            "Le composant '{$slug}' n'existe pas.",
            null,
            null
        );
    }

    /**
     * Composant incompatible avec l'appareil.
     */
    public static function incompatible(string $componentName, string $deviceModel, array $suggestedAlternatives = []): self
    {
        return new self(
            "Le composant '{$componentName}' n'est pas compatible avec '{$deviceModel}'.",
            null,
            $suggestedAlternatives
        );
    }

    /**
     * Composant en rupture de stock.
     */
    public static function outOfStock(string $componentName, array $alternatives = []): self
    {
        return new self(
            "Le composant '{$componentName}' est actuellement en rupture de stock.",
            null,
            $alternatives
        );
    }

    /**
     * Aucun composant trouvé pour les critères donnés.
     */
    public static function noMatches(array $criteria): self
    {
        return new self(
            'Aucun composant ne correspond aux critères de recherche.',
            null,
            []
        );
    }
}