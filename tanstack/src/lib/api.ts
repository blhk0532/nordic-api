export type SanctumUser = {
  id: number
  name: string
  email: string
}

export type SanctumAuthState = {
  token: string
  user: SanctumUser
}

export type SwedenPersonerItem = {
  id: number
  fornamn?: string | null
  efternamn?: string | null
  personnamn?: string | null
  adress?: string | null
  postnummer?: string | null
  postort?: string | null
  kommun?: string | null
  telefon?: string | null
  alder?: number | null
}

export type SwedenPersonerResponse = {
  data: SwedenPersonerItem[]
  meta?: {
    current_page?: number
    last_page?: number
    per_page?: number
    total?: number
  }
}

const API_BASE_URL =
  (import.meta.env.VITE_API_BASE_URL as string | undefined)?.replace(/\/$/, '') ??
  'http://127.0.0.1:8000'

const AUTH_STORAGE_KEY = 'sanctum_auth_state'

function isBrowser() {
  return typeof window !== 'undefined'
}

function readErrorMessage(payload: unknown): string {
  if (!payload || typeof payload !== 'object') {
    return 'Unexpected API error'
  }

  const maybePayload = payload as {
    message?: string
    errors?: Record<string, string[]>
  }

  if (maybePayload.message) {
    return maybePayload.message
  }

  const firstError = Object.values(maybePayload.errors ?? {})[0]?.[0]
  return firstError ?? 'Unexpected API error'
}

export function getAuthState(): SanctumAuthState | null {
  if (!isBrowser()) {
    return null
  }

  const raw = window.localStorage.getItem(AUTH_STORAGE_KEY)
  if (!raw) {
    return null
  }

  try {
    return JSON.parse(raw) as SanctumAuthState
  } catch {
    window.localStorage.removeItem(AUTH_STORAGE_KEY)
    return null
  }
}

export function clearAuthState() {
  if (isBrowser()) {
    window.localStorage.removeItem(AUTH_STORAGE_KEY)
  }
}

export async function loginWithSanctumToken(input: {
  email: string
  password: string
  deviceName: string
}): Promise<SanctumAuthState> {
  const response = await fetch(`${API_BASE_URL}/api/sanctum/token`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      email: input.email,
      password: input.password,
      device_name: input.deviceName,
    }),
  })

  const payload = (await response.json()) as {
    token?: string
    user?: SanctumUser
    message?: string
    errors?: Record<string, string[]>
  }

  if (!response.ok || !payload.token || !payload.user) {
    throw new Error(readErrorMessage(payload))
  }

  const auth: SanctumAuthState = {
    token: payload.token,
    user: payload.user,
  }

  if (isBrowser()) {
    window.localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(auth))
  }

  return auth
}

export async function fetchCurrentUser(token: string): Promise<SanctumUser> {
  const response = await fetch(`${API_BASE_URL}/api/user`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
  })

  const payload = (await response.json()) as {
    user?: SanctumUser
    message?: string
    errors?: Record<string, string[]>
  }

  if (!response.ok || !payload.user) {
    throw new Error(readErrorMessage(payload))
  }

  return payload.user
}

export async function fetchSwedenPersoner(input: {
  token: string
  page?: number
  perPage?: number
  filters?: {
    fornamn?: string
    efternamn?: string
    postort?: string
    postnummer?: string
  }
}): Promise<SwedenPersonerResponse> {
  const params = new URLSearchParams()
  params.set('page', String(input.page ?? 1))
  params.set('per_page', String(input.perPage ?? 25))

  if (input.filters?.fornamn) {
    params.set('filter[fornamn]', input.filters.fornamn)
  }
  if (input.filters?.efternamn) {
    params.set('filter[efternamn]', input.filters.efternamn)
  }
  if (input.filters?.postort) {
    params.set('filter[postort]', input.filters.postort)
  }
  if (input.filters?.postnummer) {
    params.set('filter[postnummer]', input.filters.postnummer)
  }

  const response = await fetch(`${API_BASE_URL}/api/sweden-personer/search?${params.toString()}`, {
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${input.token}`,
    },
  })

  if (response.status === 401) {
    clearAuthState()
    throw new Error('Unauthorized. Please login again.')
  }

  const payload = (await response.json()) as SwedenPersonerResponse & {
    message?: string
    errors?: Record<string, string[]>
  }

  if (!response.ok) {
    throw new Error(readErrorMessage(payload))
  }

  return payload
}