import { useEffect, useMemo, useState } from 'react'
import { createFileRoute, redirect, useNavigate } from '@tanstack/react-router'
import {
  clearAuthState,
  fetchCurrentUser,
  fetchSwedenPersoner,
  getAuthState,
  type SanctumAuthState,
  type SwedenPersonerItem,
  type SwedenPersonerResponse,
} from '../lib/api'

export const Route = createFileRoute('/sweden-personer')({
  beforeLoad: () => {
    if (typeof window === 'undefined') {
      return
    }

    if (!getAuthState()) {
      throw redirect({ to: '/login' })
    }
  },
  component: SwedenPersonerPage,
})

type SortColumn =
  | 'id'
  | 'personnamn'
  | 'fornamn'
  | 'efternamn'
  | 'adress'
  | 'postnummer'
  | 'postort'
  | 'kommun'
  | 'telefon'
  | 'alder'

type SortDirection = 'asc' | 'desc'

function SwedenPersonerPage() {
  const navigate = useNavigate()
  const [auth, setAuth] = useState<SanctumAuthState | null>(() => getAuthState())
  const [fornamn, setFornamn] = useState('')
  const [efternamn, setEfternamn] = useState('')
  const [postort, setPostort] = useState('')
  const [postnummer, setPostnummer] = useState('')
  const [results, setResults] = useState<SwedenPersonerResponse | null>(null)
  const [isCheckingSession, setIsCheckingSession] = useState(true)
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [sortColumn, setSortColumn] = useState<SortColumn>('id')
  const [sortDirection, setSortDirection] = useState<SortDirection>('asc')

  const currentPage = results?.meta?.current_page ?? 1
  const lastPage = results?.meta?.last_page ?? 1
  const totalRows = results?.meta?.total ?? 0

  const canSearch = useMemo(
    () => Boolean(auth?.token) && !isLoading,
    [auth?.token, isLoading],
  )

  const sortedRows = useMemo(() => {
    const rows = [...(results?.data ?? [])]

    rows.sort((left, right) => {
      const leftValue = (left[sortColumn] ?? '') as string | number
      const rightValue = (right[sortColumn] ?? '') as string | number

      if (typeof leftValue === 'number' && typeof rightValue === 'number') {
        return sortDirection === 'asc' ? leftValue - rightValue : rightValue - leftValue
      }

      const leftText = String(leftValue).toLocaleLowerCase('sv-SE')
      const rightText = String(rightValue).toLocaleLowerCase('sv-SE')
      const compared = leftText.localeCompare(rightText, 'sv-SE')
      return sortDirection === 'asc' ? compared : -compared
    })

    return rows
  }, [results?.data, sortColumn, sortDirection])

  useEffect(() => {
    let isActive = true

    async function validateSession() {
      if (!auth?.token) {
        setIsCheckingSession(false)
        await navigate({ to: '/login' })
        return
      }

      try {
        const user = await fetchCurrentUser(auth.token)

        if (!isActive) {
          return
        }

        setAuth((prev) => {
          if (!prev) {
            return prev
          }

          return {
            ...prev,
            user,
          }
        })

        await loadPage(1)
      } catch {
        clearAuthState()
        if (isActive) {
          setAuth(null)
          await navigate({ to: '/login' })
        }
      } finally {
        if (isActive) {
          setIsCheckingSession(false)
        }
      }
    }

    void validateSession()

    return () => {
      isActive = false
    }
  }, [])

  function toggleSort(column: SortColumn) {
    if (sortColumn === column) {
      setSortDirection((prev) => (prev === 'asc' ? 'desc' : 'asc'))
      return
    }

    setSortColumn(column)
    setSortDirection('asc')
  }

  function renderSortIndicator(column: SortColumn) {
    if (sortColumn !== column) {
      return ' [-]'
    }

    return sortDirection === 'asc' ? ' [ASC]' : ' [DESC]'
  }

  async function loadPage(page: number) {
    if (!auth?.token) {
      setError('Login is required before loading Sweden Personer data.')
      return
    }

    setIsLoading(true)
    setError(null)

    try {
      const response = await fetchSwedenPersoner({
        token: auth.token,
        page,
        perPage: 25,
        filters: {
          fornamn: fornamn.trim() || undefined,
          efternamn: efternamn.trim() || undefined,
          postort: postort.trim() || undefined,
          postnummer: postnummer.trim() || undefined,
        },
      })

      setResults(response)
    } catch (err) {
      const message = err instanceof Error ? err.message : 'Failed to load data'
      setError(message)

      if (message.toLowerCase().includes('unauthorized')) {
        setAuth(null)
      }
    } finally {
      setIsLoading(false)
    }
  }

  function handleLogout() {
    clearAuthState()
    setAuth(null)
    setResults(null)
    setError(null)
    void navigate({ to: '/login' })
  }

  function getCellValue(person: SwedenPersonerItem, column: SortColumn) {
    return person[column] ?? '-'
  }

  if (isCheckingSession) {
    return (
      <main className="page-wrap px-4 py-10">
        <section className="island-shell rounded-2xl p-6 text-sm text-[var(--sea-ink-soft)]">
          Validating session...
        </section>
      </main>
    )
  }

  return (
    <main className="page-wrap px-4 py-10">
      <section className="island-shell rounded-2xl p-6 sm:p-8">
        <p className="island-kicker mb-2">Laravel Sanctum</p>
        <h1 className="display-title mb-3 text-3xl font-bold text-[var(--sea-ink)] sm:text-5xl">
          Sweden Personer Data Table
        </h1>
        <p className="m-0 max-w-3xl text-base text-[var(--sea-ink-soft)]">
          Authenticate with your Laravel API using Sanctum token login, then browse rows from
          sweden_personer using the search endpoint.
        </p>
      </section>

      <section className="island-shell mt-6 rounded-2xl p-6">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <p className="m-0 text-sm text-[var(--sea-ink-soft)]">
            Logged in as <strong>{auth?.user.email}</strong>
          </p>
          <button
            type="button"
            onClick={handleLogout}
            className="rounded-full border border-[var(--line)] bg-white/70 px-4 py-2 text-sm font-semibold text-[var(--sea-ink)]"
          >
            Logout
          </button>
        </div>

        <div className="mt-5 grid gap-3 sm:grid-cols-4">
          <input
            type="text"
            value={fornamn}
            onChange={(event) => setFornamn(event.target.value)}
            placeholder="Filter fornamn"
            className="rounded-xl border border-[var(--line)] bg-white/80 px-3 py-2 text-sm text-[var(--sea-ink)] outline-none focus:border-[var(--lagoon-deep)]"
          />
          <input
            type="text"
            value={efternamn}
            onChange={(event) => setEfternamn(event.target.value)}
            placeholder="Filter efternamn"
            className="rounded-xl border border-[var(--line)] bg-white/80 px-3 py-2 text-sm text-[var(--sea-ink)] outline-none focus:border-[var(--lagoon-deep)]"
          />
          <input
            type="text"
            value={postort}
            onChange={(event) => setPostort(event.target.value)}
            placeholder="Filter postort"
            className="rounded-xl border border-[var(--line)] bg-white/80 px-3 py-2 text-sm text-[var(--sea-ink)] outline-none focus:border-[var(--lagoon-deep)]"
          />
          <input
            type="text"
            value={postnummer}
            onChange={(event) => setPostnummer(event.target.value)}
            placeholder="Filter postnummer"
            className="rounded-xl border border-[var(--line)] bg-white/80 px-3 py-2 text-sm text-[var(--sea-ink)] outline-none focus:border-[var(--lagoon-deep)]"
          />
        </div>

        <div className="mt-4 flex flex-wrap gap-2">
          <button
            type="button"
            onClick={() => loadPage(1)}
            disabled={!canSearch}
            className="rounded-full border border-[rgba(50,143,151,0.3)] bg-[rgba(79,184,178,0.16)] px-5 py-2.5 text-sm font-semibold text-[var(--lagoon-deep)] disabled:cursor-not-allowed disabled:opacity-60"
          >
            {isLoading ? 'Loading...' : 'Load Data'}
          </button>
        </div>

        <div className="mt-5 overflow-x-auto rounded-xl border border-[var(--line)] bg-white/70">
          <table className="w-full min-w-[900px] border-collapse text-sm">
            <thead>
              <tr className="border-b border-[var(--line)] text-left text-[var(--sea-ink)]">
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('id')} className="font-semibold">
                    ID{renderSortIndicator('id')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('personnamn')} className="font-semibold">
                    Personnamn{renderSortIndicator('personnamn')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('fornamn')} className="font-semibold">
                    Fornamn{renderSortIndicator('fornamn')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('efternamn')} className="font-semibold">
                    Efternamn{renderSortIndicator('efternamn')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('adress')} className="font-semibold">
                    Adress{renderSortIndicator('adress')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('postnummer')} className="font-semibold">
                    Postnummer{renderSortIndicator('postnummer')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('postort')} className="font-semibold">
                    Postort{renderSortIndicator('postort')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('kommun')} className="font-semibold">
                    Kommun{renderSortIndicator('kommun')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('telefon')} className="font-semibold">
                    Telefon{renderSortIndicator('telefon')}
                  </button>
                </th>
                <th className="px-3 py-2">
                  <button type="button" onClick={() => toggleSort('alder')} className="font-semibold">
                    Alder{renderSortIndicator('alder')}
                  </button>
                </th>
              </tr>
            </thead>
            <tbody>
              {sortedRows.map((person) => (
                <tr key={person.id} className="border-b border-[var(--line)]/70 text-[var(--sea-ink-soft)]">
                  <td className="px-3 py-2">{getCellValue(person, 'id')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'personnamn')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'fornamn')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'efternamn')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'adress')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'postnummer')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'postort')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'kommun')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'telefon')}</td>
                  <td className="px-3 py-2">{getCellValue(person, 'alder')}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        <div className="mt-4 flex flex-wrap items-center gap-3 text-sm text-[var(--sea-ink-soft)]">
          <span>
            Page {currentPage} / {lastPage}
          </span>
          <span>Total: {totalRows}</span>
          <button
            type="button"
            disabled={!canSearch || currentPage <= 1}
            onClick={() => loadPage(currentPage - 1)}
            className="rounded-full border border-[var(--line)] bg-white/70 px-4 py-2 font-semibold text-[var(--sea-ink)] disabled:cursor-not-allowed disabled:opacity-60"
          >
            Previous
          </button>
          <button
            type="button"
            disabled={!canSearch || currentPage >= lastPage}
            onClick={() => loadPage(currentPage + 1)}
            className="rounded-full border border-[var(--line)] bg-white/70 px-4 py-2 font-semibold text-[var(--sea-ink)] disabled:cursor-not-allowed disabled:opacity-60"
          >
            Next
          </button>
        </div>
      </section>

      {error ? (
        <section className="island-shell mt-6 rounded-2xl border-red-300 bg-red-50/70 p-4 text-sm text-red-700">
          {error}
        </section>
      ) : null}
    </main>
  )
}