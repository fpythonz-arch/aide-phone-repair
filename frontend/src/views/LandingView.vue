<template>
  <div class="landing">

    <!-- Nav -->
    <header class="landing-nav">
      <div class="landing-nav-inner">
        <div class="landing-logo">
          <div class="landing-logo-icon"><WrenchScrewdriverIcon class="w-4 h-4 text-white" /></div>
          <span>Aide<strong>Phone</strong></span>
        </div>
        <router-link to="/login" class="btn btn-primary btn-sm">Se connecter</router-link>
      </div>
    </header>

    <!-- Hero -->
    <section class="landing-hero">
      <div class="landing-hero-inner">
        <span class="landing-eyebrow">Plateforme de gestion d'atelier</span>
        <h1 class="landing-title">
          Diagnostiquez, réparez et suivez chaque appareil<br class="hidden sm:block" />
          sans perdre le fil.
        </h1>
        <p class="landing-subtitle">
          AidePhone centralise le diagnostic guidé, le suivi des réparations, les codes secrets
          et la base de composants dont un atelier de réparation électronique a besoin au quotidien —
          pensé pour les techniciens africains.
        </p>
        <div class="landing-hero-actions">
          <router-link to="/login" class="btn btn-primary btn-lg">
            Se connecter <ArrowRightIcon class="w-4 h-4" />
          </router-link>
          <a href="#fonctionnalites" class="btn btn-secondary btn-lg">Découvrir les fonctionnalités</a>
        </div>
      </div>

      <!-- Aperçu visuel stylisé -->
      <div class="landing-preview" aria-hidden="true">
        <div class="landing-preview-card">
          <div class="landing-preview-row">
            <div class="landing-preview-icon bg-blue-50 text-blue-600"><WrenchScrewdriverIcon class="w-4 h-4" /></div>
            <div class="flex-1">
              <div class="landing-preview-bar" style="width: 70%" />
              <div class="landing-preview-bar-sm" style="width: 45%" />
            </div>
            <span class="badge badge-blue">En cours</span>
          </div>
          <div class="landing-preview-row">
            <div class="landing-preview-icon bg-green-50 text-green-600"><CheckCircleIcon class="w-4 h-4" /></div>
            <div class="flex-1">
              <div class="landing-preview-bar" style="width: 55%" />
              <div class="landing-preview-bar-sm" style="width: 30%" />
            </div>
            <span class="badge badge-green">Prêt</span>
          </div>
          <div class="landing-preview-row">
            <div class="landing-preview-icon bg-amber-50 text-amber-600"><ClockIcon class="w-4 h-4" /></div>
            <div class="flex-1">
              <div class="landing-preview-bar" style="width: 60%" />
              <div class="landing-preview-bar-sm" style="width: 38%" />
            </div>
            <span class="badge badge-yellow">Attente pièces</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Fonctionnalités -->
    <section id="fonctionnalites" class="landing-section">
      <div class="landing-section-header">
        <h2 class="landing-section-title">Tout ce qu'il faut pour faire tourner l'atelier</h2>
        <p class="landing-section-subtitle">Six modules pensés pour le travail réel au comptoir, pas pour la démo.</p>
      </div>
      <div class="landing-features">
        <div v-for="f in features" :key="f.title" class="card landing-feature-card">
          <div :class="['landing-feature-icon', f.bg, f.color]"><component :is="f.icon" class="w-5 h-5" /></div>
          <h3 class="landing-feature-title">{{ f.title }}</h3>
          <p class="landing-feature-desc">{{ f.desc }}</p>
        </div>
      </div>
    </section>

    <!-- Comment ça marche -->
    <section class="landing-section landing-section-alt">
      <div class="landing-section-header">
        <h2 class="landing-section-title">Un flux simple, du comptoir à la livraison</h2>
      </div>
      <div class="landing-steps">
        <div v-for="(s, i) in steps" :key="s.title" class="landing-step">
          <div class="landing-step-number">{{ i + 1 }}</div>
          <h3 class="landing-step-title">{{ s.title }}</h3>
          <p class="landing-step-desc">{{ s.desc }}</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="landing-cta">
      <h2 class="landing-cta-title">Prêt à organiser votre atelier ?</h2>
      <p class="landing-cta-subtitle">Connectez-vous pour accéder à votre espace technicien.</p>
      <router-link to="/login" class="btn btn-primary btn-lg">
        Se connecter <ArrowRightIcon class="w-4 h-4" />
      </router-link>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
      <div class="landing-logo landing-logo-sm">
        <div class="landing-logo-icon"><WrenchScrewdriverIcon class="w-3.5 h-3.5 text-white" /></div>
        <span>Aide<strong>Phone</strong></span>
      </div>
      <p>© {{ year }} AidePhone · Conçu pour les techniciens africains</p>
    </footer>
  </div>
</template>

<script setup lang="ts">
import {
  WrenchScrewdriverIcon, ArrowRightIcon, CheckCircleIcon, ClockIcon,
  MagnifyingGlassIcon, CpuChipIcon, HashtagIcon, BoltIcon, BookOpenIcon, UserGroupIcon,
} from '@heroicons/vue/24/outline'

const year = new Date().getFullYear()

const features = [
  { title: 'Diagnostic guidé', desc: "Un parcours étape par étape (appareil → symptômes → analyse) pour identifier la panne la plus probable avant d'ouvrir l'appareil.", icon: MagnifyingGlassIcon, bg: 'bg-blue-50 dark:bg-blue-900/30', color: 'text-blue-600' },
  { title: 'Suivi des réparations', desc: 'Chaque intervention a un numéro de dossier, un statut, une priorité et un historique — plus de post-it perdus.', icon: WrenchScrewdriverIcon, bg: 'bg-indigo-50 dark:bg-indigo-900/30', color: 'text-indigo-600' },
  { title: 'Codes secrets', desc: 'Une base de codes techniques par marque et modèle pour accéder rapidement aux menus de diagnostic.', icon: HashtagIcon, bg: 'bg-purple-50 dark:bg-purple-900/30', color: 'text-purple-600' },
  { title: 'Base de composants', desc: 'Fiches composants avec difficulté de remplacement, pannes courantes et pièces compatibles.', icon: CpuChipIcon, bg: 'bg-green-50 dark:bg-green-900/30', color: 'text-green-600' },
  { title: 'Guides de dépannage', desc: "Des guides hardware et software détaillés pour les pannes les plus fréquentes.", icon: BookOpenIcon, bg: 'bg-amber-50 dark:bg-amber-900/30', color: 'text-amber-600' },
  { title: 'Suivi clients & appareils', desc: "L'historique du client et de son appareil reste rattaché au dossier, réparation après réparation.", icon: UserGroupIcon, bg: 'bg-rose-50 dark:bg-rose-900/30', color: 'text-rose-600' },
]

const steps = [
  { title: 'Diagnostiquer', desc: "Sélectionnez l'appareil et les symptômes signalés par le client pour obtenir une piste de panne." },
  { title: 'Réparer', desc: 'Créez le dossier de réparation, suivez son statut et consultez les guides et codes nécessaires.' },
  { title: 'Livrer & suivre', desc: 'Clôturez la réparation avec le coût final et la garantie ; le dossier reste consultable ensuite.' },
]
</script>

<style scoped>
.landing { min-height: 100vh; background: #f8fafc; color: #0f172a; }
.dark .landing { background: #0b1120; color: #f1f5f9; }

/* Nav */
.landing-nav { position: sticky; top: 0; z-index: 20; background: rgba(255,255,255,0.8); backdrop-filter: blur(8px); border-bottom: 1px solid #e2e8f0; }
.dark .landing-nav { background: rgba(11,17,32,0.8); border-bottom-color: #1e293b; }
.landing-nav-inner { max-width: 72rem; margin: 0 auto; padding: 0.875rem 1.5rem; display: flex; align-items: center; justify-content: space-between; }

.landing-logo { display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; font-weight: 700; color: #0f172a; }
.dark .landing-logo { color: #f8fafc; }
.landing-logo strong { color: #2563eb; }
.landing-logo-icon { width: 1.75rem; height: 1.75rem; background: #2563eb; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.landing-logo-sm { font-size: 0.875rem; justify-content: center; margin-bottom: 0.5rem; }

/* Hero */
.landing-hero { max-width: 72rem; margin: 0 auto; padding: 4rem 1.5rem 3rem; display: flex; flex-direction: column; gap: 3rem; align-items: center; text-align: center; }
@media (min-width: 1024px) { .landing-hero { flex-direction: row; text-align: left; padding: 5.5rem 1.5rem 4rem; gap: 4rem; } }

.landing-hero-inner { max-width: 34rem; }
.landing-eyebrow { display: inline-block; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: #2563eb; background: #eff6ff; padding: 0.25rem 0.75rem; border-radius: 9999px; margin-bottom: 1.25rem; }
.dark .landing-eyebrow { background: #1e3a8a; color: #93c5fd; }
.landing-title { font-size: 2rem; line-height: 1.2; font-weight: 800; letter-spacing: -0.02em; color: #0f172a; margin-bottom: 1rem; }
.dark .landing-title { color: #f8fafc; }
@media (min-width: 640px) { .landing-title { font-size: 2.5rem; } }
.landing-subtitle { font-size: 1rem; line-height: 1.6; color: #64748b; margin-bottom: 2rem; }
.dark .landing-subtitle { color: #94a3b8; }
.landing-hero-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center; }
@media (min-width: 1024px) { .landing-hero-actions { justify-content: flex-start; } }

/* Aperçu */
.landing-preview { flex: 1; width: 100%; max-width: 26rem; }
.landing-preview-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 20px 40px -12px rgb(0 0 0 / 0.15); display: flex; flex-direction: column; gap: 0.875rem; }
.dark .landing-preview-card { background: #1e293b; border-color: #334155; }
.landing-preview-row { display: flex; align-items: center; gap: 0.75rem; }
.landing-preview-icon { width: 2rem; height: 2rem; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.landing-preview-bar { height: 0.5rem; border-radius: 9999px; background: #e2e8f0; margin-bottom: 0.375rem; }
.landing-preview-bar-sm { height: 0.375rem; border-radius: 9999px; background: #f1f5f9; }
.dark .landing-preview-bar { background: #334155; }
.dark .landing-preview-bar-sm { background: #1e293b; }

/* Sections */
.landing-section { max-width: 72rem; margin: 0 auto; padding: 3.5rem 1.5rem; }
.landing-section-alt { background: #fff; max-width: none; }
.dark .landing-section-alt { background: #111827; }
.landing-section-alt > * { max-width: 72rem; margin-left: auto; margin-right: auto; }
.landing-section-header { text-align: center; max-width: 36rem; margin: 0 auto 2.5rem; }
.landing-section-title { font-size: 1.5rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; margin-bottom: 0.5rem; }
.dark .landing-section-title { color: #f8fafc; }
.landing-section-subtitle { font-size: 0.9375rem; color: #64748b; }
.dark .landing-section-subtitle { color: #94a3b8; }

.landing-features { display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem; }
@media (min-width: 640px) { .landing-features { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .landing-features { grid-template-columns: repeat(3, 1fr); } }
.landing-feature-card { text-align: left; }
.landing-feature-icon { width: 2.5rem; height: 2.5rem; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; margin-bottom: 0.875rem; }
.landing-feature-title { font-size: 1rem; font-weight: 600; color: #0f172a; margin-bottom: 0.375rem; }
.dark .landing-feature-title { color: #f8fafc; }
.landing-feature-desc { font-size: 0.875rem; line-height: 1.55; color: #64748b; }
.dark .landing-feature-desc { color: #94a3b8; }

.landing-steps { display: grid; grid-template-columns: repeat(1, 1fr); gap: 1.5rem; }
@media (min-width: 768px) { .landing-steps { grid-template-columns: repeat(3, 1fr); } }
.landing-step { text-align: center; padding: 0 1rem; }
.landing-step-number { width: 2.5rem; height: 2.5rem; border-radius: 9999px; background: #2563eb; color: #fff; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; }
.landing-step-title { font-size: 1rem; font-weight: 600; color: #0f172a; margin-bottom: 0.375rem; }
.dark .landing-step-title { color: #f8fafc; }
.landing-step-desc { font-size: 0.875rem; line-height: 1.55; color: #64748b; }
.dark .landing-step-desc { color: #94a3b8; }

/* CTA */
.landing-cta { max-width: 40rem; margin: 0 auto; padding: 4rem 1.5rem; text-align: center; }
.landing-cta-title { font-size: 1.625rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
.dark .landing-cta-title { color: #f8fafc; }
.landing-cta-subtitle { font-size: 0.9375rem; color: #64748b; margin-bottom: 1.5rem; }
.dark .landing-cta-subtitle { color: #94a3b8; }

/* Footer */
.landing-footer { border-top: 1px solid #e2e8f0; padding: 2rem 1.5rem; text-align: center; font-size: 0.8125rem; color: #94a3b8; }
.dark .landing-footer { border-top-color: #1e293b; }
</style>
