/**
 * AI Studio Manager
 * Handles AI image enhancement functionality in Media Gallery
 */

export class AiStudioManager {
    constructor(config) {
        this.config = {
            routes: config.routes || {},
            csrfToken: config.csrfToken || '',
            maxPollAttempts: config.maxPollAttempts || 40,
            pollIntervalMs: config.pollIntervalMs || 2000,
            elementIds: config.elementIds || {}, // Custom element IDs
            onEnhanceComplete: config.onEnhanceComplete || null, // Callback when enhancement is done
        };

        this.state = {
            features: {},
            selectedMediaId: null,
            selectedMediaName: null,
            isProcessing: false,
            pollTimeout: null,
            currentJobId: null,
            progressSteps: [],
            currentStep: 0,
            percentage: 0,
        };

        this.elements = this.initElements();
        this.createProgressModal();
        this.bindEvents();
        this.loadFeatures();
    }

    initElements() {
        const ids = this.config.elementIds;
        return {
            mediaIdInput: document.getElementById(ids.mediaIdInput || 'aiMediaId'),
            selectedMediaName: document.getElementById(ids.selectedMediaName || 'aiSelectedMediaName'),
            clearSelection: document.getElementById(ids.clearSelection || 'aiClearSelection'),
            backgroundColor: document.getElementById(ids.backgroundColor || 'aiBackgroundColor'),
            useSolid: document.getElementById(ids.useSolid || 'aiUseSolid'),
            featuresContainer: document.getElementById(ids.featuresContainer || 'aiFeaturesContainer'),
            quickPresetsContainer: document.getElementById(ids.quickPresetsContainer || 'aiQuickPresets'),
            enhanceTrigger: document.getElementById(ids.enhanceTrigger || 'aiEnhanceTrigger'),
            status: document.getElementById(ids.status || 'aiStatus'),
        };
    }

    createProgressModal() {
        // Create draggable progress modal
        const modal = document.createElement('div');
        modal.id = 'aiProgressModal';
        modal.className = 'ai-progress-modal hidden';
        modal.innerHTML = `
            <div class="ai-progress-header">
                <span class="ai-progress-title">⏳ AI Enhancement</span>
                <button class="ai-progress-close" type="button">✕</button>
            </div>
            <div class="ai-progress-body">
                <div class="ai-progress-steps" id="aiProgressSteps"></div>
                <div class="ai-progress-bar-container">
                    <div class="ai-progress-bar" id="aiProgressBar">
                        <div class="ai-progress-bar-fill" id="aiProgressBarFill" style="width: 0%"></div>
                    </div>
                    <div class="ai-progress-percentage" id="aiProgressPercentage">0%</div>
                </div>
                <div class="ai-progress-message" id="aiProgressMessage"></div>
            </div>
            <div class="ai-progress-preview hidden" id="aiProgressPreview">
                <div class="ai-preview-container">
                    <div class="ai-preview-item">
                        <span class="ai-preview-label">Original</span>
                        <img id="aiPreviewOriginal" alt="Original" />
                    </div>
                    <div class="ai-preview-arrow">→</div>
                    <div class="ai-preview-item">
                        <span class="ai-preview-label">AI Enhanced</span>
                        <img id="aiPreviewEnhanced" alt="Enhanced" />
                    </div>
                </div>
                <div class="ai-preview-actions">
                    <button class="ai-btn ai-btn-primary" id="aiPreviewSave">💾 Simpan</button>
                    <button class="ai-btn ai-btn-secondary" id="aiPreviewRetry">🔄 Ulangi</button>
                    <button class="ai-btn ai-btn-ghost" id="aiPreviewCancel">✖ Batal</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Make it draggable
        this.makeDraggable(modal);

        // Store references
        this.progressModal = {
            container: modal,
            steps: modal.querySelector('#aiProgressSteps'),
            bar: modal.querySelector('#aiProgressBarFill'),
            percentage: modal.querySelector('#aiProgressPercentage'),
            message: modal.querySelector('#aiProgressMessage'),
            preview: modal.querySelector('#aiProgressPreview'),
            previewOriginal: modal.querySelector('#aiPreviewOriginal'),
            previewEnhanced: modal.querySelector('#aiPreviewEnhanced'),
            closeBtn: modal.querySelector('.ai-progress-close'),
            saveBtn: modal.querySelector('#aiPreviewSave'),
            retryBtn: modal.querySelector('#aiPreviewRetry'),
            cancelBtn: modal.querySelector('#aiPreviewCancel'),
        };

        // Bind close events
        this.progressModal.closeBtn.addEventListener('click', () => this.hideProgressModal());
        this.progressModal.cancelBtn.addEventListener('click', () => this.hideProgressModal());
    }

    makeDraggable(element) {
        const header = element.querySelector('.ai-progress-header');
        let isDragging = false;
        let currentX;
        let currentY;
        let initialX;
        let initialY;

        header.style.cursor = 'move';

        header.addEventListener('mousedown', (e) => {
            isDragging = true;
            initialX = e.clientX - element.offsetLeft;
            initialY = e.clientY - element.offsetTop;
        });

        document.addEventListener('mousemove', (e) => {
            if (isDragging) {
                e.preventDefault();
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;

                element.style.left = currentX + 'px';
                element.style.top = currentY + 'px';
                element.style.transform = 'none'; // Remove centering transform
            }
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
        });
    }

    showProgressModal() {
        this.progressModal.container.classList.remove('hidden');
        // Center on first show
        if (!this.progressModal.container.style.left) {
            this.progressModal.container.style.left = '50%';
            this.progressModal.container.style.top = '50%';
            this.progressModal.container.style.transform = 'translate(-50%, -50%)';
        }
    }

    hideProgressModal() {
        this.progressModal.container.classList.add('hidden');
        this.progressModal.preview.classList.add('hidden');
        this.resetProgress();
    }

    updateProgress(step, percentage, message) {
        this.state.currentStep = step;
        this.state.percentage = percentage;

        // Update progress bar
        this.progressModal.bar.style.width = percentage + '%';
        this.progressModal.percentage.textContent = percentage + '%';

        // Update message
        if (message) {
            this.progressModal.message.textContent = message;
        }

        // Update steps
        this.renderProgressSteps();
    }

    renderProgressSteps() {
        const steps = this.state.progressSteps;
        const currentStep = this.state.currentStep;

        this.progressModal.steps.innerHTML = steps.map((step, index) => {
            let icon = '⬜';
            let className = 'ai-step-pending';

            if (index < currentStep) {
                icon = '✅';
                className = 'ai-step-completed';
            } else if (index === currentStep) {
                icon = '⏳';
                className = 'ai-step-active';
            }

            return `<div class="ai-progress-step ${className}">${icon} ${step}</div>`;
        }).join('');
    }

    resetProgress() {
        this.state.currentStep = 0;
        this.state.percentage = 0;
        this.state.progressSteps = [];
        this.progressModal.bar.style.width = '0%';
        this.progressModal.percentage.textContent = '0%';
        this.progressModal.message.textContent = '';
        this.progressModal.steps.innerHTML = '';
    }

    showPreview(originalUrl, enhancedUrl, resultMediaId = null) {
        this.progressModal.previewOriginal.src = originalUrl;
        this.progressModal.previewEnhanced.src = enhancedUrl;
        this.progressModal.preview.classList.remove('hidden');

        // Bind save action
        this.progressModal.saveBtn.onclick = () => {
            this.hideProgressModal();

            // Call enhance complete callback if provided
            if (this.config.onEnhanceComplete && resultMediaId) {
                this.config.onEnhanceComplete(resultMediaId);
            } else {
                window.location.reload(); // Reload to show new media
            }
        };

        // Bind retry action
        this.progressModal.retryBtn.onclick = () => {
            this.hideProgressModal();
            // Could re-run enhance() here with same settings
        };
    }

    bindEvents() {
        // Clear selection button
        this.elements.clearSelection?.addEventListener('click', () => this.resetSelection());

        // Enhance button
        this.elements.enhanceTrigger?.addEventListener('click', () => this.enhance());

        // AI select buttons on media cards
        document.querySelectorAll('.ai-select-btn').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                const mediaId = button.dataset.mediaId;
                const mediaFilename = button.dataset.mediaFilename;
                this.selectMedia(mediaId, mediaFilename);
            });
        });
    }

    async loadFeatures() {
        try {
            const response = await fetch(this.config.routes.features, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (!data.enabled) {
                this.showStatus('AI belum aktif. Periksa pengaturan Ambu Magic.', 'error');
                if (this.elements.enhanceTrigger) {
                    this.elements.enhanceTrigger.disabled = true;
                }
                return;
            }

            this.renderFeatures(data.features || {});
            this.renderQuickPresets();

            if (data.defaults) {
                if (this.elements.backgroundColor) {
                    this.elements.backgroundColor.value = data.defaults.background_color || this.elements.backgroundColor.value;
                }
                if (this.elements.useSolid) {
                    this.elements.useSolid.checked = !!data.defaults.use_solid_background;
                }
            }
        } catch (error) {
            if (this.elements.featuresContainer) {
                this.elements.featuresContainer.innerHTML = '<p class="text-xs text-red-500">Gagal memuat fitur AI. Coba refresh halaman.</p>';
            }
        }
    }

    renderFeatures(grouped) {
        if (!this.elements.featuresContainer) return;

        this.elements.featuresContainer.innerHTML = '';

        Object.entries(grouped).forEach(([category, items]) => {
            const section = document.createElement('div');
            section.className = 'space-y-2';

            const heading = document.createElement('p');
            heading.className = 'text-xs font-semibold uppercase tracking-wide text-gray-500';
            heading.textContent = category === 'style' ? 'Base Styles' : 'Effects';
            section.appendChild(heading);

            const grid = document.createElement('div');
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 gap-2';

            items.forEach((feature) => {
                if (this.state.features[feature.key] === undefined) {
                    this.state.features[feature.key] = false;
                }

                const label = document.createElement('label');
                label.className = 'flex items-center gap-2 text-sm text-gray-700 border border-gray-200 rounded-lg px-3 py-2 hover:border-primary transition cursor-pointer';

                const input = document.createElement('input');
                input.type = 'checkbox';
                input.className = 'rounded ai-feature-checkbox';
                input.dataset.featureKey = feature.key;
                input.addEventListener('change', (event) => {
                    this.state.features[feature.key] = event.target.checked;
                });

                const span = document.createElement('span');
                span.textContent = feature.key.replace(/_/g, ' ');

                label.appendChild(input);
                label.appendChild(span);
                grid.appendChild(label);
            });

            section.appendChild(grid);
            this.elements.featuresContainer.appendChild(section);
        });
    }

    renderQuickPresets() {
        if (!this.elements.quickPresetsContainer) return;

        const presets = [
            {
                icon: '📚',
                label: 'Buku',
                description: 'Book product shot',
                features: { standing: true, thickness: true },
                bgColor: '#FFFFFF',
                useSolid: true
            },
            {
                icon: '🍔',
                label: 'Makanan',
                description: 'Food photography',
                features: { macro: true },
                bgColor: '#FFFFFF',
                useSolid: false
            },
            {
                icon: '👕',
                label: 'Fashion',
                description: 'Clothing & accessories',
                features: { standing: true },
                bgColor: '#F5F5F5',
                useSolid: true
            },
            {
                icon: '📱',
                label: 'Gadget',
                description: 'Electronics & tech',
                features: { standing: true, glass: true },
                bgColor: '#FFFFFF',
                useSolid: true
            }
        ];

        this.elements.quickPresetsContainer.innerHTML = '';

        presets.forEach((preset) => {
            const card = document.createElement('button');
            card.type = 'button';
            card.className = 'ai-preset-card';
            card.innerHTML = `
                <div class="ai-preset-icon">${preset.icon}</div>
                <div class="ai-preset-content">
                    <div class="ai-preset-label">${preset.label}</div>
                    <div class="ai-preset-desc">${preset.description}</div>
                </div>
            `;

            card.addEventListener('click', (e) => {
                this.applyPreset(preset, e.currentTarget);
            });

            this.elements.quickPresetsContainer.appendChild(card);
        });
    }

    applyPreset(preset, clickedCard) {
        // Apply features
        Object.keys(this.state.features).forEach((key) => {
            this.state.features[key] = false;
        });

        Object.keys(preset.features).forEach((key) => {
            if (this.state.features.hasOwnProperty(key)) {
                this.state.features[key] = preset.features[key];
            }
        });

        // Update checkboxes
        document.querySelectorAll('.ai-feature-checkbox').forEach((checkbox) => {
            const featureKey = checkbox.dataset.featureKey;
            checkbox.checked = preset.features[featureKey] || false;
        });

        // Apply background settings
        if (this.elements.backgroundColor) {
            this.elements.backgroundColor.value = preset.bgColor;
        }
        if (this.elements.useSolid) {
            this.elements.useSolid.checked = preset.useSolid;
        }

        this.showStatus(`✓ Preset "${preset.label}" diterapkan!`, 'success');

        // Visual feedback
        document.querySelectorAll('.ai-preset-card').forEach(card => card.classList.remove('active'));
        if (clickedCard) {
            clickedCard.classList.add('active');
        }
    }

    selectMedia(id, filename) {
        this.state.selectedMediaId = id;
        this.state.selectedMediaName = filename;

        if (this.elements.mediaIdInput) {
            this.elements.mediaIdInput.value = id;
        }
        if (this.elements.selectedMediaName) {
            this.elements.selectedMediaName.textContent = `${filename} (ID: ${id})`;
        }
        if (this.elements.clearSelection) {
            this.elements.clearSelection.classList.remove('hidden');
        }

        // Update external hint if function exists
        if (typeof window.updateMediaSelectorHint === 'function') {
            window.updateMediaSelectorHint(filename);
        }

        this.showStatus('Media siap diproses. Pilih preset lalu jalankan Ambu Magic.');
    }

    resetSelection() {
        this.state.selectedMediaId = null;
        this.state.selectedMediaName = null;

        if (this.elements.mediaIdInput) {
            this.elements.mediaIdInput.value = '';
        }
        if (this.elements.selectedMediaName) {
            this.elements.selectedMediaName.textContent = 'Belum ada media dipilih.';
        }
        if (this.elements.clearSelection) {
            this.elements.clearSelection.classList.add('hidden');
        }

        // Update external hint if function exists
        if (typeof window.updateMediaSelectorHint === 'function') {
            window.updateMediaSelectorHint(null);
        }

        this.showStatus('');
    }

    async enhance() {
        // Update state from input if needed
        if (!this.state.selectedMediaId && this.elements.mediaIdInput?.value) {
            this.state.selectedMediaId = this.elements.mediaIdInput.value;
        }

        if (!this.state.selectedMediaId) {
            this.showStatus('Pilih media yang ingin ditingkatkan terlebih dahulu.', 'error');
            return;
        }

        if (this.state.isProcessing) {
            return;
        }

        const payload = {
            media_id: this.state.selectedMediaId,
            features: this.state.features,
            background_color: this.elements.backgroundColor?.value || '#FFFFFF',
            use_solid: this.elements.useSolid?.checked ? 1 : 0,
        };

        this.state.isProcessing = true;
        if (this.elements.enhanceTrigger) {
            this.elements.enhanceTrigger.disabled = true;
        }

        // Initialize progress steps
        this.state.progressSteps = [
            'Mengirim ke Ambu Magic',
            'Memproses dengan AI',
            'Mengunduh hasil',
            'Menyimpan gambar'
        ];

        // Show progress modal
        this.showProgressModal();
        this.updateProgress(0, 10, 'Mengirim permintaan ke Ambu Magic...');
        this.showStatus('Mengirim permintaan ke Ambu Magic...');

        try {
            const response = await fetch(this.config.routes.enhance, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok || !data.job_id) {
                throw new Error(data.message || 'Gagal memulai proses AI');
            }

            // Step 1 complete
            this.updateProgress(1, 30, 'Permintaan berhasil dikirim. Menunggu AI memproses...');
            this.showStatus('Job AI dibuat. Menunggu hasil...');
            this.state.currentJobId = data.job_id;
            this.pollJob(data.job_id);
        } catch (error) {
            this.updateProgress(0, 0, '❌ ' + error.message);
            this.showStatus(error.message || 'Gagal menghubungi Ambu Magic.', 'error');
            this.state.isProcessing = false;
            if (this.elements.enhanceTrigger) {
                this.elements.enhanceTrigger.disabled = false;
            }
            setTimeout(() => this.hideProgressModal(), 3000);
        }
    }

    async pollJob(jobId, attempt = 0) {
        if (attempt >= this.config.maxPollAttempts) {
            this.updateProgress(1, 30, '⚠️ Job terlalu lama. Queue worker mungkin tidak running.');
            this.showStatus(
                '⚠️ Job terlalu lama. Queue worker mungkin tidak running. Jalankan: php artisan queue:work',
                'error'
            );
            this.state.isProcessing = false;
            if (this.elements.enhanceTrigger) {
                this.elements.enhanceTrigger.disabled = false;
            }
            setTimeout(() => this.hideProgressModal(), 5000);
            return;
        }

        try {
            const response = await fetch(`${this.config.routes.job}/${jobId}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.status === 'done') {
                // Step 2 & 3 complete
                this.updateProgress(3, 90, '✓ Ambu Magic selesai! Menyimpan hasil...');
                this.showStatus('✓ Ambu Magic selesai! Menyimpan hasil...', 'success');

                // Final step
                setTimeout(() => {
                    this.updateProgress(4, 100, '✅ Selesai!');

                    // Show preview if we have URLs
                    if (data.result_url && data.original_url) {
                        this.showPreview(data.original_url, data.result_url, data.result_media_id);
                    } else {
                        // No preview, just reload or call callback
                        if (this.config.onEnhanceComplete && data.result_media_id) {
                            setTimeout(() => {
                                this.hideProgressModal();
                                this.config.onEnhanceComplete(data.result_media_id);
                            }, 1000);
                        } else {
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    }
                }, 500);

                this.state.isProcessing = false;
                if (this.elements.enhanceTrigger) {
                    this.elements.enhanceTrigger.disabled = false;
                }
            } else if (data.status === 'failed') {
                this.updateProgress(1, 30, `✗ ${data.error || 'AI gagal memproses gambar.'}`);
                this.showStatus(`✗ ${data.error || 'AI gagal memproses gambar.'}`, 'error');
                this.state.isProcessing = false;
                if (this.elements.enhanceTrigger) {
                    this.elements.enhanceTrigger.disabled = false;
                }
                setTimeout(() => this.hideProgressModal(), 5000);
            } else if (data.status === 'queued' && attempt > 5) {
                // Job stuck in queue after 5 attempts
                const percentage = 30 + (attempt * 2); // Slowly increment
                this.updateProgress(1, Math.min(percentage, 50),
                    `⚠️ Menunggu queue worker... (${attempt}/${this.config.maxPollAttempts})`
                );
                this.showStatus(
                    `⚠️ Job stuck di queue (${attempt}/${this.config.maxPollAttempts}). Pastikan queue worker running.`,
                    'error'
                );

                const delay = Math.min(this.config.pollIntervalMs * Math.pow(1.5, attempt), 8000);
                this.state.pollTimeout = setTimeout(() => {
                    this.pollJob(jobId, attempt + 1);
                }, delay);
            } else {
                // Still processing - use exponential backoff
                const delay = Math.min(this.config.pollIntervalMs * Math.pow(1.2, attempt), 5000);
                const statusText = data.status === 'queued' ? 'di queue' : 'sedang diproses';

                // Progress from 30% to 80% during processing
                const processingProgress = 30 + Math.min((attempt * 3), 50);
                const currentStep = data.status === 'queued' ? 1 : 2;

                this.updateProgress(currentStep, processingProgress,
                    `⏳ Ambu Magic ${statusText}... (${attempt + 1}/${this.config.maxPollAttempts})`
                );
                this.showStatus(`⏳ Ambu Magic ${statusText}... (${attempt + 1}/${this.config.maxPollAttempts})`);

                this.state.pollTimeout = setTimeout(() => {
                    this.pollJob(jobId, attempt + 1);
                }, delay);
            }
        } catch (error) {
            this.updateProgress(1, 30, `✗ Network error: ${error.message}`);
            this.showStatus(`✗ Network error: ${error.message}. Coba lagi.`, 'error');
            this.state.isProcessing = false;
            if (this.elements.enhanceTrigger) {
                this.elements.enhanceTrigger.disabled = false;
            }
            setTimeout(() => this.hideProgressModal(), 5000);
        }
    }

    showStatus(message, tone = 'info') {
        if (!this.elements.status) return;

        this.elements.status.textContent = message;
        this.elements.status.classList.remove('text-gray-600', 'text-red-600', 'text-green-600');

        if (tone === 'error') {
            this.elements.status.classList.add('text-red-600');
        } else if (tone === 'success') {
            this.elements.status.classList.add('text-green-600');
        } else {
            this.elements.status.classList.add('text-gray-600');
        }
    }

    destroy() {
        if (this.state.pollTimeout) {
            clearTimeout(this.state.pollTimeout);
        }
    }
}

// Initialize when DOM is ready
export function initAiStudio(config) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new AiStudioManager(config);
        });
    } else {
        new AiStudioManager(config);
    }
}
