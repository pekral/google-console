# Changelog

All notable changes to this project will be documented in this file.

---

## [0.4.2] - 2025-08-24
### Added
- **New Rules Added:**
  - `SlevomatCodingStandard.Attributes.AttributeAndTargetSpacing` - Controls spacing around attributes and targets
  - `SlevomatCodingStandard.Attributes.AttributesOrder` - Enforces alphabetical ordering of attributes
  - `SlevomatCodingStandard.Attributes.RequireAttributeAfterDocComment` - Requires attributes to be placed after doc comments
  - `SlevomatCodingStandard.Classes.DisallowMultiConstantDefinition` - Prevents multiple constant definitions in one statement
  - `SlevomatCodingStandard.Classes.DisallowMultiPropertyDefinition` - Prevents multiple property definitions in one statement
  - `SlevomatCodingStandard.Classes.RequireAbstractOrFinal` - Requires classes to be abstract or final
  - `SlevomatCodingStandard.Commenting.DeprecatedAnnotationDeclaration` - Controls deprecated annotation declarations
  - `SlevomatCodingStandard.ControlStructures.RequireNullSafeObjectOperator` - Enforces null safe object operator
  - `SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition` - Requires single-line conditions for short lines
  - `SlevomatCodingStandard.Files.LineLength` - Limits line length (max 156 chars, ignores comments/imports)
  - `SlevomatCodingStandard.Functions.DisallowTrailingCommaInClosureUse` - Disallows trailing commas in closure use
  - `SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator` - Requires numeric literal separators
  - `SlevomatCodingStandard.Operators.RequireOnlyStandaloneIncrementAndDecrementOperators` - Enforces standalone increment/decrement operators
  - `SlevomatCodingStandard.PHP.DisallowReference` - Disallows references
  - `SlevomatCodingStandard.PHP.RequireExplicitAssertion` - Requires explicit assertions
  - `SlevomatCodingStandard.PHP.UselessParentheses` - Prevents useless parentheses
  - `SlevomatCodingStandard.Strings.DisallowVariableParsing` - Disallows variable parsing in strings

### Changed
- Renamed `Example/` folder to `Examples/` for better organization
- Refactored all example files to show only correct edge cases
- Simplified and cleaned up example files across all rule categories
- Updated ruleset.xml with better organization and cleanup
- Enhanced composer.json with improved build scripts
- Improved .gitignore file management

### Fixed
- Fixed reading sniffs from ruleset.xml file
- Resolved deprecated error handling
- Cleaned up coverage reports and test artifacts
- Fixed various example file inconsistencies

### Technical Improvements
- Added comprehensive test coverage for commands and sniffs
- Implemented proper test result caching
- Enhanced build system with automated analysis
- Improved CI/CD pipeline with better testing

---

## [0.4.1] - 2025-05-06
### Changed
- All documentation, README, FAQ, and metadata are now fully in English
- README improved and cleaned up (badges, usage, configuration, FAQ)
- License file and reference in README ensured
- Composer metadata and keywords improved for better discoverability

---

## [0.4.0] - 2025-05-04
### Added
- Complete split of valid examples into sections by rules (Namespaces, TypeHints, Classes, ...)
- Automated CI check via GitHub Actions
- Modern README with badges, examples, and FAQ
- Metadata in composer.json (homepage, support, funding, topics, ...)
- Issue template for new rule proposals
- Missing valid examples for all rules in ruleset.xml

### Changed
- All comments unified and translated to English
- Improved directory structure (Valid/ sections)
- Namespace in all examples matches the section

### Removed
- Invalid folder and all invalid examples

---

## [0.3.0] - 2024-12-10
### Added
- First version of splitting examples into Examples/Valid folder
- Basic GitHub Actions workflow

---

## [0.2.0] - 2024-09-01
### Added
- First version of ruleset.xml based on Slevomat Coding Standard
- Basic README and composer.json

---

## [0.1.0] - 2024-06-01
### Added
- Repository initialization
- First custom rules for PHPCS 