# Commit Log

## December 7, 2025

### Sidebar Translation Refactor
- Updated sidebar translation keys in all language files (`ar`, `en`, `id`) to use English-friendly keys for contributors:
  - `institusi` → `institution`
  - `jurusan` → `major`
  - `departemen` → `department`
  - `aplikasi` → `applications`
  - `ganti_tema` → `switch_theme`
- Updated `resources/views/components/sidebar.blade.php` to use new translation keys for sidebar items and settings modal.

### Multi-language Modal & Dashboard
- Ensured all modal and dashboard texts use translation keys and are covered in all language files.

### Contributor Documentation
- Created multilingual README files in `docs/README.{en,id,ar}.md` for contributors.

### Middleware & Session
- Registered locale middleware in `bootstrap/app.php` for Laravel 12.
- Debugged and fixed session-based language switching.

---

**Next Steps:**
- Continue refactoring for consistency and contributor clarity.
- Add more documentation as needed.
