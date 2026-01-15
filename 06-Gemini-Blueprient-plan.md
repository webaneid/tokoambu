# Gemini Enhancement Plan for /media

## 1. Scope & Objectives
- Integrate Google Gemini-based image enhancement into the existing `/media` workflow without disrupting manual upload/management.
- Maintain modularity so future AI styles/effects can be added via registry-driven classes per the blueprint.
- Centralize Gemini credentials and feature toggles inside the existing settings surface.

## 2. Baseline References
- Blueprint: 06-Gemini-Blueprint.md (registry pattern, feature contract, prompt builder).
- Existing media stack: MediaController, media.index Blade view, `/settings` page for configuration storage, `Media` model for assets.

## 3. Data & Settings Strategy
- **Table**: `ai_integrations`
  - Columns: id, provider (enum: gemini), api_key (encrypted), model, is_enabled (bool), default_bg_color, use_solid_background (bool), metadata (json), timestamps.
  - Seed with one row for Gemini; expose CRUD via `/settings` UI to reuse established settings workflow.
- **Logs**: `ai_logs`
  - Columns: id, media_id (nullable), source_media_id, provider, prompt, response_meta (json), status (queued|processing|done|failed), error_message, created_at.
  - Enables auditing per blueprint section 6.

## 4. Backend Architecture
1. **Contracts & Registry**
   - `app/Contracts/AiFeatureContract.php` (per blueprint) with `getKey`, `getPrompt`, `getCategory`.
   - Feature classes under `app/AiFeatures/` (e.g., `UprightStanding`, `GlassReflection`).
   - `config/aistudio.php` listing feature classes; allows toggling via config instead of hardcoding in constructors.
2. **Services**
   - `App\Services\AiStudioService`
     - Registers features from config, builds final prompt (`buildFinalPrompt($features, $bgColor, $useSolid)`).
     - `getAvailableFeatures()` returns grouped metadata (`style`, `fx`) for frontend.
   - `App\Services\GeminiClient`
     - Wraps HTTP calls, handles auth using `ai_integrations` row, exposes `enhanceImage(Media $media, array $params)`.
     - Abstracted so future providers can reuse the same contract.
3. **Controller Layer**
   - `App\Http\Controllers\AiGatewayController`
     - `GET /api/ai/features` → returns `AiStudioService::getAvailableFeatures()` + defaults from settings.
     - `POST /api/ai/enhance` → validates payload, dispatches `ProcessAiEnhancement` job, returns job tracking id.
     - `GET /api/ai/jobs/{id}` → returns status info from `ai_logs`.
   - Controller guarded by same auth middleware as `/media`; checks `ai_integrations.is_enabled` before proceeding.
4. **Jobs & Events**
   - `ProcessAiEnhancement` job:
     - Loads source media file, composes prompt via `AiStudioService`, calls `GeminiClient`, stores result as new `Media` (type `product_photo` with metadata `source: ai`).
     - Updates `ai_logs` status + attaches new media id.
     - Broadcasting event (optional) for real-time UI updates via Echo/Pusher if needed.

## 5. Frontend / UI Plan (media.index)
- Add "AI Product Studio" panel/tab above the existing media grid, visible only if Gemini enabled.
- Components:
  1. **Source Selector**: reuse existing grid (select media) or file-upload field to upload and immediately send to AI.
  2. **Feature Checklist**: fetch `/api/ai/features` on load; auto-generate grouped toggles (Style vs FX) so new feature classes appear without Blade edits.
  3. **Background Controls**: color picker (HEX) + toggle for solid background (default from settings).
  4. **Action Buttons**: "Enhance with Gemini", showing spinner + queued status.
  5. **Result Preview**: modal similar to current detail modal, tagging outputs with badge "AI Enhanced" and linking to log entry.
- Use existing alert/toast components for success/failure messages.

## 6. API Flow
1. User submits AI form → `POST /api/ai/enhance` with payload:
   ```json
   {
     "media_id": 123,
     "features": {"standing": true, "glass_reflection": true},
     "background_color": "#FFFFFF",
     "use_solid": true
   }
   ```
2. Controller validates roles and feature keys, records new `ai_logs` row (status `queued`), dispatches job, returns `{ job_id }`.
3. Frontend polls `GET /api/ai/jobs/{job_id}` or subscribes to events for completion.
4. Job completion creates new media row, updates log status `done`, attaches result metadata.
5. UI refreshes media grid (existing pagination) so new AI item appears automatically.

## 7. Implementation Checklist
1. **Migrations**: `create_ai_integrations_table`, `create_ai_logs_table` (plus optional seeder for Gemini row).
2. **Config & Contracts**: add `config/aistudio.php`, contract interface, initial feature classes.
3. **Services**: implement `AiStudioService`, `GeminiClient`.
4. **Jobs & Controllers**: create job + controller routes (likely under `routes/web.php` for authenticated panel + `routes/api.php` variant if separation needed).
5. **Settings UI**: extend `/settings` Blade view to manage Gemini keys, default background, enable toggle. Reuse existing validation/messages.
6. **Media View Update**: add AI panel, fetch features via Axios, integrate with existing modal and grid.
7. **Logging & Monitoring**: ensure `ai_logs` surfaces in admin (simple table or export), add error handling & user messaging.
8. **Testing**: unit tests for prompt builder + feature registry, feature tests for controller endpoints, job test verifying media creation/log updates.

## 8. Risks & Mitigations
- **API failure**: handle timeouts/retries in `GeminiClient`; expose friendly error + leave log status `failed` with reason.
- **Credential management**: ensure keys encrypted at rest and masked in UI (display partial only).
- **Performance**: keep AI processing off main request via jobs; limit concurrent submissions or add rate limiting.
- **Access control**: mirror existing media permissions to ensure only authorized roles see or trigger AI enhancements.

## 9. Next Steps
1. Implement migrations + setting form (foundation).
2. Wire services/controller/job scaffolding.
3. Update `/media` UI to consume new API.
4. QA end-to-end flow (upload → AI → new media entry) before rollout.
