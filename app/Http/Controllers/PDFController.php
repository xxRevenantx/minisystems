<?php

namespace App\Http\Controllers;

use App\Models\Credencial;
use App\Models\Reconocimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
    public function reconocimiento($id)
    {

        $reconocimiento = Reconocimiento::with('reconocimientoImagen', 'directivos')->find($id);

        if (!$reconocimiento) {
            abort(404);
        }

        $data = [
            'reconocimiento' => $reconocimiento,
        ];

        $pdf = Pdf::loadView('livewire.reconocimientos.pdf.reconocimientoPDF', $data)->setPaper('letter', 'landscape')
            ->setOption([
                'fontDir' => public_path('/fonts'),
                'fontCache' => public_path('/fonts'),
                'defaultFont' => 'greatVibes'
            ]);
        return $pdf->stream("Reconocimiento_{$reconocimiento->reconocimiento_a}.pdf");
    }

    public function descargar_reconocimientos()
    {
        $reconocimientos = Reconocimiento::with('reconocimientoImagen', 'directivos')->orderBy('id', 'asc')->get();

        $data = [
            'reconocimientos' => $reconocimientos,
        ];

        $pdf = Pdf::loadView('livewire.reconocimientos.pdf.descargarReconocimientosPDF', $data)
            ->setPaper('letter', 'landscape')
            ->setOption([
                'fontDir' => public_path('/fonts'),
                'fontCache' => public_path('/fonts'),
                'defaultFont' => 'greatVibes',
            ]);

        return $pdf->stream("Reconocimientos.pdf");
    }

    public function credencialPdf(Credencial $credencial)
    {




        $pdf = Pdf::loadView('pdf.credencialIndividualPDF', [
            'credenciales' => collect([$credencial]),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('credencial_' . $credencial->matricula . '.pdf');
    }

    public function credencialesPdfTodas()
    {
        $credenciales = Credencial::query()
            ->orderBy('nivel')
            ->orderBy('licenciatura')
            ->orderBy('grado')
            ->orderBy('grupo')
            ->orderBy('nombre')
            ->get();

        if ($credenciales->isEmpty()) {
            abort(404, 'No hay credenciales registradas para descargar.');
        }

        $pdf = Pdf::loadView('pdf.credencialTodasPDF', [
            'credenciales' => $credenciales,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('todas_las_credenciales.pdf');
    }

}
