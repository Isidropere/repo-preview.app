@if($intencionCompra->isEmpty())
<div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 1rem;color:#9ca3af;">
    <svg style="width:3rem;height:3rem;margin-bottom:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    <p style="font-size:0.875rem;font-weight:500;">No hay datos</p>
</div>
@else
<div style="overflow-x:auto;">
    <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
        <thead style="background:#f9fafb;border-bottom:1px solid #f3f4f6;">
            <tr>
                <th style="text-align:left;padding:0.75rem 1rem;font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Usuario</th>
                <th style="text-align:left;padding:0.75rem 1rem;font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Artículo</th>
                <th style="text-align:left;padding:0.75rem 1rem;font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Cantidad</th>
                <th style="text-align:left;padding:0.75rem 1rem;font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Precio Unitario</th>
                <th style="text-align:left;padding:0.75rem 1rem;font-size:0.75rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($intencionCompra as $item)
            @php
                $cantidad = $item->cantidad ?? 1;
                $precioUnitario = $item->item->valor ?? 0;
                $total = $precioUnitario * $cantidad;
            @endphp
            <tr style="border-bottom:1px solid #f9fafb;">
                <td style="padding:0.75rem 1rem;">
                    @if($item->carrito?->usuario)
                        <p style="font-weight:500;color:#1f2937;margin:0;">{{ $item->carrito->usuario->nombres }}</p>
                        <p style="font-size:0.75rem;color:#9ca3af;margin:0.125rem 0 0 0;">{{ $item->carrito->usuario->email }}</p>
                    @else
                        <span style="color:#9ca3af;font-size:0.75rem;">Sin datos</span>
                    @endif
                </td>
                <td style="padding:0.75rem 1rem;">
                    <span style="font-weight:500;color:#1f2937;">{{ $item->item->item ?? 'Sin artículo' }}</span>
                </td>
                <td style="padding:0.75rem 1rem;color:#4b5563;">
                    {{ $cantidad }}
                </td>
                <td style="padding:0.75rem 1rem;font-weight:600;color:#1f2937;">
                    RD$ {{ number_format($precioUnitario, 2) }}
                </td>
                <td style="padding:0.75rem 1rem;font-weight:600;color:#1f2937;">
                    RD$ {{ number_format($total, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@if($intencionCompra->hasPages())
<div style="padding:0.75rem 1rem;border-top:1px solid #f3f4f6;">
    {{ $intencionCompra->appends(request()->except('page'))->links('vendor.pagination.custom') }}
</div>
@endif
@endif
