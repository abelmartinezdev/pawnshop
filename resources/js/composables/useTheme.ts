import { ref, watchEffect } from 'vue'

const theme = ref<'light'|'dark'>((localStorage.getItem('theme') as any) ?? 'light')

watchEffect(() => {
  document.documentElement.classList.toggle('dark', theme.value === 'dark')
  localStorage.setItem('theme', theme.value)
})

export function useTheme() {
  return {
    theme,
    toggle: () => (theme.value = theme.value === 'dark' ? 'light' : 'dark'),
    set: (v: 'light'|'dark') => (theme.value = v),
  }
}