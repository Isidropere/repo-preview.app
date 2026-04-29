@extends('layouts.app')

@section('title', 'Aprobación de Imágenes')

@section('content')
<div style="min-height:100vh;background:#f8fafc;padding:32px 16px;">
<div style="max-width:1100px;margin:0 auto;">

    @include('components.btn-volver', ['backUrl' => route('admin.index')])

    <div style="margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.5rem;font-weight:700;color:#1e293b;margin:0;">Aprobación de Imágenes</h1>
            <p style="font-size:.85rem;color:#64748b;margin:4px 0 0;">Revisa y aprueba o rechaza las imágenes pendientes de artículos y perfiles.</p>
        </div>
    </div>

    @if(session('success'))
    <div style="margin-bottom:16px;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:10px;padding:12px 16px;font-size:.875rem;">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div style="margin-bottom:16px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;padding:12px 16px;font-size:.875rem;">
        {{ session('error') }}
    </div>
    @endif

    {{-- ── IMÁGENES DE ARTÍCULOS ── --}}
    <div style="background:#fff;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.07);border:1px solid #e2e8f0;margin-bottom:32px;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:1rem;font-weight:700;color:#1e293b;">Imágenes de Artículos</span>
                <span style="background:#fef3c7;color:#92400e;font-size:.75rem;font-weight:600;padding:2px 10px;border-radius:999px;">
                    {{ $imagenesItems->count() }} pendiente{{ $imagenesItems->count() !== 1 ? 's' : '' }}
                </span>
            </div>
            @if($imagenesItems->count() > 0)
            <form method="POST" action="{{ route('admin.imagenes.items.aprobarTodas') }}" onsubmit="return confirm('¿Aprobar todas las imágenes de artículos pendientes?')">
                @csrf
                <button type="submit" style="background:#16a34a;color:#fff;border:none;border-radius:8px;padding:7px 16px;font-size:.82rem;font-weight:600;cursor:pointer;">
                    ✓ Aprobar todas
                </button>
            </form>
            @endif
        </div>

        @if($imagenesItems->isEmpty())
        <div style="padding:40px;text-align:center;color:#94a3b8;font-size:.9rem;">
            No hay imágenes de artículos pendientes de aprobación.
        </div>
        @else
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Imagen</th>
                    <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Artículo</th>
                    <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Usuario</th>
                    <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Tipo</th>
                    <th style="padding:10px 16px;text-align:center;font-weight:600;color:#475569;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($imagenesItems as $img)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 16px;">
                    <img src="{{ asset($img->ruta . '/' . ($img->nombre ?? '')) }}"
                         alt="Imagen artículo"
                         style="width:64px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;"
                         loading="lazy" width="64" height="64"
                         onerror="this.src='{{ asset('images/placeholder.png') }}'">
                </td>
                <td style="padding:10px 16px;color:#1e293b;font-weight:500;">
                    {{ $img->item->item ?? '—' }}
                </td>
                <td style="padding:10px 16px;color:#475569;">
                    {{ $img->item->id_user ?? '—' }}
                </td>
                <td style="padding:10px 16px;color:#64748b;">
                    {{ $img->tipo ?? '—' }}
                </td>
                <td style="padding:10px 16px;text-align:center;">
                    <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                        {{-- Aprobar --}}
                        <form method="POST" action="{{ route('admin.imagenes.items.aprobar', $img->id_imagen) }}">
                            @csrf
                            <button type="submit" style="background:#16a34a;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:.78rem;font-weight:600;cursor:pointer;">
                                ✓ Aprobar
                            </button>
                        </form>
                        {{-- Rechazar --}}
                        <details style="display:inline-block;">
                            <summary style="background:#dc2626;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:.78rem;font-weight:600;cursor:pointer;list-style:none;display:inline-block;">
                                ✕ Rechazar
                            </summary>
                            <div style="position:absolute;z-index:10;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px;box-shadow:0 4px 16px rgba(0,0,0,.12);min-width:260px;margin-top:4px;">
                                <form method="POST" action="{{ route('admin.imagenes.items.rechazar', $img->id_imagen) }}">
                                    @csrf
                                    <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Motivo del rechazo *</label>
                                    <textarea name="motivo_rechazo" required rows="3"
                                        style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:7px 10px;font-size:.82rem;resize:vertical;box-sizing:border-box;"
                                        placeholder="Describe el motivo..."></textarea>
                                    <button type="submit" style="margin-top:8px;background:#dc2626;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:.8rem;font-weight:600;cursor:pointer;width:100%;">
                                        Confirmar rechazo
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

    {{-- ── FOTOS DE PERFIL ── --}}
    <div style="background:#fff;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.07);border:1px solid #e2e8f0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:1rem;font-weight:700;color:#1e293b;">Fotos de Perfil</span>
                <span style="background:#fef3c7;color:#92400e;font-size:.75rem;font-weight:600;padding:2px 10px;border-radius:999px;">
                    {{ $fotosUsuarios->count() }} pendiente{{ $fotosUsuarios->count() !== 1 ? 's' : '' }}
                </span>
            </div>
            @if($fotosUsuarios->count() > 0)
            <form method="POST" action="{{ route('admin.imagenes.perfiles.aprobarTodas') }}" onsubmit="return confirm('¿Aprobar todas las fotos de perfil pendientes?')">
                @csrf
                <button type="submit" style="background:#16a34a;color:#fff;border:none;border-radius:8px;padding:7px 16px;font-size:.82rem;font-weight:600;cursor:pointer;">
                    ✓ Aprobar todas
                </button>
            </form>
            @endif
        </div>

        @if($fotosUsuarios->isEmpty())
        <div style="padding:40px;text-align:center;color:#94a3b8;font-size:.9rem;">
            No hay fotos de perfil pendientes de aprobación.
        </div>
        @else
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Foto</th>
                    <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Usuario</th>
                    <th style="padding:10px 16px;text-align:left;font-weight:600;color:#475569;">Email</th>
                    <th style="padding:10px 16px;text-align:center;font-weight:600;color:#475569;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($fotosUsuarios as $user)
            <tr style="border-bottom:1px solid #f1f5f9;">
                <td style="padding:10px 16px;">
                    <img src="{{ \App\Helpers\ImageHelper::urlPerfil($user->foto_perfil) }}"
                         alt="Foto de perfil"
                         style="width:56px;height:56px;object-fit:cover;border-radius:50%;border:2px solid #e2e8f0;"
                         loading="lazy" width="56" height="56"
                         onerror="this.src='{{ asset('imgs/defaults/profile_default.svg') }}'">
                </td>
                <td style="padding:10px 16px;color:#1e293b;font-weight:500;">
                    {{ $user->name }}
                </td>
                <td style="padding:10px 16px;color:#64748b;">
                    {{ $user->email }}
                </td>
                <td style="padding:10px 16px;text-align:center;">
                    <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                        {{-- Aprobar --}}
                        <form method="POST" action="{{ route('admin.imagenes.perfiles.aprobar', $user->id) }}">
                            @csrf
                            <button type="submit" style="background:#16a34a;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:.78rem;font-weight:600;cursor:pointer;">
                                ✓ Aprobar
                            </button>
                        </form>
                        {{-- Rechazar --}}
                        <details style="display:inline-block;">
                            <summary style="background:#dc2626;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:.78rem;font-weight:600;cursor:pointer;list-style:none;display:inline-block;">
                                ✕ Rechazar
                            </summary>
                            <div style="position:absolute;z-index:10;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px;box-shadow:0 4px 16px rgba(0,0,0,.12);min-width:260px;margin-top:4px;">
                                <form method="POST" action="{{ route('admin.imagenes.perfiles.rechazar', $user->id) }}">
                                    @csrf
                                    <label style="display:block;font-size:.8rem;font-weight:600;color:#374151;margin-bottom:6px;">Motivo del rechazo *</label>
                                    <textarea name="motivo_rechazo" required rows="3"
                                        style="width:100%;border:1px solid #d1d5db;border-radius:6px;padding:7px 10px;font-size:.82rem;resize:vertical;box-sizing:border-box;"
                                        placeholder="Describe el motivo..."></textarea>
                                    <button type="submit" style="margin-top:8px;background:#dc2626;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:.8rem;font-weight:600;cursor:pointer;width:100%;">
                                        Confirmar rechazo
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

</div>
</div>
@endsection
