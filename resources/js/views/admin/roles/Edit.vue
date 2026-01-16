<template>
  <div class="flex justify-center">
    <Card class="w-full max-w-4xl">
      <template #title>
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-xl font-semibold">Editar rol</h2>
            <p class="text-sm text-surface-500">Actualiza el nombre y revisa los permisos asociados.</p>
          </div>
          <Tag value="Roles" severity="secondary" rounded />
        </div>
      </template>

      <template #content>
        <form class="flex flex-col gap-6" @submit.prevent="submitForm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="flex flex-col gap-1">
              <label for="role-name" class="text-sm font-medium text-surface-700">Nombre del rol</label>
              <InputText
                id="role-name"
                v-model="role.name"
                :invalid="hasError('name')"
                autocomplete="off"
              />
              <small v-if="hasError('name')" class="p-error">{{ getError('name') }}</small>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <div class="flex items-center gap-2">
              <h3 class="text-base font-medium text-surface-700">Permisos</h3>
            </div>
            <PickList
              v-model="permissionsPick"
              dataKey="id"
              breakpoint="992px"
              striped
              :showSourceControls="false"
              :showTargetControls="false"
              :pt="{
                root: { class: 'picklist' },
              }"
            >
              <template #sourceheader>Disponibles</template>
              <template #targetheader>Asignados</template>
              <template #option="{ option }">
                {{ option.name }}
              </template>
            </PickList>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <Button type="button" label="Cancelar" severity="secondary" text @click="goBack" :disabled="isSubmitting" />
            <Button type="submit" label="Guardar cambios" icon="pi pi-save" :loading="isSubmitting" />
          </div>
        </form>
      </template>
    </Card>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import PickList from 'primevue/picklist'
import useRoles from '@/composables/roles'
import usePermissions from '@/composables/permissions'

const route = useRoute()
const router = useRouter()

const {
  role,
  isLoading,
  hasError,
  getError,
  getRole,
  updateRole,
  resetRole
} = useRoles()

const {
  permissionList,
  getPermissionList,
  getRolePermissions,
  updateRolePermissions,
  isLoading: isLoadingPermissions,
} = usePermissions()

const permissionsPick = ref([[], []])
const isSubmitting = computed(() => isLoading.value || isLoadingPermissions.value)

const getDifference = (array1 = [], array2 = []) => {
  return array1.filter(item1 => !array2.some(item2 => item1.id === item2.id))
}

const loadData = async () => {
  resetRole();
  const id = route.params.id
  await Promise.all([
    getRole(id),
    (async () => {
      const all = await getPermissionList().then(() => Array.isArray(permissionList.value) ? permissionList.value : [])
      const assigned = await getRolePermissions(id)
      const available = getDifference(all, assigned)
      permissionsPick.value = [available, assigned]
    })()
  ])
}

const submitForm = async () => {
  try {
    const permissionsIds = permissionsPick.value[1].map(p => p.id)
    await updateRolePermissions(role.value.id, permissionsIds)
    router.push({ name: 'admin.roles.index' })
  } catch (error) {
    // errores ya manejados en composable
  }
}

const goBack = () => {
  router.back()
}

onMounted(() => {
  loadData()
})
</script>

