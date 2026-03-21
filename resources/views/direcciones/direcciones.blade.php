@extends('layouts.app')
@section('title', 'Mis Direcciones')

@push('head_styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#dir-map{height:280px;width:100%;border-radius:8px;z-index:1;}
.dir-card{transition:box-shadow .2s;}
.dir-card:hover{box-shadow:0 4px 18px rgba(0,0,0,.1);}
.dir-badge{background:#dcfce7;color:#16a34a;font-size:.72rem;font-weight:600;padding:2px 8px;border-radius:12px;}
.dir-input{width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:.875rem;outline:none;transition:border-color .2s;box-sizing:border-box;}
.dir-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.dir-label{display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;}
.dir-btn-p{background:#3b82f6;color:#fff;border:none;border-radius:8px;padding:10px 22px;font-size:.875rem;font-weight:600;cursor:pointer;}
.dir-btn-p:hover{background:#2563eb;}
.dir-btn-s{background:#f3f4f6;color:#374151;border:1px solid #d1d5db;border-radius:8px;padding:10px 22px;font-size:.875rem;font-weight:600;cursor:pointer;}
.dir-btn-s:hover{background:#e5e7eb;}
.dir-btn-loc{background:#0ea5e9;color:#fff;border:none;border-radius:8px;padding:9px 14px;font-size:.82rem;font-weight:600;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:6px;}
.dir-btn-loc:hover{background:#0284c7;}
.dir-btn-loc:disabled{background:#94a3b8;cursor:not-allowed;}
#dir-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;padding:16px;}
#dir-overlay.open{display:flex;}
#dir-box{background:#fff;border-radius:14px;width:100%;max-width:900px;max-height:92vh;overflow-y:auto;position:relative;padding:28px;}
.dir-sec{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px;padding-bottom:4px;border-bottom:1px solid #f3f4f6;}
</style>
@endpush

@section('content')
<div style="max-width:1000px;margin:0 auto;padding:28px 16px;">

  <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
    <a href="#" onclick="history.back();return false;" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;font-size:.85rem;color:#374151;text-decoration:none;font-weight:500;">
      <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Volver
    </a>
    <h1 style="font-size:1.5rem;font-weight:700;color:#111827;margin:0;">Mis Direcciones</h1>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">

    <button id="btn-nueva" style="border:2px dashed #d1d5db;border-radius:12px;padding:28px 16px;background:#fafafa;cursor:pointer;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;color:#6b7280;font-weight:600;font-size:.9rem;">
      <svg xmlns="http://www.w3.org/2000/svg" style="width:32px;height:32px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Agregar direcci&oacute;n
    </button>

    @forelse($direcciones as $dir)
    <div class="dir-card" style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;position:relative;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <div>
          @if($dir->es_predeterminada)
            <span class="dir-badge">&#10004; Predeterminada</span>
          @else
            <button class="btn-set-default" data-id="{{ $dir->id_direccion }}" style="font-size:.75rem;color:#3b82f6;background:none;border:none;cursor:pointer;padding:0;font-weight:500;">Establecer predeterminada</button>
          @endif
        </div>
        <div style="display:flex;gap:6px;">
          <button class="btn-edit" data-id="{{ $dir->id_direccion }}" style="background:#eff6ff;border:none;border-radius:6px;padding:5px 10px;font-size:.78rem;color:#3b82f6;cursor:pointer;font-weight:600;">&#9998; Editar</button>
          <form method="POST" action="{{ route('direcciones.destroy', $dir) }}" onsubmit="return confirm('Eliminar esta direcci\u00f3n?');" style="margin:0;">
            @csrf @method('DELETE')
            <button type="submit" style="background:#fef2f2;border:none;border-radius:6px;padding:5px 10px;font-size:.78rem;color:#ef4444;cursor:pointer;font-weight:600;">&#128465; Eliminar</button>
          </form>
        </div>
      </div>
      <div style="font-size:.85rem;color:#374151;line-height:1.75;">
        <div style="font-weight:600;color:#111827;margin-bottom:2px;">{{ ucfirst(Auth::user()->nombres) }} {{ ucfirst(Auth::user()->apellidos) }}</div>
        <div>{{ ucfirst($dir->provincia->provincia ?? '') }}, {{ ucfirst($dir->municipio->municipio ?? '') }}</div>
        @if($dir->sector)<div>Sector: {{ ucfirst($dir->sector) }}</div>@endif
        <div>{{ ucfirst($dir->calle) }} #{{ $dir->N_casa_edificio }}{{ $dir->apto ? ', Apto '.$dir->apto : '' }}</div>
        <div style="color:#6b7280;margin-top:4px;">&#128222; {{ $dir->telefono_contacto }}</div>
        @if($dir->geolocalizacion)<div style="color:#9ca3af;font-size:.75rem;margin-top:2px;">&#128205; {{ $dir->geolocalizacion }}</div>@endif
      </div>
    </div>
    @empty
    <div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af;">No tienes direcciones registradas a&uacute;n.</div>
    @endforelse
  </div>
</div>

{{-- MODAL --}}
<div id="dir-overlay">
  <div id="dir-box">
    <button id="btn-cerrar" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#9ca3af;line-height:1;">&times;</button>
    <h2 id="dir-titulo" style="font-size:1.2rem;font-weight:700;color:#111827;margin:0 0 20px;">Nueva direcci&oacute;n</h2>
    <input type="hidden" id="dir-id">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

      <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="dir-sec">Datos personales</div>
        <div><label class="dir-label">Nombre completo</label>
          <input type="text" id="dir-nombre" value="{{ ucfirst(Auth::user()->nombres) }} {{ ucfirst(Auth::user()->apellidos) }}" readonly class="dir-input" style="background:#f9fafb;"></div>
        <div><label class="dir-label">Tel&eacute;fono <span style="color:#ef4444;">*</span></label>
          <input type="text" id="dir-telefono" value="{{ Auth::user()->telefono }}" class="dir-input" placeholder="809/000/0000" maxlength="12"></div>
        <div class="dir-sec" style="margin-top:4px;">Direcci&oacute;n</div>
        <div><label class="dir-label">Calle <span style="color:#ef4444;">*</span></label>
          <input type="text" id="dir-calle" class="dir-input" placeholder="Ej: Calle Principal"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
          <div><label class="dir-label">N&deg; Casa / Edificio <span style="color:#ef4444;">*</span></label>
            <input type="text" id="dir-casa" class="dir-input" placeholder="Ej: 12"></div>
          <div><label class="dir-label">Apartamento</label>
            <input type="text" id="dir-apto" class="dir-input" placeholder="Opcional"></div>
        </div>
        <div><label class="dir-label">Sector</label>
          <input type="text" id="dir-sector" class="dir-input" placeholder="Ej: Los Prados"></div>
      </div>

      <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="dir-sec">Ubicaci&oacute;n</div>
        <div><label class="dir-label">Provincia <span style="color:#ef4444;">*</span></label>
          <select id="dir-provincia" class="dir-input"><option value="">Selecciona provincia</option></select></div>
        <div><label class="dir-label">Municipio <span style="color:#ef4444;">*</span></label>
          <select id="dir-municipio" class="dir-input" disabled><option value="">Primero selecciona provincia</option></select></div>
        <div>
          <label class="dir-label">Coordenadas GPS</label>
          <div style="display:flex;gap:8px;align-items:center;">
            <input type="text" id="dir-geo" readonly class="dir-input" style="background:#f9fafb;flex:1;" placeholder="Haz clic en el mapa o usa GPS">
            <button type="button" id="btn-gps" class="dir-btn-loc">
              <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              GPS
            </button>
          </div>
          <p id="dir-geo-error" style="display:none;color:#ef4444;font-size:.78rem;margin-top:4px;"></p>
        </div>
        <div>
          <label class="dir-label">Mapa <span style="font-size:.7rem;color:#9ca3af;">(haz clic para marcar tu ubicaci&oacute;n)</span></label>
          <div id="dir-map"></div>
        </div>
      </div>
    </div>

    <div id="dir-error-msg" style="display:none;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;color:#dc2626;font-size:.85rem;margin-top:16px;"></div>
    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid #f3f4f6;">
      <button type="button" id="btn-cancelar" class="dir-btn-s">Cancelar</button>
      <button type="button" id="btn-guardar" class="dir-btn-p">Guardar direcci&oacute;n</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
  let map = null, marker = null;
  const overlay  = document.getElementById('dir-overlay');
  const box      = document.getElementById('dir-box');
  const titulo   = document.getElementById('dir-titulo');
  const idHidden = document.getElementById('dir-id');
  const fNombre  = document.getElementById('dir-nombre');
  const fTel     = document.getElementById('dir-telefono');
  const fCalle   = document.getElementById('dir-calle');
  const fCasa    = document.getElementById('dir-casa');
  const fApto    = document.getElementById('dir-apto');
  const fSector  = document.getElementById('dir-sector');
  const fProv    = document.getElementById('dir-provincia');
  const fMun     = document.getElementById('dir-municipio');
  const fGeo     = document.getElementById('dir-geo');
  const geoErr   = document.getElementById('dir-geo-error');
  const errMsg   = document.getElementById('dir-error-msg');
  const btnGPS   = document.getElementById('btn-gps');
  const btnGuard = document.getElementById('btn-guardar');

  // ── Mapa ──────────────────────────────────────────────────
  function initMap(lat, lng, zoom) {
    lat  = lat  || 18.7357;
    lng  = lng  || -70.1627;
    zoom = zoom || 8;
    if (map) { map.setView([lat, lng], zoom); map.invalidateSize(); return; }
    map = L.map('dir-map', {
      center: [lat, lng], zoom: zoom,
      maxBounds: L.latLngBounds(L.latLng(15.0,-75.0), L.latLng(20.5,-68.0)),
      maxBoundsViscosity: 0.9
    });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      minZoom: 7, maxZoom: 19
    }).addTo(map);
    map.on('click', function(e) {
      var la = e.latlng.lat.toFixed(6), lo = e.latlng.lng.toFixed(6);
      fGeo.value = la + ', ' + lo;
      if (marker) map.removeLayer(marker);
      marker = L.marker([la, lo]).addTo(map).bindPopup('Ubicaci\u00f3n seleccionada').openPopup();
    });
  }

  function setMarker(lat, lng, popup) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(map).bindPopup(popup || 'Ubicaci\u00f3n').openPopup();
    map.setView([lat, lng], 15);
  }

  // ── Modal ─────────────────────────────────────────────────
  function abrirModal() {
    overlay.classList.add('open');
    setTimeout(function() {
      initMap();
      if (map) map.invalidateSize();
    }, 200);
  }
  function cerrarModal() {
    overlay.classList.remove('open');
    resetForm();
  }
  function resetForm() {
    idHidden.value = '';
    titulo.textContent = 'Nueva direcci\u00f3n';
    fCalle.value = ''; fCasa.value = ''; fApto.value = ''; fSector.value = '';
    fProv.value = ''; fMun.value = ''; fMun.disabled = true;
    fGeo.value = ''; geoErr.style.display = 'none'; errMsg.style.display = 'none';
    if (marker && map) { map.removeLayer(marker); marker = null; }
  }

  document.getElementById('btn-nueva').addEventListener('click', abrirModal);
  document.getElementById('btn-cerrar').addEventListener('click', cerrarModal);
  document.getElementById('btn-cancelar').addEventListener('click', cerrarModal);
  overlay.addEventListener('click', function(e){ if(e.target===overlay) cerrarModal(); });

  // ── Provincias / Municipios ───────────────────────────────
  function cargarProvincias() {
    fetch('/provincias').then(r=>r.json()).then(res=>{
      var lista = res.data || res;
      fProv.innerHTML = '<option value="">Selecciona provincia</option>';
      lista.forEach(p => fProv.innerHTML += '<option value="'+p.id+'">'+p.nombre+'</option>');
    });
  }
  fProv.addEventListener('change', function() {
    var id = this.value;
    fMun.disabled = !id;
    fMun.innerHTML = '<option value="">Selecciona municipio</option>';
    if (!id) return;
    fetch('/municipios?id_provincia='+id).then(r=>r.json()).then(res=>{
      var lista = res.data || res;
      lista.forEach(m => fMun.innerHTML += '<option value="'+m.id+'">'+m.nombre+'</option>');
      fMun.disabled = false;
    });
  });
  fMun.addEventListener('change', function() {
    var pText = fProv.options[fProv.selectedIndex]?.text;
    var mText = this.options[this.selectedIndex]?.text;
    if (!pText || !mText || this.value === '') return;
    var q = encodeURIComponent(mText + ', ' + pText + ', Rep\u00fablica Dominicana');
    fetch('https://nominatim.openstreetmap.org/search?format=json&q='+q+'&limit=1')
      .then(r=>r.json()).then(data=>{
        if (data.length > 0) {
          var lat = parseFloat(data[0].lat), lng = parseFloat(data[0].lon);
          if (map) { map.setView([lat, lng], 13); }
          else { initMap(lat, lng, 13); }
          if (marker) map.removeLayer(marker);
          marker = L.marker([lat, lng]).addTo(map).bindPopup(mText).openPopup();
        }
      }).catch(function(){});
  });

  // ── GPS ───────────────────────────────────────────────────
  btnGPS.addEventListener('click', function() {
    if (!navigator.geolocation) {
      geoErr.textContent = 'Geolocalización no soportada'; geoErr.style.display = 'block'; return;
    }
    btnGPS.disabled = true; btnGPS.textContent = 'Detectando...';
    navigator.geolocation.getCurrentPosition(function(pos) {
      var la = pos.coords.latitude.toFixed(6), lo = pos.coords.longitude.toFixed(6);
      fGeo.value = la + ', ' + lo;
      geoErr.style.display = 'none';
      if (!map) initMap(parseFloat(la), parseFloat(lo), 16);
      else { map.invalidateSize(); }
      setMarker(la, lo, 'Tu ubicaci\u00f3n actual');
      btnGPS.disabled = false; btnGPS.textContent = 'GPS';
    }, function(err) {
      var msgs = {1:'Permiso denegado',2:'Ubicaci\u00f3n no disponible',3:'Tiempo agotado'};
      geoErr.textContent = msgs[err.code] || 'Error desconocido'; geoErr.style.display = 'block';
      btnGPS.disabled = false; btnGPS.textContent = 'GPS';
    }, {enableHighAccuracy:true, timeout:10000, maximumAge:0});
  });

  // ── Guardar ───────────────────────────────────────────────
  btnGuard.addEventListener('click', async function() {
    errMsg.style.display = 'none';
    if (!fCalle.value.trim()) { showErr('La calle es requerida'); fCalle.focus(); return; }
    if (!fCasa.value.trim())  { showErr('El n\u00famero de casa es requerido'); fCasa.focus(); return; }
    if (!fProv.value)         { showErr('Selecciona una provincia'); fProv.focus(); return; }
    if (!fMun.value)          { showErr('Selecciona un municipio'); fMun.focus(); return; }
    var id = idHidden.value;
    var url = id ? '/direcciones/'+id : '/direcciones';
    var method = id ? 'PUT' : 'POST';
    btnGuard.disabled = true; btnGuard.textContent = 'Guardando...';
    try {
      var res = await fetch(url, {
        method: method,
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
        body: JSON.stringify({calle:fCalle.value,N_casa_edificio:fCasa.value,apto:fApto.value,sector:fSector.value,id_provincia:fProv.value,id_municipio:fMun.value,geolocalizacion:fGeo.value,telefono_contacto:fTel.value})
      });
      var data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Error al guardar');
      cerrarModal(); window.location.reload();
    } catch(e) { showErr(e.message); }
    finally { btnGuard.disabled = false; btnGuard.textContent = 'Guardar direcci\u00f3n'; }
  });

  function showErr(msg) { errMsg.textContent = msg; errMsg.style.display = 'block'; }

  // ── Editar ────────────────────────────────────────────────
  document.querySelectorAll('.btn-edit').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.dataset.id;
      fetch('/direcciones/'+id+'/edit').then(r=>r.json()).then(function(d) {
        idHidden.value = id;
        titulo.textContent = 'Editar direcci\u00f3n';
        fCalle.value  = d.calle || '';
        fCasa.value   = d.N_casa_edificio || '';
        fApto.value   = d.apto || '';
        fSector.value = d.sector || '';
        fTel.value    = d.telefono_contacto || '';
        fGeo.value    = d.geolocalizacion || '';
        fProv.value   = d.id_provincia || '';
        fProv.dispatchEvent(new Event('change'));
        setTimeout(function(){ fMun.value = d.id_municipio || ''; }, 400);
        abrirModal();
        if (d.geolocalizacion) {
          var parts = d.geolocalizacion.split(',').map(Number);
          if (parts.length === 2 && !isNaN(parts[0])) {
            setTimeout(function(){ setMarker(parts[0], parts[1], 'Ubicaci\u00f3n guardada'); }, 300);
          }
        }
      }).catch(function(){ alert('Error al cargar la direcci\u00f3n'); });
    });
  });

  // ── Predeterminada ────────────────────────────────────────
  document.querySelectorAll('.btn-set-default').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.dataset.id;
      var textoOriginal = this.textContent;
      this.disabled = true;
      this.textContent = 'Procesando...';
      var self = this;
      fetch('/direccion/predeterminada/'+id, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json'}
      }).then(r=>r.json()).then(function(d){
        if(d.success) { window.location.reload(); }
        else { self.disabled = false; self.textContent = textoOriginal; }
      }).catch(function(){ self.disabled = false; self.textContent = textoOriginal; alert('Error al actualizar'); });
    });
  });

  // ── Tel formato ───────────────────────────────────────────
  fTel.addEventListener('input', function() {
    var n = this.value.replace(/\D/g,'').slice(0,10);
    if (n.length <= 3) this.value = n;
    else if (n.length <= 6) this.value = n.slice(0,3)+'/'+n.slice(3);
    else this.value = n.slice(0,3)+'/'+n.slice(3,6)+'/'+n.slice(6);
  });

  // ── Init ──────────────────────────────────────────────────
  cargarProvincias();
})();
</script>
@endpush
