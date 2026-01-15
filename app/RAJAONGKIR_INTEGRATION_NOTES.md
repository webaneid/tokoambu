# RajaOngkir API V2 Integration Notes

## Overview
RajaOngkir provides hierarchical location data (Provinsi → Kota → Kecamatan) for Indonesia shipping calculations. This document outlines how to integrate their API into TokoAmbu.

---

## API Structure

### Base URL
```
https://rajaongkir.komerce.id/api/v1/
```

### Authentication
All requests require API Key header:
```
Key: YOUR_API_KEY
```

### Integration Methods
RajaOngkir provides 2 approaches:

#### 1. **Step-by-Step Method (Hierarchical)** ✅ RECOMMENDED
- Best for: Dropdown forms, traditional address selection
- Uses: Province → City → District flow
- More API calls but clearer UX

#### 2. Direct Search Method
- Best for: Modern search interfaces, autocomplete
- Direct search by city name
- Fewer API calls

---

## Step-by-Step API Endpoints

### 1. Search Province (Get All Provinces)

**Endpoint:** `GET /destination/province`

**Full URL:**
```
https://rajaongkir.komerce.id/api/v1/destination/province
```

**Request:**
```bash
curl --location 'https://rajaongkir.komerce.id/api/v1/destination/province' \
--header 'Key: YOUR_API_KEY'
```

**Response:**
```json
{
  "meta": {
    "message": "Success Get Province",
    "code": 200,
    "status": "success"
  },
  "data": [
    {
      "id": 1,
      "name": "NUSA TENGGARA BARAT (NTB)"
    },
    {
      "id": 6,
      "name": "JAWA BARAT"
    },
    {
      "id": 11,
      "name": "DKI JAKARTA"
    },
    {
      "id": 12,
      "name": "BANTEN"
    },
    // ... more provinces (35 total)
  ]
}
```

**Data Structure:**
- `id`: Province ID (numeric)
- `name`: Province name (string, uppercase)

---

### 2. Search City (Get Cities by Province)

**Endpoint:** `GET /destination/city/{province_id}`

**Full URL:**
```
https://rajaongkir.komerce.id/api/v1/destination/city/{province_id}
```

**Example:** Get cities in Banten (province_id=12)
```bash
curl --location 'https://rajaongkir.komerce.id/api/v1/destination/city/12' \
--header 'Key: YOUR_API_KEY'
```

**Response:**
```json
{
  "meta": {
    "message": "Success Get District By City ID",
    "code": 200,
    "status": "success"
  },
  "data": [
    {
      "id": 1360,
      "name": "JAKARTA SELATAN",
      "zip_code": "0"
    },
    {
      "id": 1361,
      "name": "JAGAKARSA",
      "zip_code": "12630"
    },
    {
      "id": 1362,
      "name": "KEBAYORAN BARU",
      "zip_code": "12150"
    },
    // ... more cities
  ]
}
```

**Data Structure:**
- `id`: City ID (numeric)
- `name`: City name (string, uppercase)
- `zip_code`: Postal code prefix (string, may be "0" for unknown)

**⚠️ Important:** Response shows `zip_code` which is partial postal code

---

### 3. Search District (Get Districts by City)

**Endpoint:** `GET /destination/district/{city_id}`

**Full URL:**
```
https://rajaongkir.komerce.id/api/v1/destination/district/{city_id}
```

**Example:** Get districts in Jakarta Selatan (city_id=1360)
```bash
curl --location 'https://rajaongkir.komerce.id/api/v1/destination/district/575' \
--header 'Key: YOUR_API_KEY'
```

**Response:**
```json
{
  "meta": {
    "message": "Success Get District By City ID",
    "code": 200,
    "status": "success"
  },
  "data": [
    {
      "id": 1360,
      "name": "JAKARTA SELATAN",
      "zip_code": "0"
    },
    {
      "id": 1361,
      "name": "JAGAKARSA",
      "zip_code": "12630"
    },
    {
      "id": 1362,
      "name": "KEBAYORAN BARU",
      "zip_code": "12150"
    },
    // ... more districts
  ]
}
```

**Data Structure:**
- `id`: District ID (numeric)
- `name`: District name (string, uppercase)
- `zip_code`: Postal code (string, some have valid codes like "12630", others "0")

---

## Integration Pattern (What They Do)

```
User Flow:
┌─────────────────────────────────────────────┐
│ 1. Select Province (Dropdown)               │
│    ↓ Call: GET /destination/province        │
│    ↑ Returns: List of 35 provinces          │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│ 2. Select City (Dropdown)                   │
│    ↓ Call: GET /destination/city/{prov_id}  │
│    ↑ Returns: List of cities in province    │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│ 3. Select District (Dropdown)               │
│    ↓ Call: GET /destination/district/{city} │
│    ↑ Returns: List of districts in city     │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│ 4. Use District ID for shipping cost calc   │
│    (District ID is final location ID)       │
└─────────────────────────────────────────────┘
```

---

## Best Practices (Per RajaOngkir Docs)

1. **Cache Static Data** ⭐
   - Province list rarely changes → cache locally
   - City/District data can be cached with TTL
   - Reduces API calls significantly

2. **Use API Key Securely**
   - Store in `.env` file
   - Never expose in client-side code
   - Use backend proxy for requests

3. **Rate Limiting**
   - Depends on subscription tier
   - Consider caching to avoid excessive calls
   - Use debouncing for autocomplete

4. **Error Handling**
   - Check HTTP status codes
   - Provide fallback messages
   - Log API errors for debugging

5. **Debounce for Search**
   - If implementing autocomplete search
   - Prevents flooding API with requests
   - Improves user experience

---

## What We'll Implement in TokoAmbu

### Database Tables (To Cache RajaOngkir Data)

```sql
-- Store provinces
CREATE TABLE provinces (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Store cities
CREATE TABLE cities (
    id INT PRIMARY KEY,
    province_id INT,
    name VARCHAR(255),
    zip_code VARCHAR(10),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (province_id) REFERENCES provinces(id)
);

-- Store districts
CREATE TABLE districts (
    id INT PRIMARY KEY,
    city_id INT,
    name VARCHAR(255),
    zip_code VARCHAR(10),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id)
);
```

### API Endpoints to Create (Backend Proxy)

```
GET /api/provinces
  → Returns cached provinces from DB
  → If empty, fetch from RajaOngkir, cache, return

GET /api/cities/{province_id}
  → Returns cached cities from DB
  → If empty, fetch from RajaOngkir, cache, return

GET /api/districts/{city_id}
  → Returns cached districts from DB
  → If empty, fetch from RajaOngkir, cache, return
```

### Frontend Integration Points

1. **Address Fields (Orders, Customers)**
   - Add: Province, City, District, Postal Code dropdowns
   - Use cascading selection pattern
   - Call our API endpoints (not RajaOngkir directly)

2. **Shipping Calculation**
   - Use district_id from selected district
   - Pass to RajaOngkir cost calculation endpoint (later)

3. **Address Display**
   - Show formatted: "District, City, Province, PostalCode"

---

## Response Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request |
| 401 | Unauthorized (invalid API key) |
| 429 | Rate limit exceeded |
| 500 | Server error |

---

## Implementation Roadmap

1. ✅ Understand API structure (current)
2. Create migrations for provinces, cities, districts
3. Create models: Province, City, District
4. Create API endpoints for cached data
5. Create seeder to populate initial data
6. Create frontend components (cascading dropdowns)
7. Integrate into customer/order forms
8. Integrate with shipping cost calculation (later)

---

## Key Takeaway

RajaOngkir provides:
- **Provinces:** 35 total (static)
- **Cities:** ~500+ total
- **Districts:** ~7,000+ total
- **Postal Codes:** Included with districts

We should cache all this data locally to reduce API calls and improve performance. They recommend this explicitly!
