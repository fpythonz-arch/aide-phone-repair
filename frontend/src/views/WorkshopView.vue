<template>
  <div class="space-y-6 max-w-3xl">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion de l'atelier</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Espace réservé au responsable. Vos réparations et vos clients restent privés à votre atelier.
      </p>
    </div>

    <p v-if="loadError" class="text-sm text-red-600">{{ loadError }}</p>
    <p v-else-if="loading" class="text-sm text-gray-500">Chargement…</p>

    <template v-else-if="workshop">
      <section class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Nom de l'atelier</h2>
        <form class="flex flex-col sm:flex-row gap-3" @submit.prevent="save">
          <input v-model="name" type="text" maxlength="100" class="input flex-1" aria-label="Nom de l'atelier" required />
          <button type="submit" class="btn btn-primary" :disabled="saving || !name.trim() || name.trim() === workshop.name">
            {{ saving ? 'Enregistrement…' : 'Enregistrer' }}
          </button>
        </form>
        <p v-if="saveMessage" class="text-sm text-green-600 mt-2">{{ saveMessage }}</p>
        <p v-if="saveError" class="text-sm text-red-600 mt-2">{{ saveError }}</p>
        <p class="text-xs text-gray-500 mt-4">
          Créé le {{ formatDate(workshop.created_at) }} · {{ workshop.repairs_count }}
          réparation{{ workshop.repairs_count > 1 ? 's' : '' }}
        </p>
      </section>

      <section class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Équipe</h2>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-slate-700">
                <th class="py-2 pr-4 font-medium">Nom</th>
                <th class="py-2 pr-4 font-medium">E-mail</th>
                <th class="py-2 pr-4 font-medium">Rôle</th>
                <th class="py-2 font-medium"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in workshop.members" :key="m.id" class="border-b border-gray-100 dark:border-slate-700/50">
                <td class="py-2 pr-4 text-gray-900 dark:text-white">{{ m.name }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ m.email }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">
                  <template v-if="isManageable(m)">
                    <select
                      class="input py-1"
                      :value="m.role"
                      :aria-label="`Rôle de ${m.name}`"
                      :disabled="busyMemberId === m.id"
                      @change="changeRole(m, ($event.target as HTMLSelectElement).value)"
                    >
                      <option v-for="r in INVITABLE_ROLES" :key="r" :value="r">{{ r }}</option>
                    </select>
                  </template>
                  <template v-else>{{ roleLabelOf(m.role) }}</template>
                </td>
                <td class="py-2 text-right">
                  <button
                    v-if="isManageable(m)"
                    type="button"
                    class="text-sm text-red-600 hover:underline disabled:opacity-50"
                    :disabled="busyMemberId === m.id"
                    @click="removeMember(m)"
                  >Retirer</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p v-if="memberError" class="text-sm text-red-600 mt-3">{{ memberError }}</p>
        <p v-if="workshop.members.length <= 1" class="text-xs text-gray-500 mt-4">
          Vous êtes seul dans cet atelier pour l'instant. Invitez un technicien avec le formulaire ci-dessous.
        </p>
      </section>

      <!-- Invitations -->
      <section class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-slate-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Inviter un technicien</h2>
        <p class="text-xs text-gray-500 mb-4">
          Vous obtenez un lien à envoyer à la personne (WhatsApp, SMS…). Le lien est à usage unique et valable 7 jours.
        </p>

        <form class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end" @submit.prevent="createInvitation">
          <div>
            <label class="label" for="inv-role">Rôle</label>
            <select id="inv-role" v-model="inviteRole" class="input">
              <option v-for="r in INVITABLE_ROLES" :key="r" :value="r">{{ r }}</option>
            </select>
          </div>
          <div>
            <label class="label" for="inv-email">E-mail (facultatif)</label>
            <input id="inv-email" v-model="inviteEmail" type="email" class="input" placeholder="reserve le lien à cette adresse" />
          </div>
          <button type="submit" class="btn btn-primary" :disabled="inviting">
            {{ inviting ? 'Création…' : "Créer le lien" }}
          </button>
        </form>
        <p v-if="inviteError" class="text-sm text-red-600 mt-2">{{ inviteError }}</p>

        <!-- Lien affiché une seule fois -->
        <div v-if="createdLink" class="mt-4 p-4 rounded-lg border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800">
          <p class="text-sm font-medium text-green-800 dark:text-green-300">Lien créé pour : {{ createdRole }}</p>
          <div class="flex flex-col sm:flex-row gap-2 mt-2">
            <input :value="createdLink" readonly class="input flex-1 text-xs" aria-label="Lien d'invitation" @focus="($event.target as HTMLInputElement).select()" />
            <button type="button" class="btn btn-primary" @click="copyLink">{{ copied ? 'Copié ✓' : 'Copier' }}</button>
          </div>
          <p class="text-xs text-green-800 dark:text-green-300 mt-2">
            Copiez-le maintenant : pour votre sécurité, il ne sera plus jamais affiché. Si vous le perdez, annulez cette invitation et créez-en une nouvelle.
          </p>
        </div>

        <div v-if="invitations.length" class="overflow-x-auto mt-6">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 border-b border-gray-200 dark:border-slate-700">
                <th class="py-2 pr-4 font-medium">Rôle</th>
                <th class="py-2 pr-4 font-medium">E-mail</th>
                <th class="py-2 pr-4 font-medium">Statut</th>
                <th class="py-2 pr-4 font-medium">Expire le</th>
                <th class="py-2 font-medium"><span class="sr-only">Actions</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="inv in invitations" :key="inv.id" class="border-b border-gray-100 dark:border-slate-700/50">
                <td class="py-2 pr-4 text-gray-900 dark:text-white">{{ inv.role }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ inv.email ?? '—' }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ STATUS_LABELS[inv.status] }}</td>
                <td class="py-2 pr-4 text-gray-600 dark:text-gray-300">{{ formatDate(inv.expires_at) }}</td>
                <td class="py-2 text-right">
                  <button v-if="inv.status === 'pending'" type="button" class="text-sm text-red-600 hover:underline" @click="revokeInvitation(inv)">Annuler</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { workshopApi } from '@/api/client'
import { useAuth } from '@/composables/useAuth'
import { roleLabelOf } from '@/composables/usePermissions'
import type { InvitationStatus, WorkshopDetails, WorkshopInvitation, WorkshopMember } from '@/types'

const { syncFromServer, currentUser } = useAuth()

const INVITABLE_ROLES = ['Technicien', 'Technicien senior']
const STATUS_LABELS: Record<InvitationStatus, string> = {
  pending: 'En attente',
  accepted: 'Utilisée',
  revoked: 'Annulée',
  expired: 'Expirée',
}

const workshop = ref<WorkshopDetails | null>(null)
const name = ref('')
const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
const saveMessage = ref('')
const saveError = ref('')

const invitations = ref<WorkshopInvitation[]>([])
const inviteRole = ref('Technicien')
const inviteEmail = ref('')
const inviting = ref(false)
const inviteError = ref('')
const createdLink = ref('')
const createdRole = ref('')
const copied = ref(false)
const memberError = ref('')
const busyMemberId = ref<number | null>(null)

function formatDate(value: string): string {
  return new Date(value).toLocaleDateString('fr-FR')
}

/** Le responsable et soi-même ne se gèrent pas ici. */
function isManageable(m: WorkshopMember): boolean {
  return m.role !== 'Admin' && m.id !== currentUser.value?.id
}

async function loadInvitations() {
  try {
    const { data } = await workshopApi.invitations()
    invitations.value = data.data
  } catch {
    // La liste est secondaire : une erreur ici ne doit pas bloquer la page.
  }
}

async function reloadMembers() {
  try {
    const { data } = await workshopApi.get()
    workshop.value = data.data
  } catch {
    // ignoré : la page garde l'état précédent
  }
}

function apiMessage(err: any, fallback: string): string {
  return err?.response?.data?.message
    ?? err?.response?.data?.errors?.role?.[0]
    ?? err?.response?.data?.errors?.email?.[0]
    ?? fallback
}

async function createInvitation() {
  inviting.value = true
  inviteError.value = ''
  createdLink.value = ''
  copied.value = false
  try {
    const { data } = await workshopApi.createInvitation(inviteRole.value, inviteEmail.value.trim() || undefined)
    createdLink.value = `${window.location.origin}/rejoindre/${data.data.token}`
    createdRole.value = data.data.role
    inviteEmail.value = ''
    await loadInvitations()
  } catch (err: any) {
    inviteError.value = apiMessage(err, 'Création impossible. Réessayez.')
  } finally {
    inviting.value = false
  }
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(createdLink.value)
    copied.value = true
  } catch {
    copied.value = false // le lien reste sélectionnable à la main dans le champ
  }
}

async function revokeInvitation(inv: WorkshopInvitation) {
  if (!window.confirm('Annuler cette invitation ? Le lien ne fonctionnera plus.')) return
  inviteError.value = ''
  try {
    await workshopApi.revokeInvitation(inv.id)
    await loadInvitations()
  } catch (err: any) {
    inviteError.value = apiMessage(err, 'Annulation impossible. Réessayez.')
  }
}

async function changeRole(m: WorkshopMember, role: string) {
  memberError.value = ''
  busyMemberId.value = m.id
  try {
    await workshopApi.updateMember(m.id, role)
    await reloadMembers()
  } catch (err: any) {
    memberError.value = apiMessage(err, 'Modification impossible. Réessayez.')
    await reloadMembers() // remet la liste d'équerre avec le serveur
  } finally {
    busyMemberId.value = null
  }
}

async function removeMember(m: WorkshopMember) {
  if (!window.confirm(`Retirer ${m.name} de l'atelier ? Son accès sera coupé immédiatement.`)) return
  memberError.value = ''
  busyMemberId.value = m.id
  try {
    await workshopApi.removeMember(m.id)
    await reloadMembers()
  } catch (err: any) {
    memberError.value = apiMessage(err, 'Retrait impossible. Réessayez.')
  } finally {
    busyMemberId.value = null
  }
}

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await workshopApi.get()
    workshop.value = data.data
    name.value = data.data.name
    await loadInvitations()
  } catch (err: any) {
    loadError.value = err?.response?.status === 403
      ? "Cet espace est réservé au responsable de l'atelier."
      : "Impossible de charger l'atelier. Réessayez."
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  saveMessage.value = ''
  saveError.value = ''
  try {
    const { data } = await workshopApi.rename(name.value.trim())
    if (workshop.value) workshop.value.name = data.data.name
    saveMessage.value = 'Nom enregistré.'
    await syncFromServer()
  } catch (err: any) {
    saveError.value = err?.response?.data?.errors?.name?.[0] ?? "Enregistrement impossible. Réessayez."
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>
