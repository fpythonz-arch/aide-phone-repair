-- ============================================================================
-- Lot 2 : te désigner administrateur de la PLATEFORME
-- À exécuter APRÈS lot2-ateliers.sql.
--
-- Remplace UNIQUEMENT le texte REMPLACE_PAR_TON_EMAIL (entre les apostrophes) par l'adresse
-- e-mail de ton compte, en minuscules. Ce script refuse de s'exécuter si le compte
-- n'est pas trouvé : il ne peut donc pas être lancé « à vide ».
--
-- Seuls les administrateurs de la plateforme peuvent modifier les données communes à tous
-- les ateliers (par exemple les événements d'évolution).
-- ============================================================================

DO $$
DECLARE
  n integer;
BEGIN
  UPDATE users
     SET is_platform_admin = true
   WHERE lower(email) = lower('REMPLACE_PAR_TON_EMAIL');

  GET DIAGNOSTICS n = ROW_COUNT;

  IF n <> 1 THEN
    RAISE EXCEPTION 'Compte introuvable : remplace REMPLACE_PAR_TON_EMAIL par ton vrai e-mail avant de lancer ce script.';
  END IF;
END $$;

-- Vérification : une seule ligne doit apparaître, avec ton e-mail.
SELECT id, email, role, is_platform_admin FROM users WHERE is_platform_admin = true;
