<?php

namespace App\Services;

use App\Models\Client;

class ClientResolver
{
    /**
     * Retrouve un client existant par numéro de téléphone normalisé, ou en crée un nouveau.
     * Ne modifie jamais le nom/email d'un client déjà connu (une coquille sur un ticket
     * ne doit pas réécrire silencieusement l'historique du client).
     */
    public function resolve(string $name, string $phone, ?string $email = null): Client
    {
        $normalized = $this->normalize($phone);

        return Client::query()->firstOrCreate(
            ['phone_normalized' => $normalized],
            ['name' => $name, 'phone' => $phone, 'email' => $email]
        );
    }

    protected function normalize(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
