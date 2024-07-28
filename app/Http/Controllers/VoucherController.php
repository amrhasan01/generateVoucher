<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class VoucherController extends Controller
{

    public function generateVoucher(Request $request)
    {
    
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
        ]);

        $id = $request->input('id');
        $name = $request->input('name');

        // validate id or name is provided and not both
        if (($id && $name) || (!$id && !$name)) {
            return response()->json(['error' => 'you must entered id or a name and not both.'], 400);
        }
        
        
        // call the stored procedure with the id or name parameters
        $voucher = DB::select('CALL GetVoucher(?, ?)', [$id, $name]);
        

        if (empty($voucher)) {
            return response()->json(['error' => 'voucher not found'], 404);
        }

        $voucher = $voucher[0]; //get a single result

        // generate PDF Voucher
        $mpdf = new Mpdf();
        $mpdf->WriteHTML("<h1>Voucher Details</h1>");
        $mpdf->WriteHTML("<p>Name: {$voucher->name}</p>");
        $mpdf->WriteHTML("<p>Amount: {$voucher->amount}</p>");
        $mpdf->WriteHTML("<p>Description: {$voucher->description}</p>");

        // return pdf with download
        return response($mpdf->Output('', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="voucher.pdf"');
    }

}
