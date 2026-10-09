-- ============================================================================
-- OPTIONNEL (recommandé) : verrouiller toutes les tables contre l'API publique de Supabase
--
-- Pourquoi : avec l'inscription libre, la table users contient des adresses e-mail réelles.
-- Activer la « Row Level Security » sans règle bloque tout accès via l'API publique de Supabase.
-- L'application Laravel n'est PAS concernée : elle se connecte avec le rôle « postgres »,
-- qui contourne ces règles.
--
-- À faire en dernier, une fois le lot 2 déployé et validé. Teste ensuite ta connexion.
-- Si le site affiche une erreur de base de données, exécute le bloc « ANNULER » ci-dessous.
-- ============================================================================

DO $$
DECLARE r record;
BEGIN
  FOR r IN SELECT tablename FROM pg_tables WHERE schemaname = 'public' LOOP
    EXECUTE format('ALTER TABLE public.%I ENABLE ROW LEVEL SECURITY', r.tablename);
  END LOOP;
END $$;

-- ---------------------------------------------------------------------------
-- ANNULER (uniquement si le site ne fonctionne plus après le bloc ci-dessus) :
-- DO $$
-- DECLARE r record;
-- BEGIN
--   FOR r IN SELECT tablename FROM pg_tables WHERE schemaname = 'public' LOOP
--     EXECUTE format('ALTER TABLE public.%I DISABLE ROW LEVEL SECURITY', r.tablename);
--   END LOOP;
-- END $$;
-- ---------------------------------------------------------------------------
