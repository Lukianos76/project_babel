# 🛣️ Project Babel – Roadmap

## Phase 1 — Foundation

- [x] Write complete technical documentation ✅
- [x] Define architecture, roles, features
- [x] Review permissions & mod workflow

## Phase 2 — Base API + Auth

- [x] Initialize Symfony project
- [x] Set up Docker or Symfony local server
- [x] Configure database (PostgreSQL)
- [ ] Implement JWT Authentication (LexikJWT)
- [ ] Create base User entity with role system
- [ ] Seed initial roles (admin, moderator, user)

## Phase 3 — Mod and Translation Core

- [ ] Create Mod entity, upload system
- [ ] Create TranslationProject entity (per language)
- [ ] Add role assignment per mod
- [ ] Expose API endpoints:
  - `GET /mods`
  - `POST /mods`
  - `GET /translations`
  - `POST /translations`
- [ ] Integrate DeepL API for machine translation

## Phase 4 — Review & Quality System

- [ ] Implement suggestion & comment model
- [ ] Approve/reject workflow
- [ ] Versioning for translated content

## Phase 5 — Admin Interface

- [ ] Add Symfony UX or Vue-based admin panel
- [ ] Manage users/mods/translations via interface
- [ ] Add analytics (Grafana, Prometheus)
- [ ] Secure API access (rate limiting, audit logs) 