-- ============================================================================
-- Lot 2 : ateliers privés + inscription libre
-- À exécuter dans Supabase > SQL Editor, AVANT de fusionner le lot.
--
-- * Aucune valeur à remplacer dans CE script : il peut être collé tel quel.
-- * Idempotent : on peut le rejouer sans risque.
-- * Additif : il n'efface rien. L'ancienne version du site continue de fonctionner.
-- ============================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS workshops (
  id          bigserial PRIMARY KEY,
  name        varchar(255) NOT NULL,
  created_at  timestamp(0) without time zone,
  updated_at  timestamp(0) without time zone
);

ALTER TABLE users   ADD COLUMN IF NOT EXISTS workshop_id bigint REFERENCES workshops(id) ON DELETE SET NULL;
ALTER TABLE users   ADD COLUMN IF NOT EXISTS is_platform_admin boolean NOT NULL DEFAULT false;
ALTER TABLE repairs ADD COLUMN IF NOT EXISTS workshop_id bigint REFERENCES workshops(id) ON DELETE SET NULL;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS workshop_id bigint REFERENCES workshops(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS repairs_workshop_id_index ON repairs (workshop_id);
CREATE INDEX IF NOT EXISTS clients_workshop_id_index ON clients (workshop_id);

-- Les données existantes sont rattachées à un atelier « Atelier principal ».
INSERT INTO workshops (name, created_at, updated_at)
SELECT 'Atelier principal', now(), now()
WHERE NOT EXISTS (SELECT 1 FROM workshops)
  AND (EXISTS (SELECT 1 FROM users) OR EXISTS (SELECT 1 FROM repairs) OR EXISTS (SELECT 1 FROM clients));

UPDATE users   SET workshop_id = (SELECT MIN(id) FROM workshops) WHERE workshop_id IS NULL;
UPDATE repairs SET workshop_id = (SELECT MIN(id) FROM workshops) WHERE workshop_id IS NULL;
UPDATE clients SET workshop_id = (SELECT MIN(id) FROM workshops) WHERE workshop_id IS NULL;

-- Protège la nouvelle table contre l'API publique de Supabase (l'application, elle, n'est pas concernée).
ALTER TABLE workshops ENABLE ROW LEVEL SECURITY;

COMMIT;

-- Vérification : doit afficher au moins 1 atelier et 0 ligne sans atelier.
SELECT
  (SELECT count(*) FROM workshops)                            AS ateliers,
  (SELECT count(*) FROM users   WHERE workshop_id IS NULL)    AS utilisateurs_sans_atelier,
  (SELECT count(*) FROM repairs WHERE workshop_id IS NULL)    AS reparations_sans_atelier,
  (SELECT count(*) FROM clients WHERE workshop_id IS NULL)    AS clients_sans_atelier;
