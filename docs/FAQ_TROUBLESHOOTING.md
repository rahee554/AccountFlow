# AccountFlow Commands — FAQs & Troubleshooting

## ❓ Frequently Asked Questions

### Q1: What's the difference between the two commands?

**`accountflow:link`**
- Copies package files to project
- Models, migrations, seeders, views, assets
- Run once per package install/update
- Low risk (copies code, doesn't touch database)

**`accountflow:db`**
- Sets up database with migrations & seeders
- Interactive status checking
- Run whenever you need to set up/reset database
- Medium risk (modifies database, especially with `--fresh`)

---

### Q2: Do I need to run `accountflow:link` every time?

**No.** Run it:
- ✅ After `composer require artflow-studio/accountflow`
- ✅ After `composer update artflow-studio/accountflow`
- ✅ If you delete linked files accidentally
- ❌ Every time before running `accountflow:db`

---

### Q3: Can I run these commands in production?

**`accountflow:link`:** Yes, safe to run in production

**`accountflow:db`:** 
- ⚠️ Without `--fresh`: Safe (only adds pending migrations)
- ❌ With `--fresh`: DANGEROUS (drops all tables!) — Use only in dev/staging

---

### Q4: Why should I use `accountflow:db` instead of running commands separately?

**Separate commands:**
```bash
php artisan migrate
php artisan db:seed
php artisan db:seed --class=AccountsTableSeeder
# 3 commands, manual, no feedback about what's happening
```

**With `accountflow:db`:**
```bash
php artisan accountflow:db
# 1 command, automated, full status visibility
# Shows what migrations/seeders will run
# Asks for confirmation
# Provides summary
```

---

### Q5: What if seeder files don't copy properly?

**Symptoms:**
- `php artisan accountflow:link` shows: "✓ Copied {0} file(s)"
- Or file exists but can't find the class

**Solutions:**
1. Verify package path exists:
   ```bash
   ls vendor/artflow-studio/accountflow/src/database/seeders/
   ```

2. Run with --force:
   ```bash
   php artisan accountflow:link --force
   ```

3. Refresh autoload:
   ```bash
   composer dump-autoload
   ```

4. Check file permissions:
   ```bash
   chmod 755 database/seeders/AccountsTableSeeder.php
   ```

---

### Q6: Can I edit the copied seeder files?

**Yes!** Once copied to `database/seeders/`, they're part of your project.

Edit them as needed. Changes won't affect the package version.

**But:** If you run `accountflow:link --force` again, your edits won't be overwritten (file already exists). To get package updates to files, delete your version first.

---

### Q7: What happens to my data with `--fresh`?

**Before `--fresh`:**
```
Database Tables:
├── users (contains data)
├── accounts (contains data)
├── transactions (contains data)
└── ...
```

**After `php artisan accountflow:db --fresh`:**
```
Database Tables:
├── users (fresh, only seeders data)
├── accounts (fresh, only seeders data)
├── transactions (fresh, only seeders data)
└── ...

⚠️  ALL DATA LOST (unless backed up first!)
```

---

### Q8: How do I know which files will be used?

The `accountflow:db` command **shows you** before running anything:

```
📋 Step 1: Checking for AccountFlow migrations...
  Project: Found 2 ✅
  Package: Found 0

📋 Step 2: Checking for AccountFlow seeders...
  Project: Found 1 ✅
  Package: Found 0

📋 Step 3: Determining sources...
  ✓ Will use migrations from PROJECT
  ✓ Will use seeders from PROJECT
```

---

### Q9: Can I skip the confirmation in interactive mode?

**Yes!** Use `--force` flag:

```bash
php artisan accountflow:db --force           # Skip prompt
php artisan accountflow:db --fresh --force   # Skip prompt + use migrate:fresh
```

---

### Q10: What if files don't get linked on Windows?

**Windows symlink issues:**
```
Could not create symlink/junction, copying files instead:
mklink failed: Cannot create a file when that file already exists.
```

**Reason:** Junction/symlink already exists

**Solution:**
```bash
# Option 1: Delete existing link and re-link
php artisan accountflow:link --force

# Option 2: Run with elevated privileges
# Right-click PowerShell → "Run as Administrator"
# Then run the command
```

---

## 🐛 Common Issues & Fixes

### Issue 1: "Class not found" after linking

**Error:**
```
Class Database\Seeders\AccountsTableSeeder not found
```

**Cause:** Autoloader cache not updated

**Fix:**
```bash
composer dump-autoload
php artisan accountflow:db
```

---

### Issue 2: "File already exists" warning

**Output:**
```
⚠ File already exists: database/seeders/AccountsTableSeeder.php
→ Skip [other file]
```

**Cause:** File exists and `--force` not used

**Fix Option 1:** Approve overwrite (during interactive)
```
Overwrite? (yes/no) [no]: yes
```

**Fix Option 2:** Force overwrite
```bash
php artisan accountflow:link --force
```

**Fix Option 3:** Delete and re-link
```bash
rm database/seeders/AccountsTableSeeder.php
php artisan accountflow:link
```

---

### Issue 3: Migrations don't run

**Error:**
```
No migrations to run. Discovered 0 migrations.
```

**Cause 1:** Migrations not linked yet
**Fix 1:** Run `php artisan accountflow:link --force`

**Cause 2:** Migrations already ran before
**Fix 2:** Use `--fresh` to reset
```bash
php artisan accountflow:db --fresh
```

---

### Issue 4: Seeder data not inserted

**Error:**
```
✓ Seeding completed
# But database tables are empty
```

**Cause 1:** Migrations didn't create tables
**Fix 1:** Check migration errors above
```bash
php artisan accountflow:db --fresh
```

**Cause 2:** Seeder file has errors
**Fix 2:** Run seeder directly to see error
```bash
php artisan db:seed --class=AccountsTableSeeder
```

---

### Issue 5: "Config not found" during seed

**Error:**
```
config('accountflow.categories') not found
```

**Cause:** Config not published

**Fix:**
```bash
php artisan vendor:publish --provider="ArtflowStudio\AccountFlow\AccountFlowServiceProvider" --tag=accountflow-config
php artisan accountflow:db --fresh
```

---

### Issue 6: Database permission error

**Error:**
```
SQLSTATE[HY000]: General error: 1 AUTOINCREMENT is out of range
```

**Cause:** Database permissions issue

**Fix:**
1. Check database user permissions
2. Ensure user can CREATE TABLE, INSERT, ALTER
3. Try again:
```bash
php artisan accountflow:db --fresh --force
```

---

## 🔧 Manual Troubleshooting Steps

### Verify Package Installation

```bash
# Check if package exists
composer show artflow-studio/accountflow

# Verify package files
ls vendor/artflow-studio/accountflow/src/database/seeders/

# Should show: AccountsTableSeeder.php
```

---

### Verify File Copying

```bash
# Check if seeder copied to project
ls database/seeders/AccountsTableSeeder.php

# Should exist and be readable
cat database/seeders/AccountsTableSeeder.php | head -5
```

---

### Test Seeder Directly

```bash
# Run seeder with verbose output
php artisan db:seed --class=AccountsTableSeeder -vvv

# Look for errors in output
```

---

### Check Database State

```bash
# In Laravel Tinker
php artisan tinker

> Schema::getTables()
> DB::table('ac_categories')->count()
> DB::table('accounts')->get()

# Exit with Ctrl+D
```

---

### Reset Everything

```bash
# Complete reset from scratch
rm database/seeders/AccountsTableSeeder.php
php artisan accountflow:link --force
composer dump-autoload
php artisan accountflow:db --fresh --force
```

---

## 📊 Verification Checklist

After running commands, verify:

### After `php artisan accountflow:link --force`

- [ ] `database/seeders/AccountsTableSeeder.php` exists
- [ ] `app/Models/AccountFlow/` folder exists
- [ ] `app/Livewire/AccountFlow/` folder exists
- [ ] `resources/views/vendor/artflow-studio/accountflow/` exists
- [ ] `public/vendor/artflow-studio/accountflow/` exists
- [ ] No errors in output

---

### After `php artisan accountflow:db`

- [ ] "Migrations completed" message shown
- [ ] "Seeding completed" message shown
- [ ] "Database setup completed successfully" message shown
- [ ] Database file size increased (new tables)
- [ ] No SQL errors shown

---

### Final Verification

```bash
# Check tables were created
php artisan tinker
> Schema::getTables()  # Should include 'accounts', 'ac_categories', etc.

# Check data was seeded
> DB::table('accounts')->count()  # Should show count
> DB::table('ac_categories')->first()  # Should show data
```

---

## ⚡ Performance Tips

### For Large Databases

```bash
# Disable query logging during seed (faster)
php artisan config:cache
php artisan accountflow:db --force

# Clear cache after (if needed)
php artisan cache:clear
```

---

### For CI/CD Pipelines

```bash
# In your deployment script
php artisan accountflow:link --force
php artisan accountflow:db --fresh --force

# All automated, no interaction needed
```

---

### For Local Development

```bash
# Quick reset during development
php artisan accountflow:db --fresh

# Still shows status, but allows skipping confirmation if needed
# (doesn't use --force, so will ask once)
```

---

## 📞 Getting Help

### If commands fail:

1. **Check error message** - Read output carefully
2. **Follow suggested fixes** - Commands suggest solutions
3. **Verify prerequisites** - Run link first, then db
4. **Test components** - Run individual seeders/migrations
5. **Check logs** - `storage/logs/laravel.log`

---

### Common Log Errors

**In `storage/logs/laravel.log`:**

```
[2025-11-09 10:30:15] local.ERROR: Class 'Database\Seeders\AccountsTableSeeder' not found
  → Solution: composer dump-autoload

[2025-11-09 10:30:20] local.ERROR: SQLSTATE[42S02]: Table 'accounts' doesn't exist
  → Solution: Migrations didn't run, check migration output

[2025-11-09 10:30:25] local.ERROR: config('accountflow.') not found
  → Solution: Publish config file
```

---

## 🎯 Decision Matrix

| Situation | Command | Options |
|-----------|---------|---------|
| First install | `accountflow:link` then `accountflow:db` | `--force` on both |
| Update package | `accountflow:link --force` then `accountflow:db` | Optional `--fresh` |
| Reset database | `accountflow:db` | `--fresh --force` |
| Add new migrations | `accountflow:link` then `accountflow:db` | No `--fresh` |
| Production deploy | `accountflow:link --force` then `accountflow:db --force` | Never use `--fresh` |
| Development workflow | `accountflow:db` | Use `--fresh` during dev |

---

**Need more help?** Check the main documentation files:
- `COMMANDS_GUIDE.md` — Comprehensive guide
- `WORKFLOW_DIAGRAMS.md` — Visual walkthroughs
- `IMPLEMENTATION_SUMMARY.md` — Technical details
