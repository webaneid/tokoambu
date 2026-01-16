/**
 * Reusable media picker modal with upload + Ambu Magic integration.
 * CSS styles are loaded from _media-picker.scss
 */
(function(){
  function createEl(tag, cls, html){const el=document.createElement(tag);if(cls) el.className=cls;if(html) el.innerHTML=html;return el;}
  function bytesToSize(bytes){if(bytes===0) return '0 B';if(!bytes&&bytes!==0) return '';const sizes=['B','KB','MB','GB'];const i=parseInt(Math.floor(Math.log(bytes)/Math.log(1024)),10);return Math.round(bytes/Math.pow(1024,i),2)+' '+sizes[i];}
  function isImageMedia(item){
    if(!item) return false;
    if(item.mime && item.mime.toLowerCase().startsWith('image/')) return true;
    const source=(item.url||item.filename||'').toLowerCase();
    return !!source.match(/\.(png|jpe?g|webp|gif|bmp|svg)$/);
  }

  window.openMediaPicker=async function(opts){
    const options=Object.assign({
      type:'payment_proof',
      title:'Pilih Media',
      listUrl:null,
      uploadUrl:null,
      context:{},
      onSelect:null,
      aiEnabled:false,
      aiRoutes:null,
      csrfToken:null,
      onEnhanceComplete:null
    },opts||{});

    if(!options.listUrl||!options.uploadUrl){
      console.error('Media picker: listUrl dan uploadUrl wajib diisi', options);
      return;
    }

    let items=[];let filtered=[];let selected=null;let activeTab='library';let aiStudioInstance=null;

    const overlay=createEl('div','mp-overlay');
    const modal=createEl('div','mp-modal');
    overlay.appendChild(modal);

    modal.innerHTML=`
      <div class="mp-head">
        <div>
          <p class="mp-title">${options.title}</p>
          <p class="mp-subtitle">Pilih foto terbaik untuk katalogmu</p>
        </div>
        <button class="mp-close" data-close aria-label="Tutup">&times;</button>
      </div>
      <div class="mp-tabs">
        <button class="mp-tab mp-tab-active" data-tab="library">Media Library</button>
        <button class="mp-tab" data-tab="upload">Upload Files</button>
        ${options.aiEnabled ? '<button class="mp-tab" data-tab="enhance">Ambu Magic</button>' : ''}
      </div>
      <div class="mp-body">
        <div class="mp-left">
          <div class="mp-pane" data-pane="library">
            <div class="mp-search">
              <input type="text" placeholder="Cari media..." data-search>
              <button class="mp-btn" data-refresh title="Refresh">&#8635;</button>
            </div>
            <div class="mp-grid" data-grid></div>
          </div>
          <div class="mp-pane hidden" data-pane="upload">
            <div class="mp-upload-wrap">
              <div class="mp-drop" data-dropzone>
                <div style="font-size:34px;">⬆️</div>
                <div>Drop file di sini atau klik untuk pilih</div>
                <div style="font-size:12px;color:#9ca3af;">PNG, JPG hingga 5MB</div>
                <input type="file" data-file>
              </div>
              <p class="mp-upload-hint">Tip: gunakan foto square agar tampil konsisten.</p>
              <div class="mp-camera-actions">
                <button type="button" class="mp-btn-camera" data-camera-btn>
                  <span aria-hidden="true">📷</span>
                  <span>Ambil Foto dengan Kamera</span>
                </button>
                <p class="mp-camera-note">Kamera akan terbuka di perangkat yang mendukung.</p>
                <input type="file" accept="image/*" capture="environment" data-camera-input style="display:none;">
              </div>
            </div>
          </div>
          ${options.aiEnabled ? `<div class="mp-pane hidden" data-pane="enhance">
            <div class="mp-ai-wrapper">
              <div>
                <h4 class="mp-dtitle">Ambu Magic Studio</h4>
                <p class="mp-ai-hint">Pilih gambar pada tab Media lalu kreasikan latar tanpa repot.</p>
              </div>
              <div id="mpAiStudioContainer" class="mp-ai-shell">
                <div class="mp-detail-empty">
                  <div class="mp-empty-icon">✨</div>
                  <p class="mp-empty-text">Belum ada gambar terpilih.</p>
                </div>
              </div>
            </div>
          </div>` : ''}
        </div>
        <div class="mp-pane mp-right" data-detail>
          <div class="mp-detail-empty">
            <div class="mp-empty-icon">🖼️</div>
            <p class="mp-empty-text">Pilih gambar untuk melihat detail</p>
          </div>
        </div>
      </div>
      <div class="mp-mobile-actions" data-mobile-actions>
        <div class="mp-mobile-selected" data-mobile-selected>Belum ada gambar dipilih</div>
        <div class="mp-mobile-buttons">
          ${options.aiEnabled ? '<button class="mp-btn-secondary" data-mobile-enhance disabled>Ambu Magic</button>' : ''}
          <button class="mp-btn-primary" data-mobile-select disabled>Gunakan</button>
        </div>
      </div>
    `;

    const closeBtn=modal.querySelector('[data-close]');
    const tabButtons=modal.querySelectorAll('[data-tab]');
    const panes=modal.querySelectorAll('.mp-left [data-pane]');
    const gridEl=modal.querySelector('[data-grid]');
    const searchEl=modal.querySelector('[data-search]');
    const refreshBtn=modal.querySelector('[data-refresh]');
    const detailEl=modal.querySelector('[data-detail]');
    const dropzone=modal.querySelector('[data-dropzone]');
    const fileInput=modal.querySelector('[data-file]');
    const cameraBtn=modal.querySelector('[data-camera-btn]');
    const cameraInput=modal.querySelector('[data-camera-input]');
    const mobileSelectedLabel=modal.querySelector('[data-mobile-selected]');
    const mobileSelectBtn=modal.querySelector('[data-mobile-select]');
    const mobileEnhanceBtn=modal.querySelector('[data-mobile-enhance]');
    const enforceThumbSquares=()=>{
      if(!gridEl) return;
      requestAnimationFrame(()=>{
        gridEl.querySelectorAll('.mp-thumb').forEach(thumb=>{
          const width=thumb.offsetWidth;
          if(width>0){thumb.style.height=`${width}px`;}
        });
      });
    };

    const handleKey=(e)=>{if(e.key==='Escape'){close();}};
    const close=()=>{
      document.removeEventListener('keydown',handleKey);
      window.removeEventListener('resize',enforceThumbSquares);
      if(aiStudioInstance&&typeof aiStudioInstance.destroy==='function'){aiStudioInstance.destroy();}
      overlay.remove();
    };

    overlay.addEventListener('click',(e)=>{if(e.target===overlay){close();}});
    closeBtn?.addEventListener('click',close);
    document.addEventListener('keydown',handleKey);

    tabButtons.forEach(btn=>{
      btn.addEventListener('click',()=>{
        const tab=btn.getAttribute('data-tab');
        if(tab==='enhance'&&!options.aiEnabled){return;}
        setTab(tab);
      });
    });

    function setTab(tab){
      activeTab=tab;
      tabButtons.forEach(btn=>btn.classList.toggle('mp-tab-active',btn.getAttribute('data-tab')===tab));
      panes.forEach(pane=>pane.classList.toggle('hidden',pane.getAttribute('data-pane')!==tab));
    }

    function updateMobileState(item){
      if(item){
        if(mobileSelectedLabel){mobileSelectedLabel.textContent=item.filename;}
        if(mobileSelectBtn){mobileSelectBtn.disabled=false;}
        if(mobileEnhanceBtn){mobileEnhanceBtn.disabled=false;}
      }else{
        if(mobileSelectedLabel){mobileSelectedLabel.textContent='Pilih gambar terlebih dahulu';}
        if(mobileSelectBtn){mobileSelectBtn.disabled=true;}
        if(mobileEnhanceBtn){mobileEnhanceBtn.disabled=true;}
      }
    }

    function renderDetail(item){
      if(!item){
        detailEl.innerHTML='<div class="mp-detail-empty"><div class="mp-empty-icon">🖼️</div><p class="mp-empty-text">Pilih gambar untuk melihat detail</p></div>';
        updateMobileState(null);
        return;
      }

      const isImage=isImageMedia(item);
      const enhanceBtn=options.aiEnabled?'<button class="mp-btn-secondary" data-enhance>Ambu Magic</button>':'';

      detailEl.innerHTML=`
        <div class="mp-detail">
          <div class="inner">
            <div class="mp-preview">
              ${isImage?`<img src="${item.url}" alt="${item.filename}">`:`<div class="mp-doc">${item.filename}</div>`}
            </div>
            <div class="mp-meta-info">
              <div class="mp-dtitle">${item.filename}</div>
              <div>${item.mime||''}</div>
              <div>${bytesToSize(item.size)}</div>
            </div>
            <div class="mp-links">
              <a href="${item.url}" target="_blank" rel="noopener">Buka di tab baru</a>
            </div>
            <div class="mp-action-buttons">
              ${enhanceBtn}
              <button class="mp-btn-primary" data-select>Gunakan</button>
            </div>
          </div>
        </div>`;

      detailEl.querySelector('[data-select]')?.addEventListener('click',()=>{
        if(typeof options.onSelect==='function'){options.onSelect(item);}
        close();
      });

      detailEl.querySelector('[data-enhance]')?.addEventListener('click',()=>handleEnhanceClick(item));
      updateMobileState(item);
    }

    function renderGrid(list){
      gridEl.innerHTML='';
      if(!list.length){gridEl.innerHTML='<div class="mp-empty-note">Belum ada media.</div>';renderDetail(null);return;}
      list.forEach(item=>{
        const isSelected=selected&&selected.id===item.id;
        const card=createEl('button',`mp-card ${isSelected?'is-selected':''}`);
        card.innerHTML=`
          <div class="mp-thumb">
            ${isImageMedia(item)?`<img src="${item.url}" alt="${item.filename}">`:`<span class="mp-doc">${item.filename}</span>`}
            ${isSelected?'<span class="mp-check">✓</span>':''}
          </div>
          <div class="mp-meta">
            <div class="mp-name">${item.filename}</div>
            <div class="mp-mime">${bytesToSize(item.size)}</div>
          </div>`;
        card.addEventListener('click',()=>{
          selected=item;
          renderGrid(filtered);
          renderDetail(item);
        });
        gridEl.appendChild(card);
      });
      enforceThumbSquares();
    }

    async function loadList(){
      gridEl.innerHTML='<div class="mp-empty-note">Memuat...</div>';
      try{
        const res=await fetch(options.listUrl,{headers:{'Accept':'application/json'}});
        const data=await res.json();
        items=data.data||data||[];
        filtered=items;
        if(selected){
          const match=items.find(it=>it.id===selected.id);
          selected=match||null;
        }
        renderGrid(filtered);
        if(selected){renderDetail(selected);} else {renderDetail(null);}
      }catch(err){
        console.error(err);
        gridEl.innerHTML='<div class="mp-empty-note" style="color:#dc2626;">Gagal memuat media.</div>';
      }
    }

    function handleEnhanceClick(item){
      if(!options.aiEnabled){return;}
      setTab('enhance');
      const container=modal.querySelector('#mpAiStudioContainer');
      if(!container){return;}
      if(!options.aiRoutes){
        container.innerHTML='<p class="mp-ai-hint" style="color:#dc2626;">Konfigurasi Ambu Magic belum lengkap.</p>';
        return;
      }

      if(!aiStudioInstance){
        container.innerHTML=`
          <input type="hidden" id="mpAiMediaId" value="${item.id}">
          <div style="margin-bottom:12px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Background Color</label>
            <input type="color" id="mpAiBackgroundColor" value="#FFFFFF" style="width:100%;height:44px;border:1px solid #d1d5db;border-radius:12px;cursor:pointer;">
          </div>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#374151;margin-bottom:12px;">
            <input type="checkbox" id="mpAiUseSolid" checked style="width:16px;height:16px;accent-color:#f59e0b;">
            <span>Gunakan latar solid</span>
          </label>
          <div style="margin-bottom:12px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Quick Presets</label>
            <div id="mpAiQuickPresets" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;"></div>
          </div>
          <div style="margin-bottom:12px;">
            <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;">Features</label>
            <div id="mpAiFeaturesContainer" style="display:grid;grid-template-columns:1fr;gap:6px;"></div>
          </div>
          <div id="mpAiStatus" style="font-size:12px;color:#6b7280;margin-bottom:12px;min-height:18px;"></div>
          <button type="button" id="mpAiEnhanceTrigger" style="width:100%;padding:12px;background:#f59e0b;color:#fff;border:none;border-radius:12px;font-weight:600;cursor:pointer;">
            Jalankan Gemini
          </button>
        `;

        if(typeof window.AiStudioManager==='undefined'){
          container.innerHTML='<p class="mp-ai-hint" style="color:#dc2626;">AiStudioManager belum dimuat.</p>';
          return;
        }

        const aiConfig={
          routes:options.aiRoutes,
          csrfToken:options.csrfToken||document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          maxPollAttempts:40,
          pollIntervalMs:2000,
          elementIds:{
            mediaIdInput:'mpAiMediaId',
            backgroundColor:'mpAiBackgroundColor',
            useSolid:'mpAiUseSolid',
            featuresContainer:'mpAiFeaturesContainer',
            quickPresetsContainer:'mpAiQuickPresets',
            enhanceTrigger:'mpAiEnhanceTrigger',
            status:'mpAiStatus'
          },
          onEnhanceComplete:(enhancedMediaId)=>{
            loadList().then(()=>{
              const enhancedItem=items.find(it=>it.id===enhancedMediaId);
              if(enhancedItem){
                selected=enhancedItem;
                renderGrid(filtered);
                renderDetail(enhancedItem);
                setTab('library');
                if(typeof options.onEnhanceComplete==='function'){
                  options.onEnhanceComplete(enhancedItem);
                }
              }
            });
          }
        };

        aiStudioInstance=new window.AiStudioManager(aiConfig);
      }else{
        aiStudioInstance.selectMedia(item.id,item.filename);
        const hidden=container.querySelector('#mpAiMediaId');
        if(hidden){hidden.value=item.id;}
      }
    }

    async function handleUpload(file){
      const formData=new FormData();
      formData.append('file',file);
      formData.append('type',options.type);
      if(options.context){
        Object.entries(options.context).forEach(([key,val])=>{
          if(val!==undefined&&val!==null){formData.append(key,val);}
        });
      }

      try{
        const csrf=options.csrfToken||document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const res=await fetch(options.uploadUrl,{
          method:'POST',
          headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},
          body:formData
        });
        const body=await res.json();
        if(res.status===201){
          const item=body.media;
          selected=item;
          setTab('library');
          await loadList();
          renderDetail(item);
        }else{
          alert(body.message||'Upload gagal');
        }
      }catch(err){
        console.error(err);
        alert('Upload gagal');
      }
    }

    searchEl?.addEventListener('input',()=>{
      const q=searchEl.value.toLowerCase();
      filtered=items.filter(it=>(it.filename||'').toLowerCase().includes(q));
      renderGrid(filtered);
    });
    refreshBtn?.addEventListener('click',loadList);

    if(dropzone){
      const triggerPick=()=>fileInput?.click();
      dropzone.addEventListener('click',triggerPick);
      dropzone.addEventListener('dragover',(e)=>{e.preventDefault();dropzone.classList.add('mp-drop-active');});
      dropzone.addEventListener('dragleave',()=>dropzone.classList.remove('mp-drop-active'));
      dropzone.addEventListener('drop',(e)=>{
        e.preventDefault();
        dropzone.classList.remove('mp-drop-active');
        if(e.dataTransfer.files.length){handleUpload(e.dataTransfer.files[0]);}
      });
      fileInput?.addEventListener('change',()=>{if(fileInput.files.length){handleUpload(fileInput.files[0]);}});
    }

    if(cameraBtn && cameraInput){
      cameraBtn.addEventListener('click',()=>cameraInput.click());
      cameraInput.addEventListener('change',()=>{
        if(cameraInput.files && cameraInput.files.length){
          handleUpload(cameraInput.files[0]);
          cameraInput.value='';
        }
      });
    }

    mobileSelectBtn?.addEventListener('click',()=>{
      if(!selected) return;
      if(typeof options.onSelect==='function'){options.onSelect(selected);}
      close();
    });

    mobileEnhanceBtn?.addEventListener('click',()=>{
      if(selected){handleEnhanceClick(selected);}
    });

    document.body.appendChild(overlay);
    window.addEventListener('resize',enforceThumbSquares);
    setTab('library');
    renderDetail(null);
    updateMobileState(null);
    loadList();
    enforceThumbSquares();
  };
})();
