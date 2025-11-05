<?php

namespace App\Http\Controllers;

use App\Models\Reconocimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PDFController extends Controller
{
     public function reconocimiento($id){

        $reconocimiento = Reconocimiento::find($id);

        if (!$reconocimiento) {
            abort(404);
        }

        $data = [
            'reconocimiento' => $reconocimiento,
        ];

        $pdf = Pdf::loadView('livewire.reconocimientos.pdf.reconocimientoPDF', $data)->setPaper('letter', 'landscape');
        return $pdf->stream("Expediente_{$reconocimiento->reconocimiento_a}.pdf");
    }

}
