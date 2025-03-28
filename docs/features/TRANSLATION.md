# Translation Management

## Overview

Project Babel provides a comprehensive system for managing translations of video game mods. This document outlines the core translation management features, workflows, and best practices.

## Core Features

### Translation Management
- Multi-language support
- Version control for translations
- Translation memory
- Glossary management
- Quality assurance tools
- Collaboration features

### File Support
- JSON files
- YAML files
- INI files
- PO/MO files
- Custom format support

## Workflow

### 1. Project Setup
```yaml
# config/packages/translation.yaml
translation:
    default_locale: 'en'
    fallbacks: ['en']
    paths:
        - '%kernel.project_dir%/translations'
    loaders:
        json: true
        yaml: true
        ini: true
        po: true
```

### 2. Translation Process
1. **File Upload**
   - Upload source files
   - Automatic format detection
   - Validation of file structure

2. **Translation Assignment**
   - Assign translators
   - Set deadlines
   - Define quality requirements

3. **Translation Work**
   - Access to translation interface
   - Reference materials
   - Translation memory suggestions

4. **Review Process**
   - Peer review
   - Quality checks
   - Final approval

### 3. Quality Control
```php
class TranslationQualityChecker
{
    public function check(string $translation, string $source): array
    {
        return [
            'completeness' => $this->checkCompleteness($translation),
            'consistency' => $this->checkConsistency($translation),
            'terminology' => $this->checkTerminology($translation),
            'formatting' => $this->checkFormatting($translation)
        ];
    }
}
```

## Translation Interface

### Features
- Split-screen view
- Translation memory
- Glossary integration
- Context preview
- Keyboard shortcuts
- Auto-save

### Keyboard Shortcuts
- `Ctrl + Enter`: Save translation
- `Ctrl + Space`: Show suggestions
- `Ctrl + G`: Open glossary
- `Ctrl + R`: Show reference

## Translation Memory

### Features
- Fuzzy matching
- Context-based suggestions
- Quality scoring
- Import/Export support

### Usage
```php
class TranslationMemory
{
    public function findSuggestions(string $text, string $language): array
    {
        return [
            'exact_matches' => $this->findExactMatches($text, $language),
            'fuzzy_matches' => $this->findFuzzyMatches($text, $language),
            'context_matches' => $this->findContextMatches($text, $language)
        ];
    }
}
```

## Glossary Management

### Features
- Term management
- Term variants
- Usage examples
- Term relationships

### Structure
```yaml
terms:
  - term: "Inventory"
    translations:
      en: "Inventory"
      fr: "Inventaire"
      de: "Inventar"
    context: "Game UI"
    examples:
      - "Open inventory"
      - "Inventory management"
```

## Quality Assurance

### Automated Checks
- Missing translations
- Format consistency
- Terminology compliance
- Character limits
- HTML tags

### Manual Review
- Cultural appropriateness
- Context accuracy
- Style consistency
- Technical accuracy

## Collaboration Features

### Team Management
- Role-based access
- Activity tracking
- Communication tools
- Task assignment

### Workflow States
1. Draft
2. In Progress
3. Review
4. Approved
5. Published

## Export/Import

### Supported Formats
- JSON
- YAML
- PO/MO
- XLIFF
- CSV

### Export Options
```php
class TranslationExporter
{
    public function export(string $format, array $translations): string
    {
        return match($format) {
            'json' => $this->exportToJson($translations),
            'yaml' => $this->exportToYaml($translations),
            'po' => $this->exportToPo($translations),
            default => throw new \InvalidArgumentException('Unsupported format')
        };
    }
}
```

## Best Practices

### Translation Guidelines
- Maintain consistent terminology
- Preserve formatting
- Consider cultural context
- Follow style guide
- Use translation memory

### Quality Guidelines
- Regular reviews
- Terminology updates
- Style consistency
- Context verification
- Technical accuracy

## Integration

### API Endpoints
```yaml
/api/v1/translations:
    get:
        summary: List translations
        parameters:
            - name: locale
              in: query
              required: true
              schema:
                type: string
    post:
        summary: Create translation
        requestBody:
            required: true
            content:
                application/json:
                    schema:
                        type: object
                        properties:
                            key:
                                type: string
                            value:
                                type: string
                            locale:
                                type: string
```

### Webhooks
- Translation updates
- Review completions
- Quality check results
- Export completions

## Support

### Resources
- Style guides
- Terminology databases
- Reference materials
- Training documentation

### Help
- Documentation
- Support tickets
- Community forums
- Training sessions 