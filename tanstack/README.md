# TanStack Start + Laravel Sanctum

This app lives in tanstack and connects to your Laravel API backend.

Implemented features:

- Sanctum token authentication using POST /api/sanctum/token
- Sweden Personer table page at /sweden-personer
- Server-side pagination and basic filters (fornamn, efternamn, postort, postnummer)

## Requirements

- Node.js 22.12+ is recommended by current TanStack Start packages
- Laravel API running and reachable from this app

## Configure API URL

Set the backend URL in your shell before running dev/build:

```bash
export VITE_API_BASE_URL=http://127.0.0.1:8000
```

If not set, the app defaults to http://127.0.0.1:8000.

## Run

```bash
npm install
npm run dev
```

Open http://localhost:3000 and go to /sweden-personer.

## Build

```bash
npm run build
```

## Laravel endpoints used

- POST /api/sanctum/token
- GET /api/sweden-personer/search

## Notes

- The app stores the Sanctum token and user info in browser localStorage.
- If a request returns 401, the local auth state is cleared and user must sign in again.
