@extends('layouts.app')
@section('title', 'Mensajes Predefinidos - Admin')
@section('content')
<div style="padding:24px;max-width:1200px;margin:0 auto;font-family:sans-serif;">

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <h1 style="font-size:1.5rem;font-weight:700;color:#1e293b;margin:0;">Mensajes Predefinidos</h1>
    <a href="{{ route('admin.index') }}" style="font-size:.82rem;color:#64748b;text-decoration:none;">Volver al panel</a>
  </div>

  @if(session('success'))
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:.85rem;">
    {{ session('success') }}
  </div>
  @endif

  {{-- LEYENDA DE ROLES --}}
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
    <span style="background:#dbeafe;color:#1d4ed8;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:600;">Emisor — quien propone el intercambio</span>
    <span style="background:#fce7f3;color:#9d174d;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:600;">Receptor — dueño del artículo</span>
    <span style="background:#f1f5f9;color:#475569;padding:3px 12px;border-radius:20px;font-size:.75rem;font-weight:600;">General — disponible para ambos</span>
  </div>

  {{-- TABS POR ROL --}}
  <div style="display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:20px;">
    @foreach(['emisor','receptor','general'] as $r)
    <button type="button" onclick="filtrarRol('{{ $r }}')" id="tab-{{ $r }}"
      style="padding:8px 20px;font-size:.85rem;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:#64748b;transition:all .15s;">
      {{ ucfirst($r) }}
      <span style="background:#f1f5f9;color:#64748b;padding:1px 7px;border-radius:10px;font-size:.72rem;margin-left:4px;">
        {{ $mensajes->where('rol', $r)->count() }}
      </span>
    </button>
    @endforeach
    <button type="button" onclick="filtrarRol('todos')" id="tab-todos"
      style="padding:8px 20px;font-size:.85rem;font-weight:600;border:none;background:none;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;color:#64748b;">
      Todos ({{ $mensajes->count() }})
    </button>
  </div>

  {{-- TABLA --}}
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:28px;">
    <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
      <thead>
        <tr style="background:#f8fafc;">
          <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Título</th>
          <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Mensaje</th>
          <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Tipo</th>
          <th style="padding:10px 14px;text-align:left;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Rol</th>
          <th style="padding:10px 14px;text-align:center;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Activo</th>
          <th style="padding:10px 14px;text-align:center;color:#64748b;font-weight:600;border-bottom:1px solid #e2e8f0;">Acciones</th>
        </tr>
      </thead>
      <tbody id="tabla-mensajes">
        @foreach($mensajes as $msg)
        @php
          $rolColor = match($msg->rol) {
            'emisor'   => 'background:#dbeafe;color:#1d4ed8;',
            'receptor' => 'background:#fce7f3;color:#9d174d;',
            default    => 'background:#f1f5f9;color:#475569;',
          };
        @endphp
        <tr class="fila-msg" data-rol="{{ $msg->rol }}" style="border-bottom:1px solid #f1f5f9;{{ !$msg->activo ? 'opacity:.5;' : '' }}">
          <td style="padding:10px 14px;font-weight:600;color:#1e293b;">{{ $msg->titulo }}</td>
          <td style="padding:10px 14px;color:#475569;max-width:300px;">{{ $msg->mensaje }}</td>
          <td style="padding:10px 14px;">
            <span style="background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:600;">{{ $msg->tipo }}</span>
          </td>
          <td style="padding:10px 14px;">
            <span style="{{ $rolColor }}padding:2px 10px;border-radius:10px;font-size:.72rem;font-weight:600;">{{ ucfirst($msg->rol) }}</span>
          </td>
          <td style="padding:10px 14px;text-align:center;">
            @if(auth()->user()->isSuperAdminUser())
            <button type="button" onclick="toggleActivo({{ $msg->id }}, this)"
              style="background:{{ $msg->activo ? '#10b981' : '#94a3b8' }};color:#fff;border:none;border-radius:20px;padding:3px 12px;font-size:.72rem;cursor:pointer;font-weight:600;">
              {{ $msg->activo ? 'Sí' : 'No' }}
            </button>
            @else
            <span style="background:{{ $msg->activo ? '#10b981' : '#94a3b8' }};color:#fff;border-radius:20px;padding:3px 12px;font-size:.72rem;font-weight:600;">
              {{ $msg->activo ? 'Sí' : 'No' }}
            </span>
            @endif
          </td>
          <td style="padding:10px 14px;text-align:center;white-space:nowrap;">
            @if(auth()->user()->isSuperAdminUser())
            <button type="button" onclick="abrirEditar({{ $msg->id }}, '{{ addslashes($msg->titulo) }}', '{{ addslashes($msg->mensaje) }}', '{{ $msg->tipo }}', '{{ $msg->rol }}')"
              style="background:#3b82f6;color:#fff;border:none;border-radius:5px;padding:4px 10px;font-size:.72rem;cursor:pointer;margin-right:4px;">
              Editar
            </button>
            <form method="POST" action="{{ route('admin.mensajes.destroy', $msg->id) }}" style="display:inline;" onsubmit="return confirm('Eliminar este mensaje?')">
              @csrf @method('DELETE')
              <button type="submit" style="background:#ef4444;color:#fff;border:none;border-radius:5px;padding:4px 10px;font-size:.72rem;cursor:pointer;">
                Eliminar
              </button>
            </form>
            @else
            <span style="color:#94a3b8;font-size:.72rem;">Solo lectura</span>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- FORMULARIO CREAR — solo superadmin --}}
  @if(auth()->user()->isSuperAdminUser())
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:20px;margin-bottom:28px;">
    <h3 style="margin:0 0 16px;font-size:.95rem;font-weight:700;color:#1e293b;">Agregar mensaje</h3>
    <form method="POST" action="{{ route('admin.mensajes.store') }}" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      @csrf
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Título</label>
        <input name="titulo" required maxlength="100" style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;box-sizing:border-box;">
      </div>
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Tipo</label>
        <select name="tipo" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;">
          <option value="saludo">saludo</option>
          <option value="oferta">oferta</option>
          <option value="contraoferta">contraoferta</option>
          <option value="aceptar">aceptar</option>
          <option value="rechazar">rechazar</option>
          <option value="pregunta">pregunta</option>
          <option value="respuesta">respuesta</option>
          <option value="general">general</option>
        </select>
      </div>
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Rol</label>
        <select name="rol" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;">
          <option value="emisor">Emisor (quien propone)</option>
          <option value="receptor">Receptor (dueño del artículo)</option>
          <option value="general">General (ambos)</option>
        </select>
      </div>
      <div style="grid-column:1/-1;">
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Mensaje</label>
        <textarea name="mensaje" required maxlength="500" rows="2" style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;resize:vertical;box-sizing:border-box;"></textarea>
      </div>
      <div style="grid-column:1/-1;text-align:right;">
        <button type="submit" style="background:#3b82f6;color:#fff;border:none;border-radius:6px;padding:8px 20px;font-size:.85rem;font-weight:600;cursor:pointer;">
          Guardar mensaje
        </button>
      </div>
    </form>
  </div>
  @endif

</div>

{{-- MODAL EDITAR — solo superadmin --}}
@if(auth()->user()->isSuperAdminUser())
<div id="modal-editar" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:10px;padding:24px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
    <h3 style="margin:0 0 16px;font-size:.95rem;font-weight:700;color:#1e293b;">Editar mensaje</h3>
    <form id="form-editar" method="POST" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      @csrf @method('PUT')
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Título</label>
        <input id="edit-titulo" name="titulo" required maxlength="100" style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;box-sizing:border-box;">
      </div>
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Tipo</label>
        <select id="edit-tipo" name="tipo" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;">
          <option value="saludo">saludo</option>
          <option value="oferta">oferta</option>
          <option value="contraoferta">contraoferta</option>
          <option value="aceptar">aceptar</option>
          <option value="rechazar">rechazar</option>
          <option value="pregunta">pregunta</option>
          <option value="respuesta">respuesta</option>
          <option value="general">general</option>
        </select>
      </div>
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Rol</label>
        <select id="edit-rol" name="rol" required style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;">
          <option value="emisor">Emisor</option>
          <option value="receptor">Receptor</option>
          <option value="general">General</option>
        </select>
      </div>
      <div style="grid-column:1/-1;">
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Mensaje</label>
        <textarea id="edit-mensaje" name="mensaje" required maxlength="500" rows="3" style="width:100%;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:.85rem;resize:vertical;box-sizing:border-box;"></textarea>
      </div>
      <div style="grid-column:1/-1;display:flex;justify-content:flex-end;gap:8px;">
        <button type="button" onclick="cerrarEditar()" style="border:1px solid #cbd5e1;background:#fff;border-radius:6px;padding:7px 16px;font-size:.85rem;cursor:pointer;">Cancelar</button>
        <button type="submit" style="background:#3b82f6;color:#fff;border:none;border-radius:6px;padding:7px 16px;font-size:.85rem;font-weight:600;cursor:pointer;">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
// Tabs
function filtrarRol(rol) {
  document.querySelectorAll('[id^="tab-"]').forEach(t => {
    t.style.borderBottomColor = 'transparent';
    t.style.color = '#64748b';
  });
  const tab = document.getElementById('tab-' + rol);
  if (tab) { tab.style.borderBottomColor = '#3b82f6'; tab.style.color = '#3b82f6'; }

  document.querySelectorAll('.fila-msg').forEach(tr => {
    tr.style.display = (rol === 'todos' || tr.dataset.rol === rol) ? '' : 'none';
  });
}

// Activar tab "todos" por defecto
filtrarRol('todos');

// Toggle activo
async function toggleActivo(id, btn) {
  try {
    const res = await fetch(`/admin/mensajes-predefinidos/${id}/toggle`, {
      method: 'PATCH',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    });
    const data = await res.json();
    btn.textContent = data.activo ? 'Si' : 'No';
    btn.style.background = data.activo ? '#10b981' : '#94a3b8';
    const fila = btn.closest('tr');
    if (fila) fila.style.opacity = data.activo ? '1' : '.5';
  } catch(e) { console.error(e); }
}

// Modal editar
function abrirEditar(id, titulo, mensaje, tipo, rol) {
  document.getElementById('edit-titulo').value  = titulo;
  document.getElementById('edit-mensaje').value = mensaje;
  document.getElementById('edit-tipo').value    = tipo;
  document.getElementById('edit-rol').value     = rol;
  document.getElementById('form-editar').action = `/admin/mensajes-predefinidos/${id}`;
  document.getElementById('modal-editar').style.display = 'flex';
}
function cerrarEditar() {
  document.getElementById('modal-editar').style.display = 'none';
}
document.getElementById('modal-editar').addEventListener('click', function(e) {
  if (e.target === this) cerrarEditar();
});
@endif
</script>
@endsection