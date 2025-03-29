# Mods

## Purpose
Define what a "mod" represents in the context of Project Babel and how it relates to translations.

## Scope
Covers mod structure, translation lifecycle, and associations with users and roles.

## What is a Mod?

A "mod" refers to a game modification file (or package) that contains content to be translated.  
Each mod can be translated into multiple languages through the platform.

## Structure

```json
{
  "id": "mod_001",
  "name": "Dark Empire",
  "game": "Stellaris",
  "version": "3.10",
  "original_language": "en",
  "files": ["events.txt", "names.yml", "descriptions.csv"],
  "metadata": {
    "author": "DukeVanDark",
    "uploaded_by": "user_123",
    "upload_date": "2024-03-01"
  }
}
```

## Translation Workflow
Mod is uploaded by a user.

A translation project is created for each target language.

Translators and moderators are assigned to the translation.

Progress is tracked by file, language, and user contribution. 