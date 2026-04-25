?@extends('layouts.app')
@section('title', 'Estadisticas - Admin')
@section('content')
<div style="padding:24px;max-width:1400px;margin:0 auto;font-family:sans-serif;">

  @include('components.btn-volver', ['backUrl' => route('admin.index')])

  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
    <h1 style="font-size:1.6rem;font-weight:700;color:#1e293b;margin:0;">Estadisticas</h1>
    <span id="actualizado" style="font-size:.8rem;color:#64748b;"></span>
  </div>

  <!-- TABS -->
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <button onclick="mostrarSeccion('resumen')" class="tab-btn active" id="tab-resumen" style="padding:8px 18px;font-size:.85rem;font-weight:600;border:2px solid #f58634;border-radius:8px;background:#f58634;color:#fff;cursor:pointer;transition:all .15s;">📊 Resumen</button>
    <button onclick="mostrarSeccion('actividad')" class="tab-btn" id="tab-actividad" style="padding:8px 18px;font-size:.85rem;font-weight:600;border:2px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;cursor:pointer;transition:all .15s;">📈 Actividad</button>
    <button onclick="mostrarSeccion('operacion')" class="tab-btn" id="tab-operacion" style="padding:8px 18px;font-size:.85rem;font-weight:600;border:2px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;cursor:pointer;transition:all .15s;">🔧 Operación</button>
    <button onclick="mostrarSeccion('config')" class="tab-btn" id="tab-config" style="padding:8px 18px;font-size:.85rem;font-weight:600;border:2px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;cursor:pointer;transition:all .15s;">⚙️ Configuración</button>
  </div>
  <script>
  function mostrarSeccion(sec) {
    ['resumen','actividad','operacion','config'].forEach(function(s) {
      var panel = document.getElementById('seccion-' + s);
      var tab = document.getElementById('tab-' + s);
      if (panel) panel.style.display = (s === sec) ? 'block' : 'none';
      if (tab) {
        tab.style.background = (s === sec) ? '#f58634' : '#fff';
        tab.style.color = (s === sec) ? '#fff' : '#64748b';
        tab.style.borderColor = (s === sec) ? '#f58634' : '#e2e8f0';
      }
    });
  }
  </script>

  <!-- SECCIÓN: Resumen -->
  <div id="seccion-resumen">

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px 20px;margin-bottom:24px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
    <div>
      <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Periodo</label>
      <select id="f-periodo" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 10px;font-size:.85rem;">
        <option value="7d">Ultimos 7 dias</option>
        <option value="30d" selected>Ultimos 30 dias</option>
        <option value="90d">Ultimos 90 dias</option>
        <option value="365d">Ultimo ano</option>
        <option value="custom">Personalizado</option>
      </select>
    </div>
    <div id="custom-range" style="display:none;gap:8px;align-items:flex-end;flex-wrap:wrap;">
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Desde</label>
        <input type="date" id="f-desde" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 10px;font-size:.85rem;">
      </div>
      <div>
        <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Hasta</label>
        <input type="date" id="f-hasta" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 10px;font-size:.85rem;">
      </div>
    </div>
    <div>
      <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Estado compra</label>
      <select id="f-estatus" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 10px;font-size:.85rem;">
        <option value="">Todos</option>
        <option value="pendiente">Pendiente</option>
        <option value="aprobado">Aprobado</option>
        <option value="enviado">Enviado</option>
        <option value="entregado">Entregado</option>
        <option value="rechazado">Rechazado</option>
        <option value="cancelado">Cancelado</option>
      </select>
    </div>
    <div>
      <label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:4px;">Estado intercambio</label>
      <select id="f-negociacion" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 10px;font-size:.85rem;">
        <option value="">Todos</option>
        <option value="Inicial">Inicial</option>
        <option value="pendiente">Pendiente</option>
        <option value="contraoferta">Contraoferta</option>
        <option value="aceptado">Aceptado</option>
        <option value="completado">Completado</option>
        <option value="rechazado">Rechazado</option>
        <option value="cancelado">Cancelado</option>
      </select>
    </div>
    <button type="button" onclick="cargarDatos()" style="background:#3b82f6;color:#fff;border:none;border-radius:6px;padding:8px 18px;font-size:.85rem;cursor:pointer;font-weight:600;">Actualizar</button>
  </div>

  <div id="kpis" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;margin-bottom:28px;"></div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 20px;margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
      <div style="font-size:.8rem;font-weight:600;color:#64748b;">Mostrar / ocultar graficas</div>
      <div style="display:flex;gap:8px;">
        <button type="button" id="btn-mostrar-todos" onclick="toggleTodosGraficas(true)" style="font-size:.75rem;padding:3px 10px;border:1px solid #cbd5e1;border-radius:5px;background:#f8fafc;color:#475569;cursor:pointer;">Mostrar todos</button>
        <button type="button" id="btn-ocultar-todos" onclick="toggleTodosGraficas(false)" style="font-size:.75rem;padding:3px 10px;border:1px solid #cbd5e1;border-radius:5px;background:#f8fafc;color:#475569;cursor:pointer;">Ocultar todos</button>
      </div>
    </div>
    <div id="toggles-graficas" style="display:flex;flex-wrap:wrap;gap:10px;"></div>
  </div>

  <div id="graficas-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(480px,1fr));gap:20px;margin-bottom:28px;">
    <div id="wrap-compras-dia" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Compras por dia</h3>
        <div id="btns-compras-dia"></div>
      </div>
      <canvas id="chart-compras-dia" height="200"></canvas>
    </div>
    <div id="wrap-compras-estado" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Compras por estado</h3>
        <div id="btns-compras-estado"></div>
      </div>
      <canvas id="chart-compras-estado" height="200"></canvas>
    </div>
    <div id="wrap-monto-dia" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Monto aprobado por dia (RD$)</h3>
        <div id="btns-monto-dia"></div>
      </div>
      <canvas id="chart-monto-dia" height="200"></canvas>
    </div>
    <div id="wrap-top-cat" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Top categorias</h3>
        <div id="btns-top-cat"></div>
      </div>
      <canvas id="chart-top-cat" height="200"></canvas>
    </div>
    <div id="wrap-neg-dia" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Intercambios por dia</h3>
        <div id="btns-neg-dia"></div>
      </div>
      <canvas id="chart-neg-dia" height="200"></canvas>
    </div>
    <div id="wrap-neg-estado" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Intercambios por estado</h3>
        <div id="btns-neg-estado"></div>
      </div>
      <canvas id="chart-neg-estado" height="200"></canvas>
    </div>
    <div id="wrap-usuarios" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Usuarios nuevos por dia</h3>
        <div id="btns-usuarios"></div>
      </div>
      <canvas id="chart-usuarios" height="200"></canvas>
    </div>
    <div id="wrap-items" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:6px;">
        <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Articulos publicados por dia</h3>
        <div id="btns-items"></div>
      </div>
      <canvas id="chart-items" height="200"></canvas>
    </div>
  </div>

  </div><!-- /seccion-resumen -->

  <!-- SECCIÓN: Actividad -->
  <div id="seccion-actividad" style="display:none;">

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px;">
    <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:#1e293b;">Tasa de conversion</h3>
    <div id="bloque-conversion"></div>
  </div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px;">
    <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:#1e293b;">Tiempo promedio de cierre (dias)</h3>
    <div id="bloque-tiempo-cierre"></div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px;margin-bottom:28px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <h3 style="margin:0 0 12px;font-size:.95rem;font-weight:600;color:#1e293b;">Top vendedores</h3>
      <div id="top-vendedores"></div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <h3 style="margin:0 0 12px;font-size:.95rem;font-weight:600;color:#1e293b;">Top compradores</h3>
      <div id="top-compradores"></div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <h3 style="margin:0 0 12px;font-size:.95rem;font-weight:600;color:#1e293b;">Top intercambiadores</h3>
      <div id="top-intercambiadores"></div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(480px,1fr));gap:20px;margin-bottom:28px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <h3 style="margin:0 0 12px;font-size:.95rem;font-weight:600;color:#1e293b;">Ingresos semanales (RD$)</h3>
      <canvas id="chart-ingresos-semanal" height="200"></canvas>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;">
      <h3 style="margin:0 0 12px;font-size:.95rem;font-weight:600;color:#1e293b;">Ingresos mensuales (RD$)</h3>
      <canvas id="chart-ingresos-mensual" height="200"></canvas>
    </div>
  </div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px;">
    <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:#1e293b;">Actividad por provincia</h3>
    <canvas id="chart-provincias" height="160"></canvas>
  </div>

  </div><!-- /seccion-actividad -->

  <!-- SECCIÓN: Operación -->
  <div id="seccion-operacion" style="display:none;">

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px;">
    <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:#1e293b;">Articulos sin movimiento (+30 dias)</h3>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
        <thead>
          <tr style="background:#f8fafc;">
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">#</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Articulo</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Tipo</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Publicado</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Dias parado</th>
          </tr>
        </thead>
        <tbody id="tabla-sin-movimiento"></tbody>
      </table>
    </div>
  </div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px;">
    <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:#1e293b;">Alertas automaticas</h3>
    <div id="bloque-alertas"></div>
  </div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:28px;">
    <h3 style="margin:0 0 14px;font-size:.95rem;font-weight:600;color:#1e293b;">Trazabilidad reciente</h3>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:.82rem;">
        <thead>
          <tr style="background:#f8fafc;">
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Compra #</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Estado anterior</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Estado nuevo</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Admin</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Nota</th>
            <th style="padding:8px 10px;text-align:left;border-bottom:1px solid #e2e8f0;color:#64748b;">Fecha</th>
          </tr>
        </thead>
        <tbody id="tabla-traza"></tbody>
      </table>
    </div>
  </div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:28px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
      <h3 style="margin:0;font-size:.95rem;font-weight:600;color:#1e293b;">Analisis de costos de envio</h3>
      <button onclick="abrirModalDelivery()" style="background:#3b82f6;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:.8rem;cursor:pointer;font-weight:600;">Configurar porcentajes</button>
    </div>
    <div id="bloque-delivery"></div>
  </div>

  </div><!-- /seccion-operacion -->

  <!-- SECCIÓN: Configuración -->
  <div id="seccion-config" style="display:none;">


<div id="delivery-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:14px;padding:28px;max-width:560px;width:90%;max-height:85vh;overflow-y:auto;position:relative;">
    <button onclick="cerrarModalDelivery()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:1.4rem;cursor:pointer;color:#64748b;">&times;</button>
    <div style="font-size:1.1rem;font-weight:700;color:#1e293b;margin-bottom:6px;">Configurar porcentajes de envio</div>
    <div style="font-size:.82rem;color:#64748b;margin-bottom:18px;">Estos porcentajes se aplican sobre el valor del articulo para calcular el costo de envio.</div>
    <div id="delivery-form-fields"></div>
    <div style="display:flex;gap:10px;margin-top:20px;">
      <button onclick="guardarConfigDelivery()" style="background:#10b981;color:#fff;border:none;border-radius:6px;padding:8px 20px;font-size:.85rem;cursor:pointer;font-weight:600;">Guardar</button>
      <button onclick="cerrarModalDelivery()" style="background:#f1f5f9;color:#475569;border:none;border-radius:6px;padding:8px 20px;font-size:.85rem;cursor:pointer;">Cancelar</button>
    </div>
    <div id="delivery-save-msg" style="margin-top:10px;font-size:.82rem;"></div>
  </div>
</div>
<div id="kpi-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:14px;padding:28px;max-width:680px;width:90%;max-height:85vh;overflow-y:auto;position:relative;">
    <button onclick="cerrarModal()" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:1.4rem;cursor:pointer;color:#64748b;">&times;</button>
    <div id="modal-titulo" style="font-size:1.1rem;font-weight:700;color:#1e293b;margin-bottom:6px;"></div>
    <div id="modal-subtitulo" style="font-size:.82rem;color:#64748b;margin-bottom:18px;"></div>
    <div id="modal-kpis-mini" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:18px;"></div>
    <canvas id="modal-chart" height="220"></canvas>
  </div>
</div>

<style>
.ct-btn{padding:3px 9px;font-size:.72rem;border:1px solid #cbd5e1;border-radius:4px;background:#f8fafc;color:#475569;cursor:pointer;font-weight:500;}
.ct-btn.active{background:#3b82f6;color:#fff;border-color:#3b82f6;}
.toggle-check{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border:1px solid #e2e8f0;border-radius:6px;cursor:pointer;font-size:.78rem;color:#475569;background:#f8fafc;user-select:none;}
.toggle-check input{cursor:pointer;width:14px;height:14px;}
.toggle-check.off{opacity:.45;text-decoration:line-through;}
.kpi-card{cursor:pointer;transition:transform .15s,box-shadow .15s;}
.kpi-card:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(0,0,0,.1);}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const DATA_URL = '/admin/estadisticas/data';
const PALETTE  = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#ec4899'];
const charts   = {};
const TIPOS = {
  'compras-dia':['line','bar','area'],
  'compras-estado':['bar','doughnut','pie','polarArea'],
  'monto-dia':['line','bar','area'],
  'top-cat':['bar','doughnut','pie','polarArea'],
  'neg-dia':['line','bar','area'],
  'neg-estado':['bar','doughnut','pie','polarArea'],
  'usuarios':['line','bar','area'],
  'items':['line','bar','area']
};
const LABELS = {
  'compras-dia':'Compras por dia','compras-estado':'Compras por estado',
  'monto-dia':'Monto aprobado','top-cat':'Top categorias',
  'neg-dia':'Intercambios por dia','neg-estado':'Intercambios por estado',
  'usuarios':'Usuarios nuevos','items':'Articulos publicados'
};
const tipoActual = {};
const visible    = {};
Object.keys(TIPOS).forEach(k => { tipoActual[k] = TIPOS[k][0]; visible[k] = true; });
let datosCache = null;
let modalChart = null;
const fmt  = n => Number(n||0).toLocaleString('es-DO');
const fmtM = n => 'RD$ ' + Number(n||0).toLocaleString('es-DO',{minimumFractionDigits:2,maximumFractionDigits:2});
const labelTipo = t => ({line:'Linea',bar:'Barras',area:'Area',doughnut:'Dona',pie:'Pastel',polarArea:'Polar'}[t]||t);

function renderKPIs(kpis) {
  const items = [
    {key:'compras',    label:'Total compras',   val:fmt(kpis.total_compras),        color:'#3b82f6',icono:'&#128722;',sub:'Todas las ordenes en el periodo'},
    {key:'aprobadas',  label:'Aprobadas',        val:fmt(kpis.compras_aprobadas),    color:'#10b981',icono:'&#10003;',sub:'Ordenes con pago confirmado'},
    {key:'pendientes', label:'Pendientes',        val:fmt(kpis.compras_pendientes),  color:'#f59e0b',icono:'&#9203;',sub:'Ordenes esperando confirmacion'},
    {key:'monto',      label:'Monto aprobado',   val:fmtM(kpis.monto_total),         color:'#10b981',icono:'&#128176;',sub:'Total facturado en compras aprobadas'},
    {key:'intercambios',label:'Intercambios',    val:fmt(kpis.total_intercambios),   color:'#8b5cf6',icono:'&#128257;',sub:'Negociaciones iniciadas en el periodo'},
    {key:'activos',    label:'Activos',           val:fmt(kpis.intercambios_activos), color:'#06b6d4',icono:'&#128336;',sub:'Negociaciones en curso'},
    {key:'usuarios',   label:'Usuarios nuevos',  val:fmt(kpis.usuarios_nuevos),      color:'#f97316',icono:'&#128100;',sub:'Registros en el periodo'},
    {key:'items',      label:'Arts. publicados', val:fmt(kpis.items_publicados),     color:'#ec4899',icono:'&#128230;',sub:'Articulos creados en el periodo'},
  ];
  document.getElementById('kpis').innerHTML = items.map(i =>
    '<div class="kpi-card" onclick="abrirModalKPI(\''+i.key+'\')" style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;border-top:3px solid '+i.color+';">' +
    '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;"><span style="font-size:1.1rem;">'+i.icono+'</span><span style="font-size:.72rem;color:#64748b;">'+i.label+'</span></div>' +
    '<div style="font-size:1.25rem;font-weight:700;color:#1e293b;">'+i.val+'</div>' +
    '<div style="font-size:.68rem;color:#94a3b8;margin-top:3px;">'+i.sub+'</div></div>'
  ).join('');
}

function abrirModalKPI(key) {
  if (!datosCache) return;
  const d = datosCache;
  document.getElementById('kpi-modal').style.display = 'flex';
  if (modalChart) { modalChart.destroy(); modalChart = null; }
  const cfg = {
    compras:{titulo:'Total de compras',sub:'Evolucion diaria de todas las ordenes',
      minis:[{l:'Total',v:fmt(d.kpis.total_compras),c:'#3b82f6'},{l:'Aprobadas',v:fmt(d.kpis.compras_aprobadas),c:'#10b981'},{l:'Pendientes',v:fmt(d.kpis.compras_pendientes),c:'#f59e0b'},{l:'Entregadas',v:fmt(d.kpis.compras_entregadas),c:'#06b6d4'}],
      labels:(d.compras_por_dia||[]).map(x=>x.dia),values:(d.compras_por_dia||[]).map(x=>x.total),label:'Compras',color:'#3b82f6'},
    aprobadas:{titulo:'Compras aprobadas',sub:'Distribucion por estado',
      minis:[{l:'Aprobadas',v:fmt(d.kpis.compras_aprobadas),c:'#10b981'},{l:'Monto',v:fmtM(d.kpis.monto_total),c:'#10b981'},{l:'Entregadas',v:fmt(d.kpis.compras_entregadas),c:'#06b6d4'}],
      labels:(d.compras_por_estado||[]).map(x=>x.estatus),values:(d.compras_por_estado||[]).map(x=>x.total),label:'Compras',color:'#10b981',tipo:'doughnut'},
    pendientes:{titulo:'Compras pendientes',sub:'Ordenes sin procesar',
      minis:[{l:'Pendientes',v:fmt(d.kpis.compras_pendientes),c:'#f59e0b'},{l:'Total',v:fmt(d.kpis.total_compras),c:'#3b82f6'},{l:'% Pend.',v:d.kpis.total_compras>0?Math.round(d.kpis.compras_pendientes/d.kpis.total_compras*100)+'%':'0%',c:'#f59e0b'}],
      labels:(d.compras_por_dia||[]).map(x=>x.dia),values:(d.compras_por_dia||[]).map(x=>x.total),label:'Compras',color:'#f59e0b'},
    monto:{titulo:'Monto aprobado (RD$)',sub:'Ingresos diarios de compras confirmadas',
      minis:[{l:'Total',v:fmtM(d.kpis.monto_total),c:'#10b981'},{l:'Aprobadas',v:fmt(d.kpis.compras_aprobadas),c:'#3b82f6'},{l:'Promedio',v:d.kpis.compras_aprobadas>0?fmtM(d.kpis.monto_total/d.kpis.compras_aprobadas):'RD$ 0',c:'#8b5cf6'}],
      labels:(d.monto_por_dia||[]).map(x=>x.dia),values:(d.monto_por_dia||[]).map(x=>parseFloat(x.monto||0)),label:'Monto RD$',color:'#10b981'},
    intercambios:{titulo:'Intercambios / Negociaciones',sub:'Evolucion diaria de negociaciones',
      minis:[{l:'Total',v:fmt(d.kpis.total_intercambios),c:'#8b5cf6'},{l:'Activos',v:fmt(d.kpis.intercambios_activos),c:'#06b6d4'},{l:'Completados',v:fmt(d.kpis.intercambios_completados),c:'#10b981'}],
      labels:(d.intercambios_por_dia||[]).map(x=>x.dia),values:(d.intercambios_por_dia||[]).map(x=>x.total),label:'Intercambios',color:'#8b5cf6'},
    activos:{titulo:'Intercambios activos',sub:'Negociaciones en curso por estado',
      minis:[{l:'Activos',v:fmt(d.kpis.intercambios_activos),c:'#06b6d4'},{l:'Total',v:fmt(d.kpis.total_intercambios),c:'#8b5cf6'}],
      labels:(d.intercambios_por_estado||[]).map(x=>x.estado),values:(d.intercambios_por_estado||[]).map(x=>x.total),label:'Intercambios',color:'#06b6d4',tipo:'doughnut'},
    usuarios:{titulo:'Usuarios nuevos',sub:'Registros diarios en el periodo',
      minis:[{l:'Nuevos',v:fmt(d.kpis.usuarios_nuevos),c:'#f97316'}],
      labels:(d.usuarios_nuevos||[]).map(x=>x.dia),values:(d.usuarios_nuevos||[]).map(x=>x.total),label:'Usuarios',color:'#f97316'},
    items:{titulo:'Articulos publicados',sub:'Publicaciones creadas por dia',
      minis:[{l:'Publicados',v:fmt(d.kpis.items_publicados),c:'#ec4899'}],
      labels:(d.items_publicados||[]).map(x=>x.dia),values:(d.items_publicados||[]).map(x=>x.total),label:'Articulos',color:'#ec4899'},
  };
  const c = cfg[key]; if (!c) return;
  document.getElementById('modal-titulo').textContent    = c.titulo;
  document.getElementById('modal-subtitulo').textContent = c.sub;
  document.getElementById('modal-kpis-mini').innerHTML   = (c.minis||[]).map(m =>
    '<div style="background:#f8fafc;border-radius:8px;padding:10px 12px;border-left:3px solid '+m.c+';">' +
    '<div style="font-size:.68rem;color:#64748b;">'+m.l+'</div>' +
    '<div style="font-size:1rem;font-weight:700;color:#1e293b;">'+m.v+'</div></div>'
  ).join('');
  const isPie = ['doughnut','pie','polarArea'].includes(c.tipo||'line');
  const ctx   = document.getElementById('modal-chart').getContext('2d');
  modalChart  = new Chart(ctx, {
    type: c.tipo || 'line',
    data: {labels:c.labels,datasets:[isPie?{data:c.values,backgroundColor:PALETTE}:{label:c.label,data:c.values,backgroundColor:c.color+'33',borderColor:c.color,borderWidth:2,fill:true,tension:.35,pointRadius:c.labels.length>30?0:3}]},
    options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:isPie}},scales:isPie?{}:{x:{ticks:{font:{size:10},maxRotation:45}},y:{beginAtZero:true,ticks:{font:{size:10}}}}}
  });
}
function cerrarModal() {
  document.getElementById('kpi-modal').style.display = 'none';
  if (modalChart) { modalChart.destroy(); modalChart = null; }
}
document.getElementById('kpi-modal').addEventListener('click', function(e){ if(e.target===this) cerrarModal(); });

function crearToggles() {
  const cont = document.getElementById('toggles-graficas');
  cont.innerHTML = '';
  Object.keys(TIPOS).forEach(key => {
    const lbl = document.createElement('label');
    lbl.className = 'toggle-check' + (visible[key] ? '' : ' off');
    lbl.setAttribute('data-key', key);
    lbl.innerHTML = '<input type="checkbox"' + (visible[key] ? ' checked' : '') + '> ' + LABELS[key];
    lbl.querySelector('input').addEventListener('change', function(){
      visible[key] = this.checked;
      lbl.className = 'toggle-check' + (visible[key] ? '' : ' off');
      const wrap = document.getElementById('wrap-' + key);
      if (wrap) wrap.style.display = visible[key] ? '' : 'none';
    });
    cont.appendChild(lbl);
  });
}

function toggleTodosGraficas(mostrar) {
  Object.keys(TIPOS).forEach(key => {
    visible[key] = mostrar;
    const wrap = document.getElementById('wrap-' + key);
    if (wrap) wrap.style.display = mostrar ? '' : 'none';
    const lbl = document.querySelector('#toggles-graficas [data-key="' + key + '"]');
    if (lbl) {
      lbl.className = 'toggle-check' + (mostrar ? '' : ' off');
      const cb = lbl.querySelector('input');
      if (cb) cb.checked = mostrar;
    }
  });
}
function crearBotones(key) {
  const cont = document.getElementById('btns-' + key);
  if (!cont) return;
  cont.innerHTML = '';
  TIPOS[key].forEach(tipo => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'ct-btn' + (tipoActual[key] === tipo ? ' active' : '');
    btn.textContent = labelTipo(tipo);
    btn.onclick = () => { tipoActual[key] = tipo; crearBotones(key); if (datosCache) renderGrafica(key, datosCache); };
    cont.appendChild(btn);
  });
}

function renderGrafica(key, datos) {
  const canvas = document.getElementById('chart-' + key);
  if (!canvas) return;
  if (charts[key]) { charts[key].destroy(); delete charts[key]; }
  const tipo   = tipoActual[key];
  const isPie  = ['doughnut','pie','polarArea'].includes(tipo);
  const isArea = tipo === 'area';
  const chartType = isArea ? 'line' : tipo;
  let labels = [], values = [], label = '';
  if      (key==='compras-dia')    { const r=datos.compras_por_dia||[];     labels=r.map(x=>x.dia);       values=r.map(x=>x.total);               label='Compras'; }
  else if (key==='compras-estado') { const r=datos.compras_por_estado||[];  labels=r.map(x=>x.estatus);   values=r.map(x=>x.total);               label='Compras'; }
  else if (key==='monto-dia')      { const r=datos.monto_por_dia||[];       labels=r.map(x=>x.dia);       values=r.map(x=>parseFloat(x.monto||0)); label='Monto RD$'; }
  else if (key==='top-cat')        { const r=datos.top_categorias||[];      labels=r.map(x=>x.categoria); values=r.map(x=>x.total);               label='Ventas'; }
  else if (key==='neg-dia')        { const r=datos.intercambios_por_dia||[];labels=r.map(x=>x.dia);       values=r.map(x=>x.total);               label='Intercambios'; }
  else if (key==='neg-estado')     { const r=datos.intercambios_por_estado||[];labels=r.map(x=>x.estado); values=r.map(x=>x.total);               label='Intercambios'; }
  else if (key==='usuarios')       { const r=datos.usuarios_nuevos||[];     labels=r.map(x=>x.dia);       values=r.map(x=>x.total);               label='Usuarios'; }
  else if (key==='items')          { const r=datos.items_publicados||[];    labels=r.map(x=>x.dia);       values=r.map(x=>x.total);               label='Articulos'; }
  const color   = PALETTE[Object.keys(TIPOS).indexOf(key) % PALETTE.length];
  const dataset = isPie
    ? {data:values,backgroundColor:PALETTE}
    : {label,data:values,backgroundColor:isArea?color+'33':color,borderColor:color,borderWidth:2,fill:isArea,tension:.35,pointRadius:labels.length>30?0:3};
  charts[key] = new Chart(canvas, {
    type: chartType,
    data: {labels,datasets:[dataset]},
    options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:isPie}},scales:isPie?{}:{x:{ticks:{font:{size:10},maxRotation:45}},y:{beginAtZero:true,ticks:{font:{size:10}}}}}
  });
}

function renderTrazabilidad(rows) {
  const tbody = document.getElementById('tabla-traza');
  if (!rows||!rows.length){ tbody.innerHTML='<tr><td colspan="6" style="padding:16px;text-align:center;color:#94a3b8;">Sin registros</td></tr>'; return; }
  const badge = e => { const c={aprobado:'#10b981',pendiente:'#f59e0b',rechazado:'#ef4444',enviado:'#3b82f6',entregado:'#06b6d4',cancelado:'#94a3b8'}[e]||'#94a3b8'; return '<span style="background:'+c+'22;color:'+c+';padding:2px 8px;border-radius:12px;font-size:.75rem;font-weight:600;">'+( e||'- ')+'</span>'; };
  tbody.innerHTML = rows.map(r =>
    '<tr style="border-bottom:1px solid #f1f5f9;">' +
    '<td style="padding:8px 10px;">#'+r.id_pago_compra+'</td>' +
    '<td style="padding:8px 10px;">'+badge(r.estado_anterior)+'</td>' +
    '<td style="padding:8px 10px;">'+badge(r.estado_nuevo)+'</td>' +
    '<td style="padding:8px 10px;">'+( r.admin&&r.admin.nombres?r.admin.nombres:'Sistema')+'</td>' +
    '<td style="padding:8px 10px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+( r.nota||'-')+'</td>' +
    '<td style="padding:8px 10px;white-space:nowrap;">'+( r.created_at?r.created_at.substring(0,16).replace('T',' '):'-')+'</td></tr>'
  ).join('');
}

function renderAlertas(alertas) {
  const colores = {danger:'#ef4444',warning:'#f59e0b',info:'#3b82f6',success:'#10b981'};
  document.getElementById('bloque-alertas').innerHTML = (alertas||[]).map(a => {
    const c = colores[a.tipo]||'#64748b';
    return '<div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:8px;background:'+c+'11;border-left:3px solid '+c+';margin-bottom:8px;">' +
      '<span style="font-size:1.2rem;">'+a.icono+'</span>' +
      '<div><div style="font-weight:600;font-size:.85rem;color:'+c+';">'+a.titulo+'</div>' +
      '<div style="font-size:.8rem;color:#475569;margin-top:2px;">'+a.mensaje+'</div></div></div>';
  }).join('');
}

function renderConversion(tc) {
  if (!tc) return;
  const barra = (pct, color) =>
    '<div style="background:#f1f5f9;border-radius:6px;height:10px;overflow:hidden;margin-top:4px;">' +
    '<div style="width:'+Math.min(pct,100)+'%;height:100%;background:'+color+';border-radius:6px;transition:width .5s;"></div></div>';
  document.getElementById('bloque-conversion').innerHTML =
    '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">' +
    '<div style="padding:14px;background:#f8fafc;border-radius:8px;">' +
    '<div style="font-size:.8rem;color:#64748b;margin-bottom:2px;">Compras: intenciones &rarr; pagadas</div>' +
    '<div style="font-size:1.4rem;font-weight:700;color:#10b981;">'+tc.compra+'%</div>' +
    barra(tc.compra,'#10b981') +
    '<div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">'+fmt(tc.pagadas)+' de '+fmt(tc.total_intenc)+' intenciones</div></div>' +
    '<div style="padding:14px;background:#f8fafc;border-radius:8px;">' +
    '<div style="font-size:.8rem;color:#64748b;margin-bottom:2px;">Intercambios: iniciados &rarr; completados</div>' +
    '<div style="font-size:1.4rem;font-weight:700;color:#8b5cf6;">'+tc.negociacion+'%</div>' +
    barra(tc.negociacion,'#8b5cf6') +
    '<div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">'+fmt(tc.negs_cerradas)+' de '+fmt(tc.total_negs)+' negociaciones</div></div></div>';
}

function renderTiempoCierre(rows) {
  const colores = {aceptado:'#10b981',completado:'#3b82f6',rechazado:'#ef4444',cancelado:'#94a3b8'};
  document.getElementById('bloque-tiempo-cierre').innerHTML = (rows||[]).length
    ? '<div style="display:flex;flex-wrap:wrap;gap:12px;">' +
      rows.map(r => {
        const c = colores[r.estado]||'#64748b';
        return '<div style="padding:12px 16px;background:#f8fafc;border-radius:8px;border-left:3px solid '+c+';min-width:160px;">' +
          '<div style="font-size:.72rem;color:#64748b;text-transform:capitalize;">'+r.estado+'</div>' +
          '<div style="font-size:1.3rem;font-weight:700;color:'+c+';"> '+Math.round(r.promedio_dias||0)+' dias</div>' +
          '<div style="font-size:.7rem;color:#94a3b8;">'+fmt(r.total)+' negociaciones</div></div>';
      }).join('') + '</div>'
    : '<p style="color:#94a3b8;font-size:.85rem;">Sin datos en el periodo.</p>';
}

function renderTopUsuarios(vendedores, compradores, intercambiadores) {
  const lista = (rows, metrica, color) => {
    if (!rows||!rows.length) return '<p style="color:#94a3b8;font-size:.82rem;">Sin datos.</p>';
    return rows.map((r,i) =>
      '<div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid #f1f5f9;">' +
      '<span style="font-size:.75rem;font-weight:700;color:'+color+';min-width:20px;">#'+(i+1)+'</span>' +
      '<div style="flex:1;min-width:0;">' +
      '<div style="font-size:.82rem;font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+( r.nombre||r.nombre_usuario||'-')+'</div>' +
      '<div style="font-size:.7rem;color:#94a3b8;">@'+(r.nombre_usuario||'')+'</div></div>' +
      '<div style="font-size:.82rem;font-weight:700;color:'+color+';white-space:nowrap;">'+fmt(r[metrica])+(metrica==='monto'?' RD$':'')+'</div></div>'
    ).join('');
  };
  document.getElementById('top-vendedores').innerHTML       = lista(vendedores,      'ventas',       '#10b981');
  document.getElementById('top-compradores').innerHTML      = lista(compradores,     'compras',      '#3b82f6');
  document.getElementById('top-intercambiadores').innerHTML = lista(intercambiadores,'intercambios', '#8b5cf6');
}

function renderItemsSinMovimiento(items) {
  const tbody = document.getElementById('tabla-sin-movimiento');
  if (!items||!items.length){ tbody.innerHTML='<tr><td colspan="5" style="padding:16px;text-align:center;color:#94a3b8;">No hay articulos parados.</td></tr>'; return; }
  tbody.innerHTML = items.map(i =>
    '<tr style="border-bottom:1px solid #f1f5f9;">' +
    '<td style="padding:8px 10px;">#'+i.id+'</td>' +
    '<td style="padding:8px 10px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+i.nombre+'</td>' +
    '<td style="padding:8px 10px;"><span style="background:#f1f5f9;padding:2px 8px;border-radius:10px;font-size:.75rem;">'+i.tipo+'</span></td>' +
    '<td style="padding:8px 10px;white-space:nowrap;">'+( i.fecha?i.fecha.substring(0,10):'-')+'</td>' +
    '<td style="padding:8px 10px;"><span style="color:#ef4444;font-weight:600;">'+Math.abs(Math.round(i.dias||0))+' dias</span></td></tr>'
  ).join('');
}

function renderIngresos(semanal, mensual) {
  const buildChart = (id, rows) => {
    const canvas = document.getElementById(id);
    if (!canvas) return;
    if (charts[id]) { charts[id].destroy(); delete charts[id]; }
    if (!rows||!rows.length) return;
    charts[id] = new Chart(canvas, {
      type:'bar',
      data:{labels:rows.map(r=>r.periodo),datasets:[{label:'Monto RD$',data:rows.map(r=>r.monto),backgroundColor:'#3b82f633',borderColor:'#3b82f6',borderWidth:2}]},
      options:{responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{x:{ticks:{font:{size:10},maxRotation:45}},y:{beginAtZero:true,ticks:{font:{size:10}}}}}
    });
  };
  buildChart('chart-ingresos-semanal', semanal);
  buildChart('chart-ingresos-mensual', mensual);
}

function renderProvincias(rows) {
  const canvas = document.getElementById('chart-provincias');
  if (!canvas) return;
  if (charts['provincias']) { charts['provincias'].destroy(); delete charts['provincias']; }
  if (!rows||!rows.length) return;
  const top = rows.slice(0,15);
  charts['provincias'] = new Chart(canvas, {
    type:'bar',
    data:{labels:top.map(r=>r.provincia),datasets:[{label:'Usuarios activos',data:top.map(r=>r.usuarios),backgroundColor:PALETTE[0]+'88',borderColor:PALETTE[0],borderWidth:2}]},
    options:{indexAxis:'y',responsive:true,maintainAspectRatio:true,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{font:{size:10}}},y:{ticks:{font:{size:10}}}}}
  });
}

function renderDelivery(zonas, config) {
  const el = document.getElementById('bloque-delivery');
  if (!el) return;
  if (!zonas || !zonas.length) { el.innerHTML = '<p style="color:#94a3b8;font-size:.85rem;">Sin datos de zonas.</p>'; return; }
  const colores = {corta:'#10b981', larga:'#3b82f6', especial:'#f59e0b'};
  el.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;">' +
    zonas.map(z => {
      const c = colores[z.tipo] || '#64748b';
      const nombre = z.zona || z.nombre || z.tipo;
      const base   = z.precio_base || z.costo_base || 0;
      const costo  = z.costo_estimado || base;
      return '<div style="padding:14px;background:#f8fafc;border-radius:8px;border-left:3px solid '+c+';">' +
        '<div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">'+z.tipo+'</div>' +
        '<div style="font-size:.9rem;font-weight:700;color:#1e293b;margin:4px 0;">'+nombre+'</div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:.78rem;margin:8px 0;">' +
          '<div style="background:#fff;border-radius:6px;padding:6px 8px;">' +
            '<div style="color:#94a3b8;font-size:.68rem;">Base proveedor</div>' +
            '<div style="font-weight:600;color:#1e293b;">RD$ '+Number(base).toLocaleString('es-DO',{minimumFractionDigits:2})+'</div>' +
          '</div>' +
          '<div style="background:'+c+'11;border-radius:6px;padding:6px 8px;border:1px solid '+c+'33;">' +
            '<div style="color:#94a3b8;font-size:.68rem;">Costo estimado</div>' +
            '<div style="font-weight:700;color:'+c+';">RD$ '+Number(costo).toLocaleString('es-DO',{minimumFractionDigits:2})+'</div>' +
          '</div>' +
        '</div>' +
        '<div style="font-size:.7rem;color:#94a3b8;margin-top:4px;">' +
          'Ganancia '+(z.pct_ganancia||0)+'% &middot; Plataforma '+(z.pct_plataforma||0)+'% &middot; Manejo '+(z.pct_manejo||0)+'% &middot; Seguro '+(z.pct_seguro||0)+'%' +
        '</div>' +
        '<div style="font-size:.7rem;color:#94a3b8;margin-top:2px;">'+(z.dias_entrega||'')+'</div></div>';
    }).join('') + '</div>';
}let deliveryConfigCache = [];
function abrirModalDelivery() {
  document.getElementById('delivery-modal').style.display = 'flex';
  document.getElementById('delivery-save-msg').textContent = '';
  const fields = document.getElementById('delivery-form-fields');
  const cfg = datosCache && datosCache.delivery_config ? datosCache.delivery_config : [];
  if (!cfg.length) { fields.innerHTML = '<p style="color:#94a3b8;">Sin datos de configuracion.</p>'; return; }
  deliveryConfigCache = cfg;
  // cfg is array of {clave, porcentaje, porcentaje_plataforma, porcentaje_seguro, porcentaje_manejo, descripcion}
  const fieldLabels = {
    porcentaje:'Ganancia negocio (%)',
    porcentaje_plataforma:'Plataforma (%)',
    porcentaje_seguro:'Seguro (%)',
    porcentaje_manejo:'Manejo (%)'
  };
  const claveLabel = {cortas:'Rutas cortas', largas:'Rutas largas', especiales:'Rutas especiales'};
  fields.innerHTML = cfg.map(row => {
    const titulo = claveLabel[row.clave] || row.clave;
    const inputs = Object.entries(fieldLabels).map(([f,l]) =>
      '<div style="margin-bottom:10px;">' +
      '<label style="font-size:.75rem;color:#64748b;display:block;margin-bottom:3px;">'+l+'</label>' +
      '<input type="number" step="0.01" min="0" max="100" id="dc-'+row.clave+'-'+f+'" value="'+(row[f]||0)+'" style="border:1px solid #cbd5e1;border-radius:6px;padding:5px 9px;font-size:.83rem;width:100%;box-sizing:border-box;">' +
      '</div>'
    ).join('');
    return '<div style="margin-bottom:18px;padding:14px;background:#f8fafc;border-radius:8px;border-left:3px solid #3b82f6;">' +
      '<div style="font-size:.85rem;font-weight:700;color:#1e293b;margin-bottom:10px;">'+titulo+'</div>' +
      inputs + '</div>';
  }).join('');
}
function cerrarModalDelivery() { document.getElementById('delivery-modal').style.display = 'none'; }
async function guardarConfigDelivery() {
  const msg = document.getElementById('delivery-save-msg');
  msg.textContent = 'Guardando...'; msg.style.color = '#64748b';
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const fields = ['porcentaje','porcentaje_plataforma','porcentaje_seguro','porcentaje_manejo'];
    for (const row of deliveryConfigCache) {
      const body = {};
      fields.forEach(f => {
        const el = document.getElementById('dc-'+row.clave+'-'+f);
        if (el) body[f] = parseFloat(el.value);
      });
      await fetch('/admin/estadisticas/delivery-config/'+row.clave, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
        body: JSON.stringify(body)
      });
    }
    msg.textContent = 'Guardado correctamente'; msg.style.color = '#10b981';
    await cargarDatos();
    setTimeout(() => cerrarModalDelivery(), 800);
  } catch(e) { msg.textContent = 'Error al guardar'; msg.style.color = '#ef4444'; }
}
async function cargarDatos() {
  const periodo = document.getElementById('f-periodo').value;
  const params  = new URLSearchParams({
    periodo,
    estatus_compra:     document.getElementById('f-estatus').value,
    estado_intercambio: document.getElementById('f-negociacion').value,
  });
  if (periodo === 'custom') {
    params.set('fecha_desde', document.getElementById('f-desde').value);
    params.set('fecha_hasta', document.getElementById('f-hasta').value);
  }
  try {
    const res = await fetch(DATA_URL + '?' + params.toString(), {
      headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    });
    if (!res.ok) { console.error('HTTP', res.status, await res.text()); return; }
    const datos = await res.json();
    datosCache  = datos;
    renderKPIs(datos.kpis || {});
    renderTrazabilidad(datos.trazabilidad || []);
    renderAlertas(datos.alertas || []);
    renderConversion(datos.tasa_conversion || null);
    renderTiempoCierre(datos.tiempo_cierre || []);
    renderTopUsuarios(datos.top_vendedores||[], datos.top_compradores||[], datos.top_intercambiadores||[]);
    renderItemsSinMovimiento(datos.items_sin_movimiento || []);
    renderIngresos(datos.ingresos_semanal||[], datos.ingresos_mensual||[]);
    renderProvincias(datos.actividad_provincia || []);
    renderDelivery(datos.delivery_zonas || [], datos.delivery_config || []);
    Object.keys(TIPOS).forEach(k => renderGrafica(k, datos));
    const el = document.getElementById('actualizado');
    if (el) el.textContent = 'Actualizado: ' + (datos.actualizado_en || '');
  } catch(e) { console.error('Error cargando estadisticas:', e); }
}

document.addEventListener('DOMContentLoaded', () => {
  crearToggles();
  Object.keys(TIPOS).forEach(k => crearBotones(k));
  document.getElementById('f-periodo').addEventListener('change', function(){
    document.getElementById('custom-range').style.display = this.value === 'custom' ? 'flex' : 'none';
  });
  cargarDatos();
});
</script>

  {{-- 
       Cuentas Bancarias (informativas)
        --}}
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:20px 24px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
      <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0;">Cuentas Bancarias de la Empresa</h2>
      <button onclick="abrirModalCuenta(null)" style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:8px 16px;font-size:.85rem;cursor:pointer;font-weight:600;">+ Nueva cuenta</button>
    </div>
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
        <thead>
          <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
            <th style="padding:10px 12px;text-align:left;color:#64748b;font-weight:600;">Banco</th>
            <th style="padding:10px 12px;text-align:left;color:#64748b;font-weight:600;">Número</th>
            <th style="padding:10px 12px;text-align:left;color:#64748b;font-weight:600;">Tipo</th>
            <th style="padding:10px 12px;text-align:left;color:#64748b;font-weight:600;">Titular</th>
            <th style="padding:10px 12px;text-align:left;color:#64748b;font-weight:600;">Estado</th>
            <th style="padding:10px 12px;text-align:left;color:#64748b;font-weight:600;">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbodyCuentas">
          @forelse($cuentasBanco as $cuenta)
          <tr style="border-bottom:1px solid #f1f5f9;">
            <td style="padding:10px 12px;">{{ $cuenta->banco }}</td>
            <td style="padding:10px 12px;">{{ $cuenta->numero_cuenta }}</td>
            <td style="padding:10px 12px;">{{ $cuenta->tipo_cuenta }}</td>
            <td style="padding:10px 12px;">{{ $cuenta->titular }}</td>
            <td style="padding:10px 12px;">
              <span style="padding:2px 8px;border-radius:9999px;font-size:.75rem;font-weight:600;background:{{ $cuenta->activo ? '#d1fae5' : '#fee2e2' }};color:{{ $cuenta->activo ? '#065f46' : '#991b1b' }};">
                {{ $cuenta->activo ? 'Activa' : 'Inactiva' }}
              </span>
            </td>
            <td style="padding:10px 12px;">
              <div style="display:flex;gap:6px;">
                <button onclick="editarCuenta({{ json_encode($cuenta) }})" style="font-size:.75rem;padding:4px 10px;border:1px solid #3b82f6;border-radius:5px;background:#eff6ff;color:#1d4ed8;cursor:pointer;">Editar</button>
                <button onclick="toggleCuenta({{ $cuenta->id }})" style="font-size:.75rem;padding:4px 10px;border:1px solid #f59e0b;border-radius:5px;background:#fffbeb;color:#92400e;cursor:pointer;">{{ $cuenta->activo ? 'Desactivar' : 'Activar' }}</button>
                <button onclick="eliminarCuenta({{ $cuenta->id }})" style="font-size:.75rem;padding:4px 10px;border:1px solid #ef4444;border-radius:5px;background:#fef2f2;color:#dc2626;cursor:pointer;">Eliminar</button>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:16px;">Sin cuentas registradas</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Modal Cuenta Bancaria --}}
  <div id="modalCuenta" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:28px;width:100%;max-width:480px;margin:16px;">
      <h3 id="modalCuentaTitulo" style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0 0 20px;">Nueva cuenta</h3>
      <input type="hidden" id="cuentaId">
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Banco *</label>
        <input id="cuentaBanco" type="text" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;">
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Número de cuenta *</label>
        <input id="cuentaNumero" type="text" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;">
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Tipo de cuenta *</label>
        <select id="cuentaTipo" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;">
          <option value="ahorro">Ahorro</option>
          <option value="corriente">Corriente</option>
          <option value="otro">Otro</option>
        </select>
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Titular *</label>
        <input id="cuentaTitular" type="text" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Descripción</label>
        <textarea id="cuentaDescripcion" rows="2" style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;resize:vertical;"></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button onclick="cerrarModalCuenta()" style="padding:8px 18px;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#374151;cursor:pointer;font-size:.9rem;">Cancelar</button>
        <button onclick="guardarCuenta()" style="padding:8px 18px;border:none;border-radius:6px;background:#2563eb;color:#fff;cursor:pointer;font-size:.9rem;font-weight:600;">Guardar</button>
      </div>
    </div>
  </div>

  {{-- 
       Config Tarifa Categoría 29
        --}}
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:20px 24px;margin-bottom:24px;">
    <h2 style="font-size:1.1rem;font-weight:700;color:#1e293b;margin:0 0 16px;">Configuración de Tarifas  Categoría 29</h2>
    <div id="cfgMsgOk" style="display:none;background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:10px 14px;color:#065f46;font-size:.875rem;margin-bottom:14px;">Configuración guardada correctamente.</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:20px;">
      <div>
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Monto de registro (RD$)</label>
        <input id="cfgMonto" type="number" min="0" step="0.01" value="{{ $configTarifa->monto_registro ?? 0 }}"
               style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;">
      </div>
      <div>
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Descuento venta masiva (%)</label>
        <input id="cfgDescuento" type="number" min="0" max="100" step="0.01" value="{{ $configTarifa->descuento_venta_masiva ?? 0 }}"
               style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;">
      </div>
      <div>
        <label style="display:block;font-size:.85rem;font-weight:600;color:#374151;margin-bottom:4px;">Cantidad mínima para descuento</label>
        <input id="cfgCantidad" type="number" min="1" step="1" value="{{ $configTarifa->cantidad_minima_descuento ?? 1 }}"
               style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:8px 12px;font-size:.9rem;box-sizing:border-box;">
      </div>
    </div>
    <p style="font-size:.8rem;color:#64748b;margin:0 0 14px;">
      Ejemplo: si el monto es RD$ 500 y el descuento es 10% con mínimo 3 unidades, al comprar 3 o más servicios de categoría 29 se aplica un descuento de RD$ 50 por unidad.
    </p>
    <button id="btnGuardarTarifa" onclick="guardarConfigTarifa()" style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:10px 20px;font-size:.9rem;cursor:pointer;font-weight:600;">Guardar configuración</button>
  </div>

  </div><!-- /seccion-config -->

</div><!-- /contenedor principal -->

@endsection

@push('scripts')
<script>
(function() {
  var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  // Cuentas Bancarias
  function renderCuentas(cuentas) {
    var tbody = document.getElementById('tbodyCuentas');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!cuentas.length) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:16px;">Sin cuentas registradas</td></tr>';
      return;
    }
    cuentas.forEach(function(c) {
      tbody.innerHTML += '<tr style="border-bottom:1px solid #f1f5f9;">' +
        '<td style="padding:10px 12px;">' + c.banco + '</td>' +
        '<td style="padding:10px 12px;">' + c.numero_cuenta + '</td>' +
        '<td style="padding:10px 12px;">' + c.tipo_cuenta + '</td>' +
        '<td style="padding:10px 12px;">' + c.titular + '</td>' +
        '<td style="padding:10px 12px;"><span style="padding:2px 8px;border-radius:9999px;font-size:.75rem;font-weight:600;background:' + (c.activo ? '#d1fae5' : '#fee2e2') + ';color:' + (c.activo ? '#065f46' : '#991b1b') + ';">' + (c.activo ? 'Activa' : 'Inactiva') + '</span></td>' +
        '<td style="padding:10px 12px;display:flex;gap:6px;">' +
          '<button onclick="editarCuenta(' + JSON.stringify(c).replace(/"/g, '&quot;') + ')" style="font-size:.75rem;padding:4px 10px;border:1px solid #3b82f6;border-radius:5px;background:#eff6ff;color:#1d4ed8;cursor:pointer;">Editar</button>' +
          '<button onclick="toggleCuenta(' + c.id + ')" style="font-size:.75rem;padding:4px 10px;border:1px solid #f59e0b;border-radius:5px;background:#fffbeb;color:#92400e;cursor:pointer;">' + (c.activo ? 'Desactivar' : 'Activar') + '</button>' +
          '<button onclick="eliminarCuenta(' + c.id + ')" style="font-size:.75rem;padding:4px 10px;border:1px solid #ef4444;border-radius:5px;background:#fef2f2;color:#dc2626;cursor:pointer;">Eliminar</button>' +
        '</td></tr>';
    });
  }

  function cargarCuentas() {
    fetch('/admin/cuentas-banco', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
      .then(function(r) { return r.json(); })
      .then(function(data) { renderCuentas(data); })
      .catch(function(e) { console.error('Error cargando cuentas', e); });
  }

  window.abrirModalCuenta = function(cuenta) {
    var modal = document.getElementById('modalCuenta');
    document.getElementById('cuentaId').value = cuenta ? cuenta.id : '';
    document.getElementById('cuentaBanco').value = cuenta ? cuenta.banco : '';
    document.getElementById('cuentaNumero').value = cuenta ? cuenta.numero_cuenta : '';
    document.getElementById('cuentaTipo').value = cuenta ? cuenta.tipo_cuenta : 'ahorro';
    document.getElementById('cuentaTitular').value = cuenta ? cuenta.titular : '';
    document.getElementById('cuentaDescripcion').value = cuenta ? (cuenta.descripcion || '') : '';
    document.getElementById('modalCuentaTitulo').textContent = cuenta ? 'Editar cuenta' : 'Nueva cuenta';
    modal.style.display = 'flex';
  };

  window.editarCuenta = function(c) { abrirModalCuenta(c); };

  window.cerrarModalCuenta = function() {
    document.getElementById('modalCuenta').style.display = 'none';
  };

  window.guardarCuenta = function() {
    var id = document.getElementById('cuentaId').value;
    var url = id ? '/admin/cuentas-banco/' + id : '/admin/cuentas-banco';
    var method = id ? 'PUT' : 'POST';
    var body = {
      banco: document.getElementById('cuentaBanco').value,
      numero_cuenta: document.getElementById('cuentaNumero').value,
      tipo_cuenta: document.getElementById('cuentaTipo').value,
      titular: document.getElementById('cuentaTitular').value,
      descripcion: document.getElementById('cuentaDescripcion').value
    };
    fetch(url, {
      method: method,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
      if (d.success) { cerrarModalCuenta(); cargarCuentas(); }
      else { alert('Error al guardar'); }
    });
  };

  window.eliminarCuenta = function(id) {
    if (!confirm('Eliminar esta cuenta?')) return;
    fetch('/admin/cuentas-banco/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
      .then(function(r) { return r.json(); })
      .then(function(d) { if (d.success) cargarCuentas(); });
  };

  window.toggleCuenta = function(id) {
    fetch('/admin/cuentas-banco/' + id + '/toggle', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken } })
      .then(function(r) { return r.json(); })
      .then(function(d) { if (d.success) cargarCuentas(); });
  };

  // Config Tarifa
  window.guardarConfigTarifa = function() {
    var btn = document.getElementById('btnGuardarTarifa');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    var body = {
      monto_registro: document.getElementById('cfgMonto').value,
      descuento_venta_masiva: document.getElementById('cfgDescuento').value,
      cantidad_minima_descuento: document.getElementById('cfgCantidad').value
    };
    fetch('/admin/config-tarifa', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
      body: JSON.stringify(body)
    })
    .then(function(r) { return r.json().then(function(d) { return { status: r.status, data: d }; }); })
    .then(function(res) {
      btn.disabled = false;
      btn.textContent = 'Guardar configuración';
      if (res.data.success) {
        var msg = document.getElementById('cfgMsgOk');
        msg.style.display = '';
        setTimeout(function() { msg.style.display = 'none'; }, 3000);
      } else {
        var errMsg = res.data.message || (res.data.errors ? Object.values(res.data.errors).flat().join(', ') : 'Error al guardar');
        alert('Error: ' + errMsg);
      }
    })
    .catch(function(e) {
      btn.disabled = false;
      btn.textContent = 'Guardar configuración';
      alert('Error de conexión: ' + e.message);
    });
  };

  cargarCuentas();
})();
</script>
@endpush