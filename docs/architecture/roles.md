# Roles and Permissions

## Purpose
Define global application roles and mod-level translation roles in Project Babel.

## Global Roles

| Role             | Description                        | Admin UI | Manage Users | View Logs | Access API |
|------------------|------------------------------------|----------|--------------|-----------|------------|
| Admin            | Full access to everything          | ✅       | ✅           | ✅        | ✅         |
| Moderator        | Manages translations and users     | ✅       | ✅ (limited) | ✅        | ✅         |
| Standard User    | Uploads mods, contributes to translation | ❌  | ❌           | ❌        | ✅         |

## Translation Roles (per mod or language)

Each translation project (per mod + per language) can have local roles.

| Role               | Rights                                      |
|--------------------|---------------------------------------------|
| Translator         | Edit translations                           |
| Proofreader        | Suggest corrections                         |
| Language Moderator | Validate/approve final strings              |
| Viewer             | Read-only access                            |

## Role Assignment

- A user may have:
  - Global `Moderator` rights + local `Translator` rights on Mod A
  - No rights at all on Mod B

- Roles are stored in a pivot table:
  `mod_translation_roles(user_id, mod_id, role, language)` 