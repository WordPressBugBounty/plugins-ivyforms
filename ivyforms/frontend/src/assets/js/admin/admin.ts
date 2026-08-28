/* eslint no-unused-vars: "off" */
import type { Component } from 'vue'
import * as Vue from 'vue'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from '../../../views/admin/App.vue'
import EntryPage from '@/views/admin/entries/entry-page/EntryPage.vue'
import router from '@/router'
import '@/assets/scss/main.scss'
import 'element-plus/dist/index.css'
import { useLabelsStore } from '@/stores/labels'
import { useLabels } from '@/composables/useLabels'
import api, { dispatchIvyReady, installIvyGlobals } from '@/composables/IvyFormAPI'
import { useProFeatures } from '@/composables/useProFeatures'
import { useFormBuilder } from '@/stores/useFormBuilder'
import { useEntry } from '@/stores/useEntry'
import { useNavigation } from '@/composables/useNavigation'
import { registerLiteComponents } from '@/composables/registerLiteComponents'
import { registerLiteFieldOptions } from '@/views/admin/form-builder/form-builder-page/form-builder/panel/tabs/field-options/registerLiteFieldOptions'
import { registerLiteStyleSectionPromos } from '@/views/admin/form-builder/form-builder-page/form-builder/panel/tabs/style-controls/registerLiteStyleSectionPromos'
import * as LiteTypes from '@/types/index'
import type { IvyProFeatures } from '@/types/global'
import { useWcagColors } from '@/composables/useWcagColors'
import { useSettingsStore } from '@/stores/useSettingsStore'
import IvyMessage from '@/views/_components/message/ivyMessage'
import { useApiClient } from '@/composables/useApiClient'
import { useActionEntityStore } from '@/stores/actionEntityStore'
import { useUnsavedChangesStore } from '@/stores/useUnsavedChangesStore'
import { useProUpgradeDialogStore } from '@/stores/proUpgradeDialogStore'
import { useGlobalState } from '@/stores/useGlobalState'
import { denormalizeFieldsForApi } from '@/utils/fieldsNormalization'
import { countryList } from '@/constants/countries'
import { isSecurityField } from '@/constants/securityFields'
import { useProFeatureUpgrade } from '@/composables/useProFeatureUpgrade'
import { useConfirmationMultiSetting } from '@/stores/useConfirmationMultiSetting'
import { useGoogleSheetsFormIntegrations } from '@/integrations/google-sheets/composables/useGoogleSheetsFormIntegrations'
import {
  registerFormBuilderExtension,
  getFormBuilderExtensions,
} from '@/extensions/formBuilderExtensions'
import { useAllEntries } from '@/stores/useAllEntries'
import { getPreviewFieldComponent } from '@/composables/useFieldPreviewComponents'
import { normalizeFieldsForBuilder } from '@/utils/fieldsNormalization'
import { registerLitePublicFieldComponents } from '@/entries/publicFieldComponentRegistry'
import { registerGoogleSheetsAdmin } from '@/integrations/google-sheets/registerGoogleSheetsAdmin'

// Note: Window interface extensions are now defined in src/types/global.d.ts
// No need to duplicate declarations here

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(router)

// Shared settings store: must exist before mount so Pro views (e.g. Zapier) can read the same
// `settings/all` payload as the rest of admin via `window.IvyForms.settingsStore`.
const settingsStore = useSettingsStore()

// Initialize the labels store with WordPress data
const labelsStore = useLabelsStore()
labelsStore.initialize()

// Make labels globally available to all components
app.config.globalProperties.$labels = useLabels()

// Expose IvyFormsApi and Lite globals for extenders
;(function exposeGlobals() {
  // Ensure the API is present on window
  installIvyGlobals()
  const w = window
  const { getLabel } = useLabels()
  const formBuilder = useFormBuilder()
  const globalState = useGlobalState()

  // Proxy to resolve labels dynamically via getLabel
  const labelsProxy = new Proxy(
    {},
    {
      get: (_t, key: string | symbol) => {
        try {
          return getLabel(String(key))
        } catch {
          return String(key)
        }
      },
    },
  )

  w.IvyForms = w.IvyForms || { api, hooks: api.hooks, fields: api.fields }
  w.IvyForms.api = api
  // Always sync to canonical api.hooks so third-party registrations land on the same registry.
  w.IvyForms.hooks = api.hooks

  // Ensure hook registration triggers reactive updates in Lite components
  if (w.IvyForms.hooks === api.hooks && api.hooks.triggerUpdate) {
    w.IvyForms.hooks.triggerUpdate = api.hooks.triggerUpdate.bind(api.hooks)
  }

  w.IvyForms.fields = api.fields
  // Expose label helpers for Pro plugin and other extenders
  w.IvyForms.getLabel = getLabel
  w.IvyForms.labels = labelsProxy
  // Set WordPress admin URL
  w.IvyForms.adminUrl = w.wpIvyApiSettings?.root?.replace(/\/wp-json\/?$/, '') + '/wp-admin/'
  // Expose Vue for pro plugins with guaranteed compatibility
  w.IvyForms.Vue = Vue
  //w.Vue = w.Vue || Vue // Global fallback for compatibility with other plugins
  // Expose IvyMessage utility for Pro plugin
  w.IvyForms.IvyMessage = IvyMessage
  // Expose useApiClient for Pro plugin (call it once to prevent tree-shaking)
  w.IvyForms.useApiClient = useApiClient
  // Shared Google Sheets form-builder CRUD for Lite single + Pro multi shells
  w.IvyForms.useGoogleSheetsFormIntegrations = useGoogleSheetsFormIntegrations
  // Expose stores for Pro plugin to use dialogs and entity actions
  w.IvyForms.useActionEntityStore = useActionEntityStore
  // Expose the actual store instance (not the function) so Pro uses the same instance
  w.IvyForms.unsavedChangesStore = useUnsavedChangesStore()
  w.IvyForms.useProUpgradeDialogStore = useProUpgradeDialogStore
  // Expose the form builder store so external components can read/write fields
  w.IvyForms.formBuilder = formBuilder
  w.IvyForms.registerFormBuilderExtension = registerFormBuilderExtension
  w.IvyForms.getFormBuilderExtensions = getFormBuilderExtensions
  w.IvyForms.useEntry = useEntry
  // Expose global state for Pro plugins to set page title and active page
  w.IvyForms.globalState = globalState
  // Expose shared types for Pro plugin and other extenders
  w.IvyForms.types = LiteTypes
  // Expose shared utility helpers for Pro plugin and other extenders
  const { showUpgradeDialog } = useProFeatureUpgrade()
  w.IvyForms.helpers = {
    isSecurityField,
    showUpgradeDialog,
    getPreviewFieldComponent: getPreviewFieldComponent as (fieldType: string) => unknown,
    normalizeFieldsForBuilder: normalizeFieldsForBuilder as (fields: unknown[]) => unknown[],
    denormalizeFieldsForApi,
    countryList,
    registerLitePublicFieldComponents,
  }
  w.IvyForms.confirmationMulti = {
    refreshTable: async (formId: number) => {
      await useConfirmationMultiSetting().searchConfirmations({ formId })
    },
  }

  // Expose entry stores so Pro can read/refresh entries (add-entry is a Pro feature).
  w.IvyForms.useAllEntries = useAllEntries
  w.IvyForms.useEntry = useEntry
  // Create a callable components registry:
  // - Use as function:   w.IvyForms.components('MyComp', Comp)
  // - Or as object map:  w.IvyForms.components.MyComp
  // - Or helper alias:   w.IvyForms.registerComponent('MyComp', Comp)
  type ComponentRegistry = {
    (name?: string, component?: Component): Component | ComponentRegistry
    [key: string]: Component | unknown
  }
  const componentsRegistry = ((name?: string, component?: Component) => {
    if (name && component) {
      app.component(name, component)
      ;(componentsRegistry as Record<string, Component>)[name] = component
      return component
    }
    // If only name is provided, return the component
    if (name) {
      return (componentsRegistry as Record<string, Component>)[name]
    }
    return componentsRegistry
  }) as ComponentRegistry

  // Register all Lite components for Pro plugin access
  registerLiteComponents(componentsRegistry, app)

  w.IvyForms.components = componentsRegistry
  w.IvyForms.registerComponent = (name: string, component: Component) =>
    componentsRegistry(name, component)
  w.IvyForms.registerComponent('EntryPage', EntryPage)

  // Expose router for Pro plugins to add routes dynamically
  w.IvyForms._router = router

  // Initialize pro features bridge if not already provided by Pro plugin
  if (!w.IvyForms.pro) {
    const pro = useProFeatures()
    // Fire fetch; backend route may 404 in Lite-only context which is fine.
    pro
      .fetch()
      .then(() => {
        window.dispatchEvent(new CustomEvent('ivyforms:pro_features_loaded'))
      })
      .catch(() => {})
    w.IvyForms.pro = {
      get loaded() {
        return pro.loaded.value
      },
      get loading() {
        return pro.loading.value
      },
      get plan() {
        return pro.plan.value
      },
      get upgradeMap() {
        return pro.upgradeMap.value
      },
      get groups() {
        return pro.groups.value
      },
      get license() {
        return pro.license.value
      },
      get features(): Record<string, boolean> {
        const rawFeatures =
          (pro as { data?: { value?: { features?: Record<string, unknown> } } }).data?.value
            ?.features || {}
        // Convert unknown to boolean
        const booleanFeatures: Record<string, boolean> = {}
        for (const key in rawFeatures) {
          booleanFeatures[key] = !!rawFeatures[key]
        }
        return booleanFeatures
      },
      hasFeature: (slug: string) => pro.hasFeature(slug),
      fetch: (opts?: Record<string, unknown>) => pro.fetch(opts),
      refresh: () => pro.refresh(),
    }
  }

  // Simple onProReady queue so other scripts can safely register logic dependent on features
  if (!w.IvyForms.onProReady) {
    w.IvyForms._proReadyQueue = []
    w.IvyForms.onProReady = (cb: (pro: IvyProFeatures) => void) => {
      try {
        if (w.IvyForms.pro?.loaded) return cb(w.IvyForms.pro)
        w.IvyForms._proReadyQueue?.push(cb)
      } catch {
        /* noop */
      }
    }
    window.addEventListener('ivyforms:pro_features_loaded', () => {
      const q = Array.isArray(w.IvyForms._proReadyQueue) ? w.IvyForms._proReadyQueue : []
      while (q.length) {
        const fn = q.shift()
        try {
          if (fn) {
            fn(w.IvyForms.pro)
          }
        } catch {
          /* ignore */
        }
      }
    })
  }

  // Convenience global helper (returns boolean) – safe to call anytime
  if (!w.IvyForms.hasProFeature) {
    w.IvyForms.hasProFeature = (slug: string): boolean => {
      try {
        if (!w.IvyForms.pro) return false
        if (!w.IvyForms.pro.loaded) return false // treat not-yet-loaded as false for simplicity
        return !!w.IvyForms.pro.hasFeature?.(slug)
      } catch {
        return false
      }
    }
  }

  // Pinia settings store (same instance as Lite admin) — Pro reads `integrations.*` here.
  w.IvyForms.settingsStore = settingsStore
})()

// Register Google Sheets after hooks + router are exposed
registerGoogleSheetsAdmin()

// Register Lite field options
registerLiteFieldOptions()

// Register Lite advanced style section promos (replaced by Pro via hook when available)
registerLiteStyleSectionPromos()

// Bind navigation helper under app context so useRouter() resolves correctly.
app.runWithContext(() => {
  window.IvyForms.navigateToAdminPage = useNavigation().navigateToAdminPage
})

app.mount('#ivyforms-app')

// Load settings from backend first, then initialize WCAG colors
settingsStore
  .loadAllSettings()
  .then(() => {
    // Initialize WCAG colors watcher globally after settings are loaded
    const { startWatching } = useWcagColors()
    startWatching()
  })
  .catch(() => {
    // Still start watcher even if settings fail to load (will use default value)
    const { startWatching } = useWcagColors()
    startWatching()
  })

// Signal readiness for any external extenders waiting on Lite
dispatchIvyReady({ scope: 'admin' })
