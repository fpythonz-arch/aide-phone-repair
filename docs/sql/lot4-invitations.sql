-- ============================================================================
-- Lot 4 : invitations de techniciens
-- À exécuter dans Supabase > SQL Editor, AVANT de fusionner le lot.
--
-- * Aucune valeur à remplacer : à coller tel quel.
-- * Idempotent : peut être rejoué sans risque.
-- * Additif : ne modifie ni ne supprime aucune donnée existante.
-- ============================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS workshop_invitations (
  id           bigserial PRIMARY KEY,
  workshop_id  bigint NOT NULL REFERENCES workshops(id) ON DELETE CASCADE,
  token_hash   varchar(64) NOT NULL UNIQUE,
  role         varchar(255) NOT NULL,
  email        varchar(255),
  created_by   bigint REFERENCES users(id) ON DELETE SET NULL,
  accepted_by  bigint REFERENCES users(id) ON DELETE SET NULL,
  expires_at   timestamp(0) without time zone NOT NULL,
  accepted_at  timestamp(0) without time zone,
  revoked_at   timestamp(0) without time zone,
  created_at   timestamp(0) without time zone,
  updated_at   timestamp(0) without time zone
);

CREATE INDEX IF NOT EXISTS workshop_invitations_workshop_id_index ON workshop_invitations (workshop_id);

-- Protège la nouvelle table contre l'API publique de Supabase (l'application n'est pas concernée).
ALTER TABLE workshop_invitations ENABLE ROW LEVEL SECURITY;

COMMIT;

-- Vérification : doit afficher 0 (aucune invitation pour l'instant).
SELECT count(*) AS invitations FROM workshop_invitations;
