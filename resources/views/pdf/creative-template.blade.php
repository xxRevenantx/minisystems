<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; font-family: DejaVu Sans, sans-serif; }
        .canvas { position: relative; width: 100%; height: 100%; overflow: hidden; background: #fff; }
        .background { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .block { position: absolute; overflow: hidden; display: flex; align-items: center; }
        .line { width: 100%; height: 1px; background: currentColor; }
        .box { width: 100%; height: 100%; border: 2px solid currentColor; }
        .qr { width: 100%; height: 100%; background: #111; color: white; display: flex; align-items: center; justify-content: center; font-size: 9px; }
        .safe-area { position: absolute; border: 1px dashed rgba(245, 158, 11, .75); pointer-events: none; }
        .crop { position: absolute; background: #111; opacity: .8; }
        .crop.tl-h,.crop.tr-h,.crop.bl-h,.crop.br-h { width: 12px; height: 1px; }
        .crop.tl-v,.crop.tr-v,.crop.bl-v,.crop.br-v { width: 1px; height: 12px; }
        .crop.tl-h { left: 2px; top: 6px; } .crop.tl-v { left: 6px; top: 2px; }
        .crop.tr-h { right: 2px; top: 6px; } .crop.tr-v { right: 6px; top: 2px; }
        .crop.bl-h { left: 2px; bottom: 6px; } .crop.bl-v { left: 6px; bottom: 2px; }
        .crop.br-h { right: 2px; bottom: 6px; } .crop.br-v { right: 6px; bottom: 2px; }
    </style>
</head>
<body>
@php
    $replaceVariables = function (?string $text) use ($row) {
        $text = (string) $text;
        foreach ($row as $key => $value) {
            $text = str_replace('{{'.$key.'}}', (string) $value, $text);
        }
        return $text;
    };
@endphp
@php
    $print = $template->configuracion_impresion ?? [];
    $printEnabled = (bool) ($print['habilitada'] ?? false);
    $safeMm = (float) ($print['margen_seguro_mm'] ?? 0);
    $safePct = min(18, max(0, ($safeMm / 210) * 100));
@endphp
<div class="canvas">
    @if($template->fondo)
        <img class="background" src="{{ storage_path('app/public/'.$template->fondo->archivo) }}">
    @endif

    @if($printEnabled && $safeMm > 0)
        <div class="safe-area" style="left:{{ $safePct }}%;right:{{ $safePct }}%;top:{{ $safePct }}%;bottom:{{ $safePct }}%;"></div>
    @endif
    @if($printEnabled && ($print['marcas_corte'] ?? false))
        @foreach(['tl-h','tl-v','tr-h','tr-v','bl-h','bl-v','br-h','br-v'] as $mark)<span class="crop {{ $mark }}"></span>@endforeach
    @endif

    @foreach($template->estructura ?? [] as $block)
        @php
            $style = sprintf(
                'left:%s%%;top:%s%%;width:%s%%;height:%s%%;color:%s;text-align:%s;font-size:%spx;font-weight:%s;',
                $block['x'] ?? 0,
                $block['y'] ?? 0,
                $block['w'] ?? 10,
                $block['h'] ?? 10,
                $block['color'] ?? '#111827',
                $block['align'] ?? 'center',
                max(6, (int) ($block['font_size'] ?? 30)),
                $block['font_weight'] ?? '700',
            );
        @endphp
        <div class="block" style="{{ $style }}">
            @switch($block['tipo'] ?? 'texto')
                @case('texto')
                    <div style="width:100%">{!! nl2br(e($replaceVariables($block['contenido'] ?? ''))) !!}</div>
                    @break
                @case('linea')
                    <div class="line"></div>
                    @break
                @case('caja')
                    <div class="box"></div>
                    @break
                @case('qr')
                    @php
                        $qrContent = $replaceVariables($block['contenido'] ?? ('{' . '{folio}' . '}'));
                        $qrUri = app(\App\Services\QrCodeService::class)->dataUri($qrContent, 320, 1);
                    @endphp
                    @if($qrUri)
                        <img src="{{ $qrUri }}" style="width:100%;height:100%;object-fit:contain" alt="Código QR">
                    @else
                        <div class="qr">CÓDIGO<br>{{ $qrContent }}</div>
                    @endif
                    @break
                @case('imagen')
                    @php $imagePath = $replaceVariables($block['contenido'] ?? ''); @endphp
                    @if($imagePath && is_file(storage_path('app/public/'.$imagePath)))
                        <img src="{{ storage_path('app/public/'.$imagePath) }}" style="width:100%;height:100%;object-fit:cover">
                    @endif
                    @break
            @endswitch
        </div>
    @endforeach
</div>
</body>
</html>
