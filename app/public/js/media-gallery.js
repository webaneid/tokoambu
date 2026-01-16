/**
 * Product Gallery Manager with drag & drop support
 * Handles featured image + gallery (max 5 photos)
 */
(function(){
  const MAX_GALLERY = 5;

  function createEl(tag, cls, html) {
    const el = document.createElement(tag);
    if (cls) el.className = cls;
    if (html) el.innerHTML = html;
    return el;
  }

  window.ProductGallery = class {
    constructor(options) {
      this.featuredImageId = options.featuredImageId || null;
      this.galleryMediaIds = options.galleryMediaIds || [];
      this.onFeaturedChange = options.onFeaturedChange || (() => {});
      this.onGalleryChange = options.onGalleryChange || (() => {});
      this.listUrl = options.listUrl;
      this.uploadUrl = options.uploadUrl;
      this.productId = options.productId || null;
      this.aiEnabled = options.aiEnabled || false;
      this.aiRoutes = options.aiRoutes || null;
      this.csrfToken = options.csrfToken || '';

      // Debug log
      console.log('ProductGallery initialized:', {
        listUrl: this.listUrl,
        uploadUrl: this.uploadUrl,
        productId: this.productId,
        aiEnabled: this.aiEnabled
      });

      this.featuredMedia = null;
      this.galleryMedia = [];

      this.init();
    }

    init() {
      // Load initial data if IDs provided
      if (this.featuredImageId) {
        this.loadFeaturedImage();
      }
      if (this.galleryMediaIds.length > 0) {
        this.loadGalleryImages();
      }
    }

    async loadFeaturedImage() {
      // Featured image is already loaded from backend, just render
      this.renderFeatured();
    }

    async loadGalleryImages() {
      // Gallery images are already loaded from backend, just render
      this.renderGallery();
    }

    openFeaturedPicker() {
      console.log('[ProductGallery] Opening featured picker with AI config:', {
        aiEnabled: this.aiEnabled,
        aiRoutes: this.aiRoutes,
        csrfToken: this.csrfToken ? '***' : null
      });

      openMediaPicker({
        type: 'product_photo',
        title: 'Pilih Gambar Utama',
        listUrl: this.listUrl,
        uploadUrl: this.uploadUrl,
        context: { product_id: this.productId },
        aiEnabled: this.aiEnabled,
        aiRoutes: this.aiRoutes,
        csrfToken: this.csrfToken,
        onSelect: (item) => {
          this.featuredImageId = item.id;
          this.featuredMedia = item;
          this.renderFeatured();
          this.onFeaturedChange(item.id);
        },
      });
    }

    openGalleryPicker() {
      if (this.galleryMediaIds.length >= MAX_GALLERY) {
        alert(`Maksimal ${MAX_GALLERY} foto gallery`);
        return;
      }

      openMediaPicker({
        type: 'product_photo',
        title: 'Pilih Foto untuk Gallery',
        listUrl: this.listUrl,
        uploadUrl: this.uploadUrl,
        context: {
          product_id: this.productId,
          gallery_order: this.galleryMediaIds.length
        },
        aiEnabled: this.aiEnabled,
        aiRoutes: this.aiRoutes,
        csrfToken: this.csrfToken,
        onSelect: (item) => {
          if (this.galleryMediaIds.length < MAX_GALLERY) {
            this.galleryMediaIds.push(item.id);
            this.galleryMedia.push(item);
            this.renderGallery();
            this.onGalleryChange(this.galleryMediaIds);
          }
        },
      });
    }

    renderFeatured() {
      const container = document.getElementById('featuredImagePreview');
      if (!container) return;

      if (this.featuredMedia) {
        container.innerHTML = `
          <img src="${this.featuredMedia.url}" alt="${this.featuredMedia.filename}"
               class="w-full h-full object-cover rounded-lg">
        `;
      } else {
        container.innerHTML = `
          <div class="flex items-center justify-center h-full text-gray-400 text-sm">
            Belum ada gambar utama
          </div>
        `;
      }
    }

    renderGallery() {
      const container = document.getElementById('galleryGrid');
      if (!container) return;

      // Clear content but preserve grid classes
      container.innerHTML = '';
      if (!container.className.includes('grid')) {
        container.className = 'grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-3 gap-4';
      }

      if (this.galleryMedia.length === 0) {
        container.innerHTML = `
          <div class="col-span-full">
            <button type="button" id="btnAddToGallery" class="w-full bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary cursor-pointer transition flex items-center justify-center text-center p-6" style="min-height: 180px;">
              <div>
                <div class="text-3xl text-gray-400">+</div>
                <div class="text-sm text-gray-500 mt-1">Tambah Foto ke Gallery</div>
                <p class="text-xs text-gray-400 mt-1">Foto terbaik membuat produk lebih percaya diri</p>
              </div>
            </button>
          </div>
        `;
        // Re-attach event listener for the new button
        const btnAddToGallery = document.getElementById('btnAddToGallery');
        if (btnAddToGallery) {
          btnAddToGallery.addEventListener('click', () => this.openGalleryPicker());
        }
        return;
      }

      this.galleryMedia.forEach((item, index) => {
        const card = createEl('div', 'relative cursor-move bg-white rounded-xl border-2 border-gray-200 overflow-hidden transition');
        card.style.aspectRatio = '1 / 1';
        card.style.minHeight = '160px';
        card.draggable = true;
        card.dataset.mediaId = item.id;
        card.dataset.index = index;

        card.innerHTML = `
          <img src="${item.url || ''}" alt="${item.filename}" class="w-full h-full object-cover bg-gray-100" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'%3E%3Crect width=\'100\' height=\'100\' fill=\'%23f3f4f6\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'0.3em\' fill=\'%239ca3af\' font-size=\'12\'%3ENo Image%3C/text%3E%3C/svg%3E'">
          <div class="absolute top-2 left-2 bg-white px-2 py-0.5 rounded shadow-sm text-xs font-semibold text-gray-700 z-20">${index + 1}</div>
          <div class="absolute bottom-0 left-0 right-0 px-3 py-2 text-white text-xs font-medium space-y-1" style="background: linear-gradient(180deg, rgba(15,23,42,0) 0%, rgba(15,23,42,0.92) 85%);">
            <span class="block truncate">${item.filename || 'Gambar'}</span>
            <div class="flex items-center gap-2">
              <button type="button" class="gallery-action flex-1 inline-flex items-center justify-center gap-1 px-2 py-1 rounded-full bg-white/92 text-slate-800 border border-white/60 text-[11px] font-semibold" data-replace-index="${index}" aria-label="Ganti foto ${index + 1}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M16.5 4.5c-1.22 0-2.323.575-3.03 1.465l-6.72 3.735c-.216-.042-.439-.065-.665-.065A3.585 3.585 0 0 0 2.5 13.22a3.585 3.585 0 0 0 3.585 3.585c.226 0 .449-.023.665-.065l6.72 3.735a3.585 3.585 0 1 0 6.03-2.835c-.226 0-.449.023-.665.065l-6.72-3.735c.108-.519.108-1.06 0-1.58l6.72-3.735c.216.042.439.065.665.065a3.585 3.585 0 1 0-3.585-3.585Z" />
                </svg>
                <span class="hidden lg:inline">Ganti</span>
              </button>
              <button type="button" class="gallery-action flex-1 inline-flex items-center justify-center gap-1 px-2 py-1 rounded-full text-[11px] font-semibold" data-remove="${item.id}" aria-label="Hapus foto ${index + 1}" style="background:rgba(255,248,240,0.95);color:#B45309;border:1px solid #FCD9BD;">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M9 3.75A.75.75 0 0 0 8.25 4.5V6H4.5a.75.75 0 0 0 0 1.5h15a.75.75 0 0 0 0-1.5H15.75V4.5a.75.75 0 0 0-.75-.75h-6ZM6.75 9a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-1.5 0v-9A.75.75 0 0 1 6.75 9Zm10.5 0a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-1.5 0v-9a.75.75 0 0 1 .75-.75ZM11.25 9a.75.75 0 0 1 .75.75v9a.75.75 0 0 1-1.5 0v-9a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                </svg>
                <span class="hidden lg:inline">Hapus</span>
              </button>
            </div>
          </div>
        `;

        const removeBtn = card.querySelector('[data-remove]');
        const replaceBtn = card.querySelector('[data-replace-index]');
        removeBtn.setAttribute('draggable', 'false');
        replaceBtn.setAttribute('draggable', 'false');

        // Hover/touch highlight for desktop & mobile
        const highlightCard = () => { card.style.borderColor = '#F17B0D'; };
        const resetCard = () => { card.style.borderColor = '#E5E7EB'; };
        card.addEventListener('mouseenter', highlightCard);
        card.addEventListener('mouseleave', resetCard);
        card.addEventListener('touchstart', highlightCard, { passive: true });
        card.addEventListener('touchend', resetCard, { passive: true });
        card.addEventListener('touchcancel', resetCard, { passive: true });

        // Remove button
        removeBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.removeFromGallery(item.id);
        });

        // Replace button
        replaceBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          this.replaceGalleryItem(index);
        });

        // Drag events
        card.addEventListener('dragstart', (e) => this.handleDragStart(e));
        card.addEventListener('dragover', (e) => this.handleDragOver(e));
        card.addEventListener('drop', (e) => this.handleDrop(e));
        card.addEventListener('dragend', (e) => this.handleDragEnd(e));

        container.appendChild(card);
      });

      // Add "Add more" button if less than MAX_GALLERY
      if (this.galleryMedia.length < MAX_GALLERY) {
        const addBtn = createEl('div', 'relative bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 hover:border-primary cursor-pointer transition flex items-center justify-center text-center p-4');
        addBtn.style.aspectRatio = '1 / 1';
        addBtn.style.minHeight = '160px';
        addBtn.innerHTML = `
          <div class="text-center">
            <div class="text-3xl text-gray-400">+</div>
            <div class="text-xs text-gray-500 mt-1">Tambah Foto</div>
          </div>
        `;
        addBtn.addEventListener('click', () => this.openGalleryPicker());
        container.appendChild(addBtn);
      }
    }

    replaceGalleryItem(index) {
      openMediaPicker({
        type: 'product_photo',
        title: 'Ganti Foto Gallery',
        listUrl: this.listUrl,
        uploadUrl: this.uploadUrl,
        context: {
          product_id: this.productId,
          gallery_order: index
        },
        aiEnabled: this.aiEnabled,
        aiRoutes: this.aiRoutes,
        csrfToken: this.csrfToken,
        onSelect: (item) => {
          this.galleryMediaIds[index] = item.id;
          this.galleryMedia[index] = item;
          this.renderGallery();
          this.onGalleryChange(this.galleryMediaIds);
        }
      });
    }

    removeFromGallery(mediaId) {
      const index = this.galleryMediaIds.indexOf(mediaId);
      if (index > -1) {
        this.galleryMediaIds.splice(index, 1);
        this.galleryMedia.splice(index, 1);
        this.renderGallery();
        this.onGalleryChange(this.galleryMediaIds);
      }
    }

    handleDragStart(e) {
      e.target.classList.add('opacity-50');
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', e.target.dataset.index);
    }

    handleDragOver(e) {
      if (e.preventDefault) {
        e.preventDefault();
      }
      e.dataTransfer.dropEffect = 'move';
      return false;
    }

    handleDrop(e) {
      if (e.stopPropagation) {
        e.stopPropagation();
      }
      e.preventDefault();

      const fromIndex = parseInt(e.dataTransfer.getData('text/plain'));
      const toElement = e.target.closest('[data-index]');

      if (!toElement) return;

      const toIndex = parseInt(toElement.dataset.index);

      if (fromIndex !== toIndex) {
        // Reorder arrays
        const movedMediaId = this.galleryMediaIds.splice(fromIndex, 1)[0];
        const movedMedia = this.galleryMedia.splice(fromIndex, 1)[0];

        this.galleryMediaIds.splice(toIndex, 0, movedMediaId);
        this.galleryMedia.splice(toIndex, 0, movedMedia);

        this.renderGallery();
        this.onGalleryChange(this.galleryMediaIds);
      }

      return false;
    }

    handleDragEnd(e) {
      e.target.classList.remove('opacity-50');
      if (e.target && e.target.style) {
        e.target.style.borderColor = '#E5E7EB';
      }
    }

    // Public method to set featured and gallery from backend
    setMedia(featured, gallery) {
      this.featuredMedia = featured;
      this.galleryMedia = gallery || [];
      this.featuredImageId = featured ? featured.id : null;
      this.galleryMediaIds = gallery ? gallery.map(m => m.id) : [];

      this.renderFeatured();
      this.renderGallery();
    }
  };
})();
