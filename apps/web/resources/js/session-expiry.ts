// Author by Lab | zefry. A session rejection never discards a pending POS operation.
import axios from 'axios'

export function installSessionExpiryNavigation(): void {
  const signIn = () => {
    const owner = location.pathname.startsWith('/platform/') || location.pathname === '/owner-login'
    location.assign(`${owner ? '/owner-login' : '/business-login'}?reason=session-expired`)
  }
  const expired = (status: number, code?: string) => status === 419 || (status === 401 && code === 'SESSION_AUTHORITY_DENIED')
  axios.interceptors.response.use(response => response, error => {
    if (expired(error.response?.status, error.response?.data?.error?.code)) signIn()
    return Promise.reject(error)
  })
  const originalFetch = window.fetch.bind(window)
  window.fetch = async (...args) => {
    const response = await originalFetch(...args)
    if (response.status === 401 || response.status === 419) {
      try {
        const data = await response.clone().json()
        if (expired(response.status, data?.error?.code)) signIn()
      } catch { if (response.status === 419) signIn() }
    }
    return response
  }
}
