<?php

namespace App\Exceptions;

use Exception;

class MCPException extends Exception
{
    protected string $errorCode = 'MCP_ERROR';
    protected ?array $errorData = null;
    protected ?string $requestId = null;
    protected int $statusCode = 500;

    public function __construct(
        string $message = 'Erreur du protocole MCP',
        string $errorCode = 'MCP_ERROR',
        ?array $errorData = null,
        ?string $requestId = null,
        int $statusCode = 500,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->errorCode = $errorCode;
        $this->errorData = $errorData;
        $this->requestId = $requestId;
        $this->statusCode = $statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getErrorData(): ?array
    {
        return $this->errorData;
    }

    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Erreur de méthode inconnue.
     */
    public static function unknownMethod(string $method, ?string $requestId = null): self
    {
        return new self(
            "La méthode '{$method}' n'est pas reconnue par le serveur MCP.",
            'METHOD_NOT_FOUND',
            ['available_methods' => []],
            $requestId,
            404
        );
    }

    /**
     * Erreur de serveur MCP non trouvé.
     */
    public static function serverNotFound(string $serverName, ?string $requestId = null): self
    {
        return new self(
            "Le serveur MCP '{$serverName}' n'existe pas.",
            'SERVER_NOT_FOUND',
            ['available_servers' => []],
            $requestId,
            404
        );
    }

    /**
     * Erreur de paramètres invalides.
     */
    public static function invalidParams(string $details, ?array $errors = null, ?string $requestId = null): self
    {
        return new self(
            "Paramètres invalides : {$details}",
            'INVALID_PARAMS',
            ['validation_errors' => $errors],
            $requestId,
            400
        );
    }

    /**
     * Erreur d'authentification MCP.
     */
    public static function unauthorized(string $reason = 'Authentification requise.', ?string $requestId = null): self
    {
        return new self(
            $reason,
            'UNAUTHORIZED',
            null,
            $requestId,
            401
        );
    }

    /**
     * Erreur de permission insuffisante.
     */
    public static function forbidden(string $reason = 'Permission insuffisante.', ?string $requestId = null): self
    {
        return new self(
            $reason,
            'FORBIDDEN',
            null,
            $requestId,
            403
        );
    }

    /**
     * Erreur de timeout.
     */
    public static function timeout(int $timeoutSeconds, ?string $requestId = null): self
    {
        return new self(
            "Le traitement a dépassé le délai de {$timeoutSeconds} secondes.",
            'TIMEOUT',
            ['timeout_seconds' => $timeoutSeconds],
            $requestId,
            504
        );
    }
}