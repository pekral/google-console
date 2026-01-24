# Changelog

All notable changes to `arch-app-services` will be documented in this file.

## [Unreleased] - 2026-01-24


- 🐛 **Fixed**: composer scripts fix
- 🔧 **Changed**: composer update dependencies
- ✨ **Added**: load Google data
- 🔧 **Changed**: update dependencies
- 🔧 **Changed**: initialize project from php-skeleton template

## [Unreleased] - 2026-01-11

- 🎉 **Added**: Initial release with Google Search Console API wrapper
- 🎉 **Added**: `GoogleConsole` class with methods: `getSiteList`, `getSite`, `getSearchAnalytics`, `inspectUrl`
- 🎉 **Added**: Typed DTOs: `Site`, `SearchAnalyticsRow`, `UrlInspectionResult`
- 🎉 **Added**: CLI commands: `list-sites`, `get-site`, `search-analytics`, `inspect-url`
- 🎉 **Added**: Full test coverage with Pest

