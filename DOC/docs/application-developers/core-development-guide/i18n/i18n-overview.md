# Internationalization (i18n) Overview

Two distinct mechanisms are involved in i18n within the eQual framework:
    
*   **Data Translation**: Translating the *values* stored in the database (e.g., a product description available in English and French). See [Model Data Translation](i18n-model-data.md).
*   **Interface Translation**: Translating the *structure* and *labels* when objects are rendered within a View (e.g., field labels, menu items, help text). See [Interface Translation](i18n-interface.md).

## Naming Conventions

Language identification uses the [ISO 639-1](https://www.iso.org/iso-639-language-code) code for the language and optionally the [ISO 3166-1](https://www.iso.org/iso-3166-country-codes.html) code for the country, separated by a dash.

**Syntax**: `{ISO639-1 lang}[-{ISO-3166-1 country}]`

**Examples**:

*   `fr`: Standard French
*   `fr-CA`: Canadian French
*   `zh-CN`: Simplified Chinese

These codes are used consistently across directory structures and API parameters.

---