## heartsong/backup-exclusion

Keeps the app's database, storage, and logs out of iCloud and device backups (iOS only).

- Call `Heartsong\BackupExclusion\Facades\BackupExclusion::apply()` on every app start; it returns `null` outside the native app.
- `BackupExclusion::isExcluded()` reports the current state.
