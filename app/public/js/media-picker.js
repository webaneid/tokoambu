/**
 * Reusable media picker modal with upload + Ambu Magic integration.
 */
(function(){
  const STYLE_ID = 'media-picker-inline-style';

  function injectStyles(){
    if(document.getElementById(STYLE_ID)) return;
    const style=document.createElement('style');
    style.id=STYLE_ID;
    style.innerHTML=`
    .mp-overlay{position:fixed;inset:0;z-index:60;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,0.55);padding:24px;overflow-y:auto;}
    .mp-modal{background:#fff;border-radius:24px;box-shadow:0 35px 80px rgba(15,23,42,0.35);width:100%;max-width:1180px;max-height:92vh;overflow:hidden;display:flex;flex-direction:column;}
    .mp-head{display:flex;align-items:center;justify-content:space-between;padding:18px 28px;border-bottom:1px solid #e5e7eb;gap:16px;}
    .mp-title{font-size:20px;font-weight:700;color:#0f172a;margin:0;}
    .mp-subtitle{font-size:13px;color:#6b7280;margin:4px 0 0;}
    .mp-close{width:44px;height:44px;border:none;border-radius:9999px;background:#f8fafc;color:#0f172a;font-size:22px;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 12px 30px rgba(15,23,42,0.16);transition:transform .2s;}
    .mp-close:hover{transform:rotate(6deg);}
    .mp-tabs{display:flex;gap:10px;padding:14px 28px;border-bottom:1px solid #e5e7eb;background:#fff;}
    .mp-tab{flex:1;padding:10px 0;border-radius:999px;background:#f8fafc;border:1px solid transparent;font-size:13px;font-weight:600;color:#6b7280;cursor:pointer;transition:all .2s;}
    .mp-tab-active{color:#0f172a;background:#fff;border-color:#f59e0b;box-shadow:0 4px 12px rgba(245,158,11,0.25);}
    .mp-body{display:grid;grid-template-columns:minmax(0,2.1fr) minmax(260px,1fr);gap:0;height:560px;max-height:calc(92vh - 170px);}
    .mp-left{display:flex;flex-direction:column;height:100%;min-height:0;}
    .mp-pane{display:flex;flex-direction:column;height:100%;min-height:0;overflow:hidden;}
    .mp-pane.hidden{display:none;}
    .mp-pane[data-pane="upload"],.mp-pane[data-pane="enhance"]{background:#fff;}
    .mp-search{display:flex;align-items:center;gap:12px;padding:18px 28px 12px;}
    .mp-search input{flex:1;border:1px solid #d1d5db;border-radius:16px;padding:12px 16px;font-size:13px;box-shadow:0 6px 16px rgba(15,23,42,0.05);}
    .mp-btn{border:1px solid #d1d5db;border-radius:14px;background:#fff;padding:10px;font-size:13px;cursor:pointer;transition:border-color .2s,color .2s;}
    .mp-btn:hover{border-color:#f59e0b;color:#f59e0b;}
    .mp-grid{flex:1;overflow-y:auto;padding:0 28px 28px;display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;scrollbar-width:thin;-webkit-overflow-scrolling:touch;}
    .mp-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;box-shadow:0 8px 22px rgba(15,23,42,0.08);overflow:hidden;cursor:pointer;transition:box-shadow .2s,transform .2s,border-color .2s;display:flex;flex-direction:column; height:173px;}
    .mp-card:hover{box-shadow:0 16px 36px rgba(15,23,42,0.16);transform:translateY(-2px);}
    .mp-card.is-selected{border-color:#f59e0b;box-shadow:0 24px 50px rgba(245,158,11,0.35);}
    .mp-thumb{position:relative;width:100%;padding-top:100%;background:#f1f5f9;border-radius:18px;overflow:hidden;}
    .mp-thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;}
    .mp-check{position:absolute;top:10px;right:10px;width:26px;height:26px;border-radius:9999px;background:#f59e0b;color:#fff;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;box-shadow:0 12px 22px rgba(15,23,42,0.25);}
    .mp-meta{padding:12px 16px;display:flex;flex-direction:column;gap:4px;}
    .mp-name{font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .mp-mime{font-size:12px;color:#6b7280;}
    .mp-upload-wrap{flex:1;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:32px;}
    .mp-drop{width:100%;border:2px dashed #d1d5db;border-radius:22px;padding:36px;text-align:center;color:#6b7280;font-size:14px;cursor:pointer;transition:background .2s,border-color .2s;display:flex;flex-direction:column;gap:10px;align-items:center;justify-content:center;min-height:320px;}
    .mp-drop input{display:none;}
    .mp-drop-active{border-color:#f59e0b;background:#fff8ec;color:#b45309;}
    .mp-upload-hint{margin-top:14px;font-size:12px;color:#94a3b8;text-align:center;}
    .mp-camera-actions{display:flex;flex-direction:column;gap:10px;margin-top:18px;width:100%;}
    .mp-camera-actions button{width:100%;}
    .mp-btn-camera{border:1px solid #0f172a;background:#0f172a;color:#fff;border-radius:16px;padding:12px 16px;font-size:14px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 10px 25px rgba(15,23,42,0.25);}
    .mp-btn-camera:hover{background:#111e40;border-color:#111e40;}
    .mp-camera-note{font-size:12px;color:#6b7280;text-align:center;}
    .mp-right{padding:28px;border-left:1px solid #e5e7eb;background:#fafafa;overflow-y:auto;min-height:0;}
    .mp-detail-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;color:#6b7280;gap:8px;}
    .mp-empty-icon{font-size:32px;}
    .mp-detail .inner{display:flex;flex-direction:column;gap:18px;height:100%;}
    .mp-preview{width:100%;border-radius:22px;background:#fff;box-shadow:inset 0 0 0 1px #f3f4f6;overflow:hidden;min-height:220px;display:flex;align-items:center;justify-content:center;}
    .mp-preview img{width:100%;height:100%;object-fit:cover;}
    .mp-doc{font-size:13px;color:#475569;padding:18px;text-align:center;}
    .mp-meta-info{display:flex;flex-direction:column;gap:6px;font-size:13px;color:#4b5563;}
    .mp-dtitle{font-size:15px;font-weight:700;color:#0f172a;}
    .mp-links a{font-size:13px;color:#2563eb;text-decoration:none;}
    .mp-action-buttons{display:flex;gap:12px;padding-top:12px;}
    .mp-btn-primary{background:#f59e0b;color:#fff;border:none;border-radius:16px;padding:13px 18px;font-size:13px;font-weight:600;cursor:pointer;box-shadow:0 15px 25px rgba(245,158,11,0.35);}
    .mp-btn-primary:disabled{opacity:.4;cursor:not-allowed;box-shadow:none;}
    .mp-btn-secondary{background:#fff;color:#374151;border:1px solid #d1d5db;border-radius:16px;padding:13px 18px;font-size:13px;font-weight:600;cursor:pointer;}
    .mp-btn-secondary:disabled{opacity:.4;cursor:not-allowed;}
    .mp-ai-wrapper{flex:1;min-height:0;overflow-y:auto;padding:28px;display:flex;flex-direction:column;gap:18px;}
    .mp-ai-shell{border:1px dashed #d1d5db;border-radius:20px;padding:18px;min-height:320px;background:#fff; margin-bottom: 90px}
    .mp-ai-hint{font-size:13px;color:#6b7280;margin:0;}
    .mp-empty-note{grid-column:1/-1;font-size:13px;color:#6b7280;text-align:center;padding:32px 0;}
    .mp-mobile-actions{display:flex;align-items:center;gap:12px;padding:16px 18px;border-top:1px solid #e5e7eb;background:#fff;box-shadow:0 -18px 36px rgba(15,23,42,0.18);position:sticky;bottom:0;margin-top:auto;z-index:5;}
    .mp-mobile-selected{font-size:13px;font-weight:600;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;flex:1;}
    .mp-mobile-buttons{display:flex;gap:10px;width:100%;}
    .mp-mobile-buttons .mp-btn-primary,.mp-mobile-buttons .mp-btn-secondary{flex:1;}
    @media (max-width:1024px){
      .mp-overlay{padding:0;align-items:flex-start;}
      .mp-modal{border-radius:0;min-height:100vh;max-height:none;}
      .mp-body{grid-template-columns:1fr;height:auto;max-height:none;}
      .mp-right{border-left:none;border-top:1px solid #e5e7eb;}
    }
    @media (max-width:640px){
      .mp-head{align-items:flex-start;padding:20px 20px 14px;gap:6px;}
      .mp-tabs{padding:10px 20px 10px;overflow-x:auto;}
      .mp-tab{flex:none;min-width:140px;}
      .mp-body{grid-template-columns:1fr;}
      .mp-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:0 18px 140px;}
      .mp-meta,.mp-meta-info{display:none;}
      .mp-right{display:none;}
      .mp-mobile-actions{position: fixed; bottom: 0px; left: 0px; right: 0px; z-index: var(--ane-z-fixed); max-width: var(--ane-max-width-mobile); margin: 0px auto; width: 100%;}
      #featuredImagePreview {min-height: 350px;}
      .mp-card {height: auto;}
    }
    @media (min-width:641px){
      .mp-mobile-actions{display:none!important;}
    }
    `;
    document.head.appendChild(style);
  }

  function createEl(tag, cls, html){const el=document.createElement(tag);if(cls) el.className=cls;if(html) el.innerHTML=html;return el;}
  function bytesToSize(bytes){if(bytes===0) return '0 B';if(!bytes&&bytes!==0) return '';const sizes=['B','KB','MB','GB'];const i=parseInt(Math.floor(Math.log(bytes)/Math.log(1024)),10);return Math.round(bytes/Math.pow(1024,i),2)+' '+sizes[i];}
  function isImageMedia(item){
    if(!item) return false;
    if(item.mime && item.mime.toLowerCase().startsWith('image/')) return true;
    const source=(item.url||item.filename||'').toLowerCase();
    return !!source.match(/\.(png|jpe?g|webp|gif|bmp|svg)$/);
  }

  window.openMediaPicker=async function(opts){
    injectStyles();
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

    if(!options.listUrl||!options.uploadUrl){console.error('Media picker: listUrl dan uploadUrl wajib diisi');return;}

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
