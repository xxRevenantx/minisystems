<!doctype html>
<html lang="es"><head><meta charset="utf-8"><style>
    @page{margin:0}*{box-sizing:border-box}body{margin:0;font-family:DejaVu Sans,sans-serif;color:#1f2937}.pagina{position:relative;width:100%;height:100%;overflow:hidden;page-break-after:always}.pagina:last-child{page-break-after:auto}.fondo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.bloque{position:absolute;left:8%;right:8%;text-align:center}.nombre{font-family:serif;font-weight:bold;color:#006492;line-height:1.05}.lugar{margin-top:8px;font-weight:bold;color:#66851f}.descripcion{line-height:1.5}.fecha{font-weight:bold}.evento{font-size:10px;color:#555;margin-bottom:5px}.firmas{position:absolute;left:6%;right:6%;display:table;width:88%;table-layout:fixed}.firma{display:table-cell;text-align:center;vertical-align:bottom;padding:0 8px;font-size:9px}.firma-img{height:40px;max-width:110px;object-fit:contain}.linea{border-top:1px solid #333;padding-top:4px}.cargo{font-size:8px;color:#555;margin-top:2px}.cancelado{position:absolute;left:12%;right:12%;top:42%;transform:rotate(-25deg);font-size:58px;font-weight:bold;color:rgba(190,0,0,.20);text-align:center;z-index:5}.sello{position:absolute;right:5%;bottom:4%;width:65px;opacity:.9}
</style></head><body>
@foreach($reconocimientos as $reconocimiento)
@php
    $img=$reconocimiento->reconocimientoImagen; $cfg=$img?->configuracion ?? [];
    $nombreTop=data_get($cfg,'nombre.top',250); $nombreTam=data_get($cfg,'nombre.tamano',34);
    $descTop=data_get($cfg,'descripcion.top',330); $descTam=data_get($cfg,'descripcion.tamano',16);
    $fechaTop=data_get($cfg,'fecha.top',470); $firmasTop=data_get($cfg,'firmas.top',540);
    $dirs=$reconocimiento->directivos->sortBy('orden')->values();
@endphp
<div class="pagina">
    @if($img?->imagen)<img class="fondo" src="{{ public_path('storage/imagenesReconocimientos/'.$img->imagen) }}">@endif
    @if($reconocimiento->estado==='cancelado')<div class="cancelado">CANCELADO</div>@endif
    <div class="bloque nombre" style="top:{{ $nombreTop }}px;font-size:{{ $nombreTam }}px">{{ $reconocimiento->reconocimiento_a }}</div>
    @if($reconocimiento->lugar_obtenido)<div class="bloque lugar" style="top:{{ $nombreTop+$nombreTam+15 }}px;font-size:{{ max(12,$nombreTam-16) }}px">{{ $reconocimiento->lugar_obtenido }}</div>@endif
    <div class="bloque descripcion" style="top:{{ $descTop }}px;font-size:{{ $descTam }}px">
        @if($reconocimiento->evento)<div class="evento">{{ $reconocimiento->evento->nombre }}@if($reconocimiento->evento->lugar) · {{ $reconocimiento->evento->lugar }}@endif</div>@endif
        {!! \App\Support\ReconocimientoHtml::limpiar($reconocimiento->descripcion) !!}
    </div>
    <div class="bloque fecha" style="top:{{ $fechaTop }}px;font-size:12px">{{ $reconocimiento->fecha?->translatedFormat('d \d\e F \d\e Y') }}</div>
    <div class="firmas" style="top:{{ $firmasTop }}px">
        @foreach($dirs as $d)<div class="firma">@if($d->firma)<img class="firma-img" src="{{ public_path('storage/firmasDirectivos/'.$d->firma) }}">@else<div style="height:40px"></div>@endif<div class="linea"><strong>{{ $d->nombre_completo }}</strong><div class="cargo">{{ $d->cargo }}</div></div></div>@endforeach
    </div>
    @php $sello=$dirs->first(fn($d)=>$d->sello); @endphp
    @if($sello)<img class="sello" src="{{ public_path('storage/sellosDirectivos/'.$sello->sello) }}">@endif
</div>
@endforeach
</body></html>
