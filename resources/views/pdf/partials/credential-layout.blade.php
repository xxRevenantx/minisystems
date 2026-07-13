<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Credenciales</title>
<style>
@page { margin: 28px 28px; }
* { box-sizing: border-box; }
body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #0f172a; }
.card { position: relative; width: 18cm; height: 5.5cm; margin: 0 auto 10px; overflow: hidden; border: 1px solid #cbd5e1; border-radius: 12px; background: #fff; page-break-inside: avoid; }
.bar { position: absolute; left: 0; top: 0; width: 100%; height: 12px; background: #006492; }
.bar2 { position: absolute; right: 0; top: 0; width: 34%; height: 12px; background: #88AC2E; }
.photo { position: absolute; left: 16px; top: 28px; width: 112px; height: 142px; border-radius: 10px; border: 1px solid #cbd5e1; object-fit: cover; background: #f1f5f9; }
.no-photo { position: absolute; left: 16px; top: 28px; width: 112px; height: 142px; border-radius: 10px; border: 1px dashed #94a3b8; text-align: center; padding-top: 61px; font-size: 10px; color: #64748b; background: #f8fafc; }
.brand-logo { position: absolute; right: 18px; top: 24px; width: 58px; height: 58px; object-fit: contain; }
.info { position: absolute; left: 146px; top: 28px; right: 92px; bottom: 18px; }
.kicker { font-size: 8px; text-transform: uppercase; letter-spacing: 1.8px; color: #006492; font-weight: bold; }
.name { margin-top: 5px; font-size: 17px; line-height: 19px; font-weight: bold; text-transform: uppercase; }
.role { margin-top: 4px; font-size: 10px; color: #334155; font-weight: bold; }
.org { font-size: 9px; color: #64748b; }
.grid { margin-top: 12px; width: 100%; border-collapse: collapse; }
.grid td { padding: 2px 8px 2px 0; font-size: 8px; vertical-align: top; }
.label { color: #64748b; font-weight: bold; text-transform: uppercase; }
.value { color: #0f172a; font-weight: bold; }
.validation { position: absolute; right: 16px; bottom: 14px; width: 88px; text-align: center; font-size: 6px; line-height: 1.2; color: #64748b; }
.validation img { display: block; width: 48px; height: 48px; margin: 0 auto 3px; }
.validation strong { display: block; color: #006492; font-size: 7px; }
.back { position: relative; width: 18cm; height: 5.5cm; margin: 0 auto 10px; overflow: hidden; border: 1px solid #cbd5e1; border-radius: 12px; background: #f8fafc; page-break-inside: avoid; }
.back-bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.back-overlay { position: absolute; inset: 0; background: rgba(255,255,255,.90); }
.back-brand { position: absolute; left: 22px; top: 20px; font-size: 12px; font-weight: bold; color: #006492; }
.back-text { position: absolute; left: 22px; right: 150px; top: 52px; bottom: 22px; font-size: 9px; line-height: 1.5; color: #334155; white-space: pre-line; }
.back-code { position: absolute; right: 22px; top: 34px; width: 110px; min-height: 126px; padding: 8px; border: 1px dashed #006492; border-radius: 8px; text-align: center; font-size: 7px; color: #64748b; }
.back-code img { display:block; width:68px; height:68px; margin:4px auto; }
.back-code strong { display: block; margin: 4px 0; color: #006492; font-size: 8px; }
.page-break { page-break-after: always; }
</style>
</head>
<body>
@php $hasReverse = $credenciales->contains(fn($item) => (bool) $item->tiene_reverso); @endphp
@foreach($credenciales as $index => $credencial)
    @php
        $isSchool = ($credencial->tipo ?? 'general') === 'escolar';
        $photoPath = $credencial->foto ? storage_path('app/public/'.$credencial->foto) : null;
        $brandLogo = $credencial->marca?->logo ? storage_path('app/public/'.$credencial->marca->logo) : null;
        $reverseImage = $credencial->reverso_imagen ? storage_path('app/public/'.$credencial->reverso_imagen) : null;
        $brandPrimary = $credencial->marca?->color_primario ?: '#006492';
        $brandSecondary = $credencial->marca?->color_secundario ?: '#88AC2E';
        $folio = $credencial->folio ?: $credencial->matricula;
        $typeLabel = match($credencial->tipo ?? 'general') {
            'evento' => 'Gafete de evento',
            'empleado' => 'Credencial de personal',
            'visitante' => 'Pase de visitante',
            'membresia' => 'Credencial de membresía',
            'escolar' => 'Credencial escolar',
            default => 'Identificación general',
        };
        $schoolProgram = $credencial->nivel === 'Licenciatura'
            ? $credencial->licenciatura
            : trim(($credencial->grado ?? '').' '.($credencial->grupo ?? ''));
    @endphp
    <div class="card">
        <div class="bar"></div><div class="bar2"></div>

        @if($photoPath && is_file($photoPath))
            <img class="photo" src="{{ $photoPath }}">
        @else
            <div class="no-photo">FOTOGRAFÍA</div>
        @endif

        @if($brandLogo && is_file($brandLogo))
            <img class="brand-logo" src="{{ $brandLogo }}">
        @endif

        <div class="info">
            <div class="kicker">{{ $typeLabel }}</div>
            <div class="name">{{ $credencial->nombre }}</div>
            <div class="role">{{ $credencial->cargo ?: ($isSchool ? ($credencial->nivel ?: 'Estudiante') : 'Identificación') }}</div>
            <div class="org">{{ $credencial->organizacion ?: ($credencial->marca?->nombre ?? '') }}</div>

            <table class="grid">
                <tr>
                    <td><span class="label">Folio</span><br><span class="value">{{ $folio ?: '—' }}</span></td>
                    <td><span class="label">Vigencia</span><br><span class="value">{{ $credencial->vigencia ?: 'Sin vigencia' }}</span></td>
                    <td><span class="label">Estado</span><br><span class="value">{{ strtoupper($credencial->estado ?: 'ACTIVA') }}</span></td>
                </tr>
                @if($isSchool)
                    <tr>
                        <td><span class="label">Nivel</span><br><span class="value">{{ $credencial->nivel ?: '—' }}</span></td>
                        <td colspan="2"><span class="label">{{ $credencial->nivel === 'Licenciatura' ? 'Licenciatura' : 'Grado / grupo' }}</span><br><span class="value">{{ $schoolProgram ?: '—' }}</span></td>
                    </tr>
                @else
                    <tr>
                        <td><span class="label">Teléfono</span><br><span class="value">{{ $credencial->telefono ?: '—' }}</span></td>
                        <td colspan="2"><span class="label">Correo</span><br><span class="value">{{ $credencial->correo ?: '—' }}</span></td>
                    </tr>
                @endif
            </table>
        </div>

        @if($credencial->registroValidacion)
            @php
                $validationUrl = route('validacion.publica', $credencial->registroValidacion->codigo);
                $validationQr = app(\App\Services\QrCodeService::class)->dataUri($validationUrl, 150, 1);
            @endphp
            <div class="validation">
                @if($validationQr)<img src="{{ $validationQr }}" alt="QR de validación">@endif
                <strong>{{ $credencial->registroValidacion->codigo }}</strong>
                VALIDACIÓN PÚBLICA
            </div>
        @endif
    </div>

    @if($credencial->tiene_reverso)
        <div class="back" style="border-color: {{ $brandPrimary }};">
            @if($reverseImage && is_file($reverseImage))
                <img class="back-bg" src="{{ $reverseImage }}">
                <div class="back-overlay"></div>
            @else
                <div style="position:absolute;left:0;top:0;bottom:0;width:14px;background:{{ $brandPrimary }};"></div>
                <div style="position:absolute;right:0;top:0;bottom:0;width:14px;background:{{ $brandSecondary }};"></div>
            @endif
            <div class="back-brand" style="color: {{ $brandPrimary }};">{{ $credencial->marca?->nombre ?: ($credencial->organizacion ?: 'MiniSystems') }}</div>
            <div class="back-text">{{ $credencial->reverso_texto ?: 'Esta credencial es personal e intransferible. En caso de extravío, comuníquese con la organización emisora.' }}</div>
            <div class="back-code" style="border-color: {{ $brandPrimary }};">
                IDENTIFICADOR
                <strong style="color: {{ $brandPrimary }};">{{ $folio ?: '—' }}</strong>
                @if($credencial->registroValidacion)
                    @php
                        $backValidationUrl = route('validacion.publica', $credencial->registroValidacion->codigo);
                        $backValidationQr = app(\App\Services\QrCodeService::class)->dataUri($backValidationUrl, 180, 1);
                    @endphp
                    @if($backValidationQr)<img src="{{ $backValidationQr }}" alt="QR de validación">@endif
                    VALIDACIÓN<br>{{ $credencial->registroValidacion->codigo }}
                @endif
            </div>
        </div>
    @endif

    @php $perPage = $hasReverse ? 2 : 4; @endphp
    @if(($index + 1) % $perPage === 0 && !$loop->last)<div class="page-break"></div>@endif
@endforeach
</body>
</html>
