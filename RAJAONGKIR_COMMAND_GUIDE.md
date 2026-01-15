# RajaOngkir Data Fetch - Command Reference

## Command Overview

Command untuk fetch semua location data dari RajaOngkir API dan simpan ke database atau JSON file.

```bash
php artisan rajaongkir:fetch [options]
```

## Usage

### Basic Usage (Save to Database Only)
```bash
php artisan rajaongkir:fetch
```

**Output:**
```
🚀 Fetching provinces...
✅ Found 35 provinces
  [0/35] Fetching cities for NUSA TENGGARA BARAT (NTB)...
  [1/35] Fetching cities for NUSA TENGGARA BARAT...
  ...
✅ Found 534 cities and 7224 districts
💾 Saving to database...
✅ Done! Data fetched successfully
   Provinces: 35
   Cities: 534
   Districts: 7224
```

### Save to Both Database & JSON File
```bash
php artisan rajaongkir:fetch --save-json
```

**Additional Output:**
```
📝 Saving to JSON file...
✅ JSON saved to: /path/to/storage/rajaongkir/locations.json
```

## Requirements

1. **API Key:** Must have `RAJAONGKIR_API_KEY` in `.env`
   ```env
   RAJAONGKIR_API_KEY=your_api_key_here
   ```

2. **Storage Permissions:** Write access to `storage/rajaongkir/` directory

3. **Network:** Active internet connection to RajaOngkir API

## Options

| Option | Description | Effect |
|--------|-------------|--------|
| `--save-json` | Save to JSON file | Also creates `storage/rajaongkir/locations.json` |

## Behavior

1. **Clears existing data** - Truncates all tables (provinces, cities, districts)
2. **Fetches from API** - Calls RajaOngkir API sequentially for all levels
3. **Saves to database** - Inserts all records with proper relationships
4. **Optionally saves JSON** - Creates backup JSON file

## Timing

Approximate timing for full fetch:
- **Small (Demo data):** 5-10 seconds
- **Full (35 prov + 534 cities + 7224 dist):** 2-3 minutes

*Depends on network speed and API response times.*

## Error Handling

### Missing API Key
```
❌ RAJAONGKIR_API_KEY not found in .env
```

**Solution:** Add to `.env`
```env
RAJAONGKIR_API_KEY=your_api_key_here
```

### Network/API Error
```
❌ Failed to fetch provinces: 401
```

**Solution:** Check API key validity and network connection

### Partial Fetch Failure
```
⚠️  Failed to fetch cities for {province_id}
```

**Solution:** Rerun command (will retry from beginning)

## Output Files

### Database Tables Created/Updated
```
provinces    - 35 records
cities       - 500+ records  
districts    - 7000+ records
```

### JSON File (Optional)
```
storage/rajaongkir/locations.json
```

**File Structure:**
```json
{
  "provinces": [...],
  "cities": [...],
  "districts": [...],
  "fetched_at": "2026-01-04T12:00:00+07:00"
}
```

**File Size:** ~1-2 MB

## How It's Used After Fetch

### Automatic Detection
LocationService automatically:
1. Checks if JSON file exists
2. If yes → uses JSON (faster)
3. If no → uses database

### In Your Application
```php
$service = new LocationService();

// Get provinces (from DB or JSON)
$provinces = $service->getProvinces();

// Autocomplete provinces
$results = $service->searchProvinces('jakarta', 6);
// Returns max 6 matching provinces
```

## Performance Impact

### Before Fetch
- Database: Empty location tables
- API: All requests go through LocationService to database

### After Fetch (DB Mode)
- Database: Populated with full data
- Queries: ~2-10ms per location lookup
- Autocomplete: ~1-3ms for search filtering

### After Fetch (JSON Mode - with --save-json)
- Database: Populated with full data
- JSON File: Backup in storage
- Performance: <1ms (in-memory, no DB queries)
- API: No longer needed for location data!

## Backup & Restore

### Backup (Create JSON)
```bash
php artisan rajaongkir:fetch --save-json
# Creates storage/rajaongkir/locations.json
```

### Manual JSON Backup
```bash
cp storage/rajaongkir/locations.json storage/rajaongkir/locations.backup.json
```

### Restore from JSON
Just run command again - it will reload from JSON if it exists:
```bash
php artisan rajaongkir:fetch --save-json
```

## Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| Timeout error | Large dataset, slow network | Increase PHP timeout, retry |
| Duplicate key error | Running twice simultaneously | Wait for first to complete |
| Permission denied | No write access to storage | Check folder permissions |
| API rate limit | Too many requests | Wait and retry (limit ~1000/hour) |

## Testing the Data

### Check if data loaded
```bash
php artisan tinker
>>> App\Models\Province::count()
35
>>> App\Models\City::count()
534
>>> App\Models\District::count()
7224
```

### Test LocationService
```bash
php artisan tinker
>>> $service = new \App\Services\LocationService()
>>> $service->searchProvinces('jakarta', 3)
=> array
```

### Test API Endpoint
```bash
curl http://localhost:8000/api/provinces/search?q=jakarta
```

## Automation (Optional)

Schedule command to run monthly:
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('rajaongkir:fetch --save-json')
        ->monthlyOn(1, '02:00'); // First of month at 2 AM
}
```

## FAQ

**Q: Do I need to run this?**
A: Yes, once to populate the database. After that, data stays unless you delete it.

**Q: Can I run it multiple times?**
A: Yes, it clears and repopulates. Safe to run repeatedly.

**Q: Is JSON file required?**
A: No, it's optional. Database is enough. JSON is just a backup/performance boost.

**Q: What if API key expires?**
A: Command will fail with API error. Update `.env` and rerun.

**Q: Can I use dummy data for testing?**
A: Yes! Use the seeded demo data. Only run fetch when you have real API key.

**Q: How often should I update?**
A: RajaOngkir data rarely changes. Once a month is fine.

---

**Last Updated:** 2026-01-04
**Version:** 1.0
