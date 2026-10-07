<?php

return [
    /*
    | Durée de vie des tokens d'accès, en minutes (défaut : 7 jours).
    | Les autres options Sanctum gardent leurs valeurs par défaut (fusion de config).
    */
    'expiration' => (int) env('SANCTUM_EXPIRATION', 10080),
];
