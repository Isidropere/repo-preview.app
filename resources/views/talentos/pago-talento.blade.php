@extends('layouts.app')
@section('title', 'Pago para publicar talento')

@section('content')
<div style="max-width:600px;margin:40px auto;padding:0 16px;font-family:sans-serif;">

  @include('components.btn-volver', ['backUrl' => route('items.talento_create')])

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:28px;margin-top:16px;">

    <h1 style="font-size:1.4rem;font-weight:700;color:#1e293b;margin:0 0 6px;">Publicar tu talento</h1>
    <p style="color:#64748b;font-size:.9rem;margin:0 0 24px;">Para publicar tu talento en la plataforma debes realizar un pago único de registro.</p>

    {{-- Monto --}}
    <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;">
      <span style="color:#0369a1;font-weight:600;">Monto de registro</span>
      <span style="font-size:1.5rem;font-weight:700;color:#0369a1;">RD$ {{ number_format($monto, 2) }}</span>
    </div>

    {{-- Mensajes de error/éxito --}}
    @if(session('error'))
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#dc2626;font-size:.875rem;">
        {{ session('error') }}
      </div>
    @endif

    @if($errors->any())
      <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#dc2626;font-size:.875rem;">
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form id="formPagoTalento" action="{{ route('talento.pago.procesar') }}" method="POST">
      @csrf
      <input type="hidden" name="id_tarjeta" id="hiddenIdTarjeta" value="">

      {{-- Selector de tarjetas guardadas --}}
      <div style="margin-bottom:20px;">
        <div style="font-weight:600;color:#1e293b;margin-bottom:12px;font-size:.95rem;">Selecciona una tarjeta</div>

        @forelse($tarjetas as $tarjeta)
          <div style="border:2px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:10px;cursor:pointer;transition:border-color .2s;"
               class="tarjeta-item"
               data-id="{{ $tarjeta->id_tarjeta }}"
               onclick="seleccionarTarjeta(this)">
            <div style="display:flex;align-items:center;gap:12px;">
              <input type="radio" name="id_tarjeta_select" value="{{ $tarjeta->id_tarjeta }}" style="width:16px;height:16px;">
              <div>
                <div style="font-weight:600;color:#1e293b;">
                  **** **** **** {{ $tarjeta->last4 ?? '????' }}
                </div>
                <div style="font-size:.8rem;color:#64748b;">
                  {{ $tarjeta->nombre_titular ?? '' }} &bull; Vence {{ $tarjeta->mes_expiracion ?? '??' }}/{{ $tarjeta->getAttribute(\App\Models\TarjetaPago::COL_ANIO) ?? '??' }}
                </div>
              </div>
            </div>
          </div>
        @empty
          <div style="background:#fefce8;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;color:#92400e;font-size:.875rem;margin-bottom:12px;">
            No tienes tarjetas guardadas. Agrega una tarjeta para continuar.
          </div>
        @endforelse
      </div>

      {{-- CVV --}}
      <div id="cvvSection" style="margin-bottom:20px;{{ $tarjetas->isEmpty() ? 'display:none;' : '' }}">
        <label style="display:block;font-weight:600;color:#1e293b;margin-bottom:6px;font-size:.9rem;">CVV / CVC</label>
        <input type="password" name="cvv" id="cvv" maxlength="4" placeholder="123"
               style="width:120px;border:1px solid #cbd5e1;border-radius:8px;padding:10px 14px;font-size:1rem;outline:none;">
        <span style="font-size:.75rem;color:#64748b;margin-left:8px;">3 o 4 dígitos al dorso de tu tarjeta</span>
      </div>

      {{-- Botón pagar --}}
      <button type="submit" id="btnPagar"
              style="width:100%;background:#2563eb;color:#fff;border:none;border-radius:8px;padding:14px;font-size:1rem;font-weight:600;cursor:pointer;transition:background .2s;"
              {{ $tarjetas->isEmpty() ? 'disabled' : '' }}>
        Pagar RD$ {{ number_format($monto, 2) }} y publicar
      </button>
    </form>

    {{-- Agregar nueva tarjeta --}}
    <div style="margin-top:20px;padding-top:20px;border-top:1px solid #e2e8f0;">
      <div style="font-weight:600;color:#1e293b;margin-bottom:12px;font-size:.9rem;">¿No tienes tarjeta guardada?</div>
      <a href="{{ route('carrito.checkout_index') }}"
         style="display:inline-block;color:#2563eb;font-size:.875rem;text-decoration:none;border:1px solid #2563eb;border-radius:6px;padding:8px 16px;">
        Ir al carrito para agregar una tarjeta
      </a>
      <p style="font-size:.75rem;color:#94a3b8;margin-top:8px;">
        Agrega tu tarjeta desde el checkout y luego regresa aquí para completar el pago.
      </p>
    </div>

  </div>
</div>

<script>
function seleccionarTarjeta(el) {
  document.querySelectorAll('.tarjeta-item').forEach(function(t) {
    t.style.borderColor = '#e2e8f0';
  });
  el.style.borderColor = '#2563eb';
  var radio = el.querySelector('input[type=radio]');
  if (radio) radio.checked = true;
  document.getElementById('hiddenIdTarjeta').value = el.dataset.id;
  document.getElementById('cvvSection').style.display = '';
}

// Preseleccionar primera tarjeta
var primera = document.querySelector('.tarjeta-item');
if (primera) seleccionarTarjeta(primera);

// Validar antes de enviar
document.getElementById('formPagoTalento').addEventListener('submit', function(e) {
  var id = document.getElementById('hiddenIdTarjeta').value;
  if (!id) {
    e.preventDefault();
    alert('Por favor selecciona una tarjeta para continuar.');
    return;
  }
  var btn = document.getElementById('btnPagar');
  btn.disabled = true;
  btn.textContent = 'Procesando pago...';
});
</script>
@endsection
