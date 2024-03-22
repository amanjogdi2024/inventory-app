<?php

namespace App\Http\Controllers;

use App\Models\Fabric;
use App\Models\Receipt;
use App\Models\Supplier;
use App\Models\Cat;
use App\Models\Packing;
use App\Models\OrderMain;
use Illuminate\Support\Facades\DB;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FabricController extends Controller
{
    public function index()
    {
        $fabrics = Fabric::all(); // Retrieve all fabric records from the database

        return response()->json(['fabrics' => $fabrics], 200);
        
    }


    public function show($id)
    {
        $fabric = Fabric::where('fabric_id', $id)->first();

        if (!$fabric) {
            return response()->json(['message' => 'Fabric not found'], 404);
        }
        if ($fabric->receipt_id != 0) {
            $receipt = Receipt::where('receipt_id', $fabric->receipt_id)->first();
        }else {
            $receipt =0;
        }    

        if ($fabric->supplier_id != 0) {
            $supplier = Supplier::where('supplier_id', $fabric->supplier_id)->first();
        }else {
            $supplier= 0;
        }

        if ($fabric->cat_id != 0) {
            $cat = Cat::where('cat_id', $fabric->cat_id)->first();
        }else {
            $cat= 0;
        }

    
        $fabric->receipt_id = $receipt; // Add the receipt data to the fabric object
        $fabric->supplier_id = $supplier;
        $fabric->cat_id = $cat;
    
        return response()->json(['fabric' => $fabric], 200);       
    }


    public function barCode($id, $poid)
    {   
        $fabric['fabric'] = Fabric::where('fabric_id', $id)->first();

        $fabric['packing'] = Packing::where('pac_id', $poid)->first();

        if (!$fabric['fabric'] ) {
            return response()->json(['message' => 'Fabric not found'], 404);
        }
        if ($fabric['fabric']->receipt_id != 0) {
            $receipt = Receipt::where('receipt_id', $fabric['fabric']->receipt_id)->first();
        }else {
            $receipt =0;
        }    

        if ($fabric['fabric']->supplier_id != 0) {
            $supplier = Supplier::where('supplier_id', $fabric['fabric']->supplier_id)->first();
        }else {
            $supplier= 0;
        }

        if ($fabric['fabric']->cat_id != 0) {
            $cat = Cat::where('cat_id', $fabric['fabric']->cat_id)->first();
        }else {
            $cat= 0;
        }

    
        $fabric['fabric']->receipt_id = $receipt; // Add the receipt data to the fabric object
        $fabric['fabric']->supplier_id = $supplier;
        $fabric['fabric']->cat_id = $cat;
    
        return response()->json(['data' => $fabric], 200);       
    }

    
    public function getOrder(Request $request)
    {   
        $search = $request->input('search');

        // Query orders based on the search term
        $orders = OrderMain::where('order_main_code', 'like', '%'.$search.'%')
                            ->whereYear('order_main_date', 2024)->get();// Retrieve all fabric records from the database

        return response()->json(['Order' => $orders], 200);
        
    }

    public function packingList($pac_id)
    {
        $query = "SELECT tbl_packing_list.*, cat.cat_name_en, fabric.fabric_color, fabric.fabric_box, fabric.fabric_no, fabric.fabric_in_piece, fabric.fabric_in_price, fabric.fabric_in_total, fabric.fabric_balance, fabric.on_producing FROM tbl_packing_list LEFT JOIN fabric ON tbl_packing_list.fabric_id = fabric.fabric_id LEFT JOIN cat ON cat.cat_id = fabric.cat_id WHERE pac_id = ?";
        $packingList['packing'] = DB::select("SELECT tbl_packing.*,CONCAT('PAC-',RIGHT(CONCAT('00000',tbl_packing.pac_id),6)) AS pack_no,supplier.supplier_name FROM tbl_packing LEFT JOIN supplier ON tbl_packing.supplier_id = supplier.supplier_id WHERE tbl_packing.pac_id='".$pac_id."' ");

        $packingList['packlist'] = DB::select($query, [$pac_id]);

        $sum_total = 0.0;
        foreach ($packingList['packlist'] as $key => $value) {
            $sum_total += $value->fabric_in_total;
        }

        $packingList['tootal'] = $sum_total ; 
        

        return response()->json($packingList);
    }

}
