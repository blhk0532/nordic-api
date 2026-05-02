import { FormEvent, useState } from 'react'
import { createFileRoute, useNavigate } from '@tanstack/react-router'
import { loginWithSanctumToken } from '../lib/api'

export const Route = createFileRoute('/login')({
  component: LoginPage,
})

function LoginPage() {
  const navigate = useNavigate()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [deviceName, setDeviceName] = useState('TanStack Start Web')
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  async function handleLogin(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    setIsLoading(true)

    try {
      await loginWithSanctumToken({
        email: email.trim(),
        password,
        deviceName: deviceName.trim() || 'TanStack Start Web',
      })

      await navigate({ to: '/sweden-personer' })
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Login failed')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <main className="page-wrap px-4 py-10">
      <section className="island-shell rounded-2xl p-6 sm:p-8">
        <p className="island-kicker mb-2">Laravel Sanctum</p>
        <h1 className="display-title mb-3 text-3xl font-bold text-[var(--sea-ink)] sm:text-5xl">
          Login to Continue
        </h1>
        <p className="m-0 max-w-3xl text-base text-[var(--sea-ink-soft)]">
          Use your Laravel credentials to get a Sanctum token and access Sweden Personer data.
        </p>
      </section>

      <section className="island-shell mt-6 rounded-2xl p-6">
        <form className="grid gap-3 sm:grid-cols-2" onSubmit={handleLogin}>
          <label className="flex flex-col gap-1 text-sm font-medium text-[var(--sea-ink-soft)]">
            Email
            <input
              type="email"
              required
              value={email}
              onChange={(event) => setEmail(event.target.value)}
              className="rounded-xl border border-[var(--line)] bg-white/80 px-3 py-2 text-[var(--sea-ink)] outline-none focus:border-[var(--lagoon-deep)]"
              placeholder="user@example.com"
            />
          </label>

          <label className="flex flex-col gap-1 text-sm font-medium text-[var(--sea-ink-soft)]">
            Password
            <input
              type="password"
              required
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              className="rounded-xl border border-[var(--line)] bg-white/80 px-3 py-2 text-[var(--sea-ink)] outline-none focus:border-[var(--lagoon-deep)]"
              placeholder="Your password"
            />
          </label>

          <label className="flex flex-col gap-1 text-sm font-medium text-[var(--sea-ink-soft)] sm:col-span-2">
            Device Name
            <input
              type="text"
              value={deviceName}
              onChange={(event) => setDeviceName(event.target.value)}
              className="rounded-xl border border-[var(--line)] bg-white/80 px-3 py-2 text-[var(--sea-ink)] outline-none focus:border-[var(--lagoon-deep)]"
              placeholder="TanStack Start Web"
            />
          </label>

          <div className="sm:col-span-2">
            <button
              type="submit"
              disabled={isLoading}
              className="rounded-full border border-[rgba(50,143,151,0.3)] bg-[rgba(79,184,178,0.16)] px-5 py-2.5 text-sm font-semibold text-[var(--lagoon-deep)] disabled:cursor-not-allowed disabled:opacity-60"
            >
              {isLoading ? 'Signing in...' : 'Sign In'}
            </button>
          </div>
        </form>
      </section>

      {error ? (
        <section className="island-shell mt-6 rounded-2xl border-red-300 bg-red-50/70 p-4 text-sm text-red-700">
          {error}
        </section>
      ) : null}
    </main>
  )
}