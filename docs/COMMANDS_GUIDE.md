# AccountFlow Package: Updated Link & DB Commands

This document covers the improvements made to the AccountFlow package's console commands.

---

## 🎯 Problem Solved

**Original Issues:**
1. ❌ Seeder files were not being linked to the project during `accountflow:link`
2. ❌ No proper fallback mechanism for database migrations/seeders
3. ❌ No interactive way to manage database setup with status visibility

**Solution Implemented:**
- ✅ Seeder files now copied individually to project `database/seeders/` (no nested links)
- ✅ New interactive `accountflow:db` command with priority-based source selection
- ✅ Full status reporting for migrations and seeders
- ✅ Automatic fallback: Project files > Package files

---

## 📦 Updated Commands

### 1. `accountflow:link` — Enhanced

**What Changed:**
- Added `seeders` entry in links configuration
- Seeders are now **copied as individual files** (not as a directory link)
- Prevents nested symlinks that could cause issues on Windows

**How It Works:**
```
├── package/database/seeders/AccountsTableSeeder.php
│   ↓ (copy individual file)
└── project/database/seeders/AccountsTableSeeder.php  ✅
```

**Command:**
```bash
php artisan accountflow:link
php artisan accountflow:link --force  # Skip confirmations
```

**Output:**
```
✓ Linked: app/Models/AccountFlow → ...
✓ Merged: database/migrations → ...
✓ Copied {1} file(s): database/seeders → ...  ← NEW!
✓ Copied: resources/views → ...
✅ AccountFlow package files linked successfully!
```

---

### 2. `accountflow:db` — NEW Interactive Database Command

**Purpose:**
Interactive database setup with status checking, smart source detection, and guided execution.

**Features:**
- 📋 **Step 1:** Scans project and package for AccountFlow migrations
- 📋 **Step 2:** Scans project and package for AccountFlow seeders
- 📋 **Step 3:** Automatically selects source (Project > Package priority)
- 🚀 **Step 4:** Runs migrations (or migrate:fresh)
- 🌱 **Step 5:** Runs seeders
- 📊 Displays full summary with sources and counts

**Commands:**

```bash
# Interactive (asks for confirmation)
php artisan accountflow:db

# Auto-run without prompts
php artisan accountflow:db --force

# Use migrate:fresh instead of migrate
php artisan accountflow:db --fresh
php artisan accountflow:db --fresh --force

# Combine options
php artisan accountflow:db --fresh --force
```

**Sample Output:**

```
═══════════════════════════════════════════════════════════
  🗂️  AccountFlow Database Setup Command
═══════════════════════════════════════════════════════════

📋 Step 1: Checking for AccountFlow migrations...
  ✓ Project migrations folder: .../database/migrations
    ✓ Found 2 AccountFlow migration(s) in project
      - 9900_create_accounts_tables.php
      - 9901_add_columns_to_account.php
  ✓ Package migrations folder: .../vendor/.../database/migrations
    ⚠ No AccountFlow migrations found in package

📋 Step 2: Checking for AccountFlow seeders...
  ✓ Project seeders folder: .../database/seeders
    ✓ Found 1 AccountFlow seeder(s) in project
      - AccountsTableSeeder.php
  ✓ Package seeders folder: .../vendor/.../database/seeders
    ⚠ No AccountFlow seeders found in package

📋 Step 3: Determining migration sources (Priority: Project > Package)...
  ✓ Will use migrations from PROJECT
  ✓ Will use seeders from PROJECT

⚠️  This will:
  - Run: php artisan migrate
  - Run: php artisan db:seed
  - Seed AccountFlow data

Do you want to continue? (yes/no) [no]: yes

🚀 Step 4: Running migrations...
  → Running: php artisan migrate
  ✓ Migrations completed

🌱 Step 5: Running seeders...
  → Running: php artisan db:seed
  ✓ Seeding completed

═══════════════════════════════════════════════════════════
  ✅ AccountFlow database setup completed successfully!
═══════════════════════════════════════════════════════════

📊 Summary:
  - Migrations Source: project
  - Seeders Source: project
  - Migrations: 2 total
  - Seeders: 1 total

💡 Next steps:
  - Check database: verify tables created in your database
  - Run tests: php artisan test
```

---

## 🔍 How File Detection Works

### Migrations & Seeders Detection

The command searches for files containing **"account"** (case-insensitive) in the filename:

**Detected patterns:**
- ✅ `9900_create_accounts_tables.php`
- ✅ `9901_add_columns_to_account.php`
- ✅ `AccountsTableSeeder.php`
- ✅ `AccountFlowSeeder.php`
- ✅ `account_*_seeder.php`

**Priority Logic:**
1. If migrations/seeders found in **project** → Use project versions
2. Else, if found in **package** → Use package versions
3. Else → Warn and continue (migrations may not exist yet)

---

## 🔗 Symlink vs Copy Behavior

### Why Individual File Copy for Seeders?

On **Windows**, creating a symlink to a directory and then placing files inside it can cause:
- ❌ Nested junction errors
- ❌ File resolution issues
- ❌ Permission problems

**Solution:** Copy individual files directly to the target directory.

### Seeder File Linking Details

| Item | Behavior |
|------|----------|
| **Source** | `package/database/seeders/*.php` |
| **Target** | `project/database/seeders/` |
| **Type** | Individual file copy |
| **Merge** | ✅ Files placed directly in existing seeders folder |
| **Skip Existing** | ✅ Won't overwrite unless `--force` used |
| **Symlink** | ❌ Not created (copy only) |

---

## 🚀 Typical Workflow

### First-Time Setup

```bash
# 1. Install package (composer require ...)
# 2. Link package files to project
php artisan accountflow:link --force

# 3. Verify seeders copied
ls database/seeders/

# 4. Run database setup interactively
php artisan accountflow:db

# 5. Confirm and wait for completion
# → Migrations run
# → Seeders run
# → Tables created with data
```

### Subsequent Runs (e.g., after git pull)

```bash
# If new migrations added to package:
php artisan accountflow:link --force

# Run fresh database setup
php artisan accountflow:db --fresh --force
```

### Update Existing Database

```bash
# Run pending migrations only
php artisan accountflow:db

# Or use migrate:fresh (resets all)
php artisan accountflow:db --fresh
```

---

## 📋 Code Changes Summary

### Files Modified

1. **`AccountFlowLinkCommand.php`**
   - Added `seeders` entry in `$links` array
   - Added `copy_files_only` parameter
   - New logic to copy individual files only
   - Prevents nested symlink creation

2. **`AccountFlowServiceProvider.php`**
   - Registered new `AccountFlowDbCommand` class

### Files Created

3. **`AccountFlowDbCommand.php`** (NEW)
   - Interactive database setup command
   - Scans for migrations and seeders
   - Priority-based source selection
   - Detailed status reporting

---

## ⚙️ Configuration

### Detecting Account-Related Files

Edit `findAccountMigrations()` or `findAccountSeeders()` in `AccountFlowDbCommand.php` to customize detection:

```php
private function findAccountMigrations($path)
{
    // Currently matches files with "account" in filename
    // Modify the strpos() check to match different patterns
    
    if (strpos($filename, 'account') !== false && $file->getExtension() === 'php') {
        // Add to results
    }
}
```

---

## 🐛 Troubleshooting

### "No AccountFlow migrations found in package"

**Reason:** Package migrations haven't been linked yet.

**Solution:**
```bash
php artisan accountflow:link --force
```

Then the next run of `accountflow:db` will find them.

---

### Seeder File Not Copied

**Reason:** File already exists and `--force` wasn't used.

**Solution:**
```bash
php artisan accountflow:link --force
```

Or manually delete the existing seeder first.

---

### Database Errors During Seed

**Reason:** Migrations didn't run properly.

**Solution:**
```bash
# Verify tables exist
php artisan tinker
> Schema::getTables()

# Or re-run with fresh
php artisan accountflow:db --fresh --force
```

---

## 📝 Notes for Package Developers

### When Adding New Seeders to Package

1. Place seeder in `src/database/seeders/`
2. Name it with "account" in the filename (e.g., `AccountEquitySeeder.php`)
3. Run `php artisan accountflow:link --force` in host project
4. Seeder will be copied to `database/seeders/`
5. Run `php artisan accountflow:db` to execute

### When Adding New Migrations

1. Place migration in `src/database/migrations/`
2. Name it with "account" in the filename (e.g., `9902_add_account_features.php`)
3. Run `php artisan accountflow:link --force`
4. Run `php artisan accountflow:db` to execute

### Testing the Commands

```bash
# Test link command
php artisan accountflow:link --force

# Test db command (interactive)
php artisan accountflow:db

# Test db command (auto)
php artisan accountflow:db --fresh --force
```

---

## 🎓 Best Practices

✅ **Do:**
- Run `accountflow:link` after updating package
- Use `--fresh` when resetting entire database
- Check status output before confirming
- Keep seeders for development/testing data

❌ **Don't:**
- Edit copied seeders in vendor folder (edit in `database/seeders/`)
- Mix package and project migrations in confusing ways
- Run commands in production without understanding impact

---

## 🔄 Version & Compatibility

- **Framework:** Laravel 11+ (Livewire 3.6+)
- **PHP:** 8.1+
- **Tested On:** Windows (PowerShell), Linux/macOS (Bash)
- **Symlink Support:** Fallback to copy if mklink fails

---

**Questions or Issues?** Contact package maintainers or review command source code in:
- `src/app/Console/AccountFlowLinkCommand.php`
- `src/app/Console/AccountFlowDbCommand.php`
