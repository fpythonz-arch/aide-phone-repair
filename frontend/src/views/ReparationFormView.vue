<template>
  <div class="animate-fade-in max-w-2xl mx-auto">
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ isEdit ? 'Modifier la réparation' : 'Nouvelle réparation' }}</h1>
        <p class="page-subtitle">{{ isEdit ? repair?.number : 'Enregistrer une nouvelle demande' }}</p>
      </div>
      <button class="btn btn-secondary" @click="$router.back()"><ArrowLeftIcon class="w-4 h-4" />Retour</button>
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Client -->
      <div class="card">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <UserIcon class="w-4 h-4 text-blue-500" />Client
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="label">Nom <span class="text-red-500">*</span></label><input v-model="f.client_name" class="input" placeholder="Nom et prénom" required /></div>
          <div><label class="label">Téléphone <span class="text-red-500">*</span></label><input v-model="f.client_phone" type="tel" class="input" placeholder="+228 90 00 00 00" required /></div>
          <div class="sm:col-span-2"><label class="label">Email</label><input v-model="f.client_email" type="email" class="input" placeholder="client@email.com" /></div>
        </div>
      </div>

      <!-- Appareil -->
      <div class="card">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <DevicePhoneMobileIcon class="w-4 h-4 text-blue-500" />Appareil
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Marque <span class="text-red-500">*</span></label>
            <select v-model="f.device_brand" class="select" required>
              <option value="">Sélectionner</option>
              <option v-for="b in BRANDS" :key="b" :value="b">{{ b }}</option>
            </select>
          </div>
          <div><label class="label">Modèle <span class="text-red-500">*</span></label><input v-model="f.device_model" class="input" placeholder="Galaxy A15, iPhone 13..." required /></div>
          <div><label class="label">IMEI / Série</label><input v-model="f.device_imei" class="input" placeholder="Optionnel" /></div>
        </div>
      </div>

      <!-- Problème -->
      <div class="card">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <WrenchScrewdriverIcon class="w-4 h-4 text-blue-500" />Problème
        </h2>
        <div class="space-y-4">
          <div><label class="label">Description <span class="text-red-500">*</span></label><textarea v-model="f.problem_description" rows="3" class="input resize-none" placeholder="Problème signalé par le client..." required /></div>
          <div><label class="label">Diagnostic technique</label><textarea v-model="f.diagnosis" rows="2" class="input resize-none" placeholder="Résultat du diagnostic..." /></div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">Technicien</label>
              <select v-model="f.technician" class="select">
                <option value="">Non assigné</option>
                <option v-for="t in TECHS" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
            <div>
              <label class="label">Priorité</label>
              <select v-model="f.priority" class="select">
                <option value="low">Faible</option><option value="normal">Normale</option>
                <option value="high">Élevée</option><option value="urgent">Urgente</option>
              </select>
            </div>
            <div>
              <label class="label">Statut</label>
              <select v-model="f.status" class="select">
                <option value="new">Nouveau</option><option value="received">Reçu</option>
                <option value="diagnosing">Diagnostic</option><option value="in_progress">En cours</option>
                <option value="waiting_parts">Attente pièces</option><option value="testing">Tests</option>
                <option value="ready">Prêt</option><option value="delivered">Livré</option>
              </select>
            </div>
            <div><label class="label">Date estimée</label><input v-model="f.estimated_ready" type="date" class="input" :min="today" /></div>
          </div>
        </div>
      </div>

      <!-- Coût -->
      <div class="card">
        <h2 class="font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <BanknotesIcon class="w-4 h-4 text-blue-500" />Coût
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="label">Devis estimé</label>
            <div class="relative"><input v-model.number="f.cost_estimate" type="number" min="0" step="100" class="input pr-16" placeholder="0" /><span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">{{ f.currency }}</span></div>
          </div>
          <div>
            <label class="label">Coût final</label>
            <div class="relative"><input v-model.number="f.cost_final" type="number" min="0" step="100" class="input pr-16" placeholder="0" /><span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">{{ f.currency }}</span></div>
          </div>
          <div>
            <label class="label">Devise</label>
            <select v-model="f.currency" class="select">
              <option value="FCFA">FCFA</option><option value="EUR">EUR</option>
              <option value="USD">USD</option><option value="GHS">GHS</option><option value="NGN">NGN</option>
            </select>
          </div>
        </div>
        <div class="mt-3"><label class="label">Garantie (jours)</label><input v-model.number="f.warranty_days" type="number" min="0" max="365" class="input w-32" placeholder="30" /></div>
      </div>

      <div class="card"><label class="label">Notes internes</label><textarea v-model="f.notes" rows="2" class="input resize-none" placeholder="Notes, remarques..." /></div>

      <div class="flex gap-3 pb-4">
        <button type="button" class="btn btn-secondary flex-1" @click="$router.back()">Annuler</button>
        <button type="submit" class="btn btn-primary flex-1" :disabled="submitting">{{ isEdit ? 'Enregistrer' : 'Créer la réparation' }}</button>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeftIcon, UserIcon, DevicePhoneMobileIcon, WrenchScrewdriverIcon, BanknotesIcon } from '@heroicons/vue/24/outline'
import { useRepairs } from '@/composables/useRepairs'
import { useUiStore } from '@/stores'
import type { Repair } from '@/types'

const route = useRoute(), router = useRouter()
const { getById, createRepair, updateRepair } = useRepairs()
const uiStore = useUiStore()

const isEdit = computed(() => !!route.params.id && route.params.id !== 'nouvelle')
const repair = computed(() => isEdit.value ? getById(route.params.id as string) : undefined)
const submitting = ref(false)
const today = new Date().toISOString().split('T')[0]

const BRANDS = ['Samsung', 'Apple', 'Xiaomi', 'Huawei', 'Tecno', 'Infinix', 'itel', 'Motorola', 'OPPO', 'realme', 'Google', 'Nokia', 'HP', 'Dell', 'Lenovo', 'Autre']
const TECHS  = ['Abdoul', 'Ibrahim', 'Moussa', 'Aminata', 'Kofi', 'Kwame']

const f = ref<Omit<Repair, 'id'|'number'|'created_at'|'updated_at'>>({
  client_name: '', client_phone: '', client_email: '', device_brand: '', device_model: '',
  device_imei: '', problem_description: '', diagnosis: '', technician: '', status: 'new',
  priority: 'normal', cost_estimate: undefined, cost_final: undefined, currency: 'FCFA',
  warranty_days: 30, estimated_ready: '', notes: '', parts_used: [],
})

onMounted(() => { if (isEdit.value && repair.value) f.value = { ...repair.value } })

async function handleSubmit() {
  submitting.value = true
  try {
    if (isEdit.value && repair.value) {
      updateRepair(repair.value.id, f.value)
      uiStore.showSuccess('Réparation mise à jour')
      router.push(`/reparations/${repair.value.id}`)
    } else {
      const r = createRepair(f.value)
      uiStore.showSuccess(`Réparation ${r.number} créée`)
      router.push('/reparations')
    }
  } finally { submitting.value = false }
}
</script>
