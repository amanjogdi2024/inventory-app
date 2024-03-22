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
        

        $packingList['packlist'] = DB::select($query, [$pac_id]);

        $sum_total = 0.0;
        foreach ($packingList['packlist'] as $key => $value) {
            $sum_total += $value->fabric_in_total;
        }

        $packingList['tootal'] = $sum_total ; 
        

        return response()->json($packingList);
    }

    public function materialList()
    {              

        $query = DB::select("SELECT 
                tbl_rq_form.*, 
                employee.employee_name 
            FROM 
                tbl_rq_form 
            LEFT JOIN 
                tbl_rq_form_item ON tbl_rq_form.rq_id = tbl_rq_form_item.rq_id 
            LEFT JOIN 
                employee ON employee.employee_id = tbl_rq_form.employee_id 
            WHERE 
                tbl_rq_form.enable = 1 
                AND (tbl_rq_form.rq_status = 'new' OR tbl_rq_form.rq_status = 'update')  
            ORDER BY 
                tbl_rq_form.finish_date DESC, tbl_rq_form.rq_date DESC;
            ");

        return response()->json($query);
    }

    public function materialByfabord($fab_id, $ord_id)
    {                      
        $order_main = OrderMain::where('order_main_id', $ord_id)->first();
                
        $order_title = $order_main['order_main_code'];

        $data['tbl_order_lkr'] = DB::select("SELECT * FROM tbl_order_lkr WHERE order_title='".$order_title."' AND enable=1 ORDER BY file_name ASC ");
        
        $data['order'] = DB::select("SELECT order_name,order_detail,folder_name FROM tbl_order_lkr_title WHERE order_title='".$order_title."' ");


        return response()->json($data);
    }

    public function materialRequest($rq_id){
        
        $materialRequest = DB::select("SELECT tbl_rq_form_item.*,fabric.*,cat.cat_name_en FROM tbl_rq_form_item LEFT JOIN fabric ON tbl_rq_form_item.fabric_id=fabric.fabric_id LEFT JOIN cat ON cat.cat_id=fabric.cat_id WHERE tbl_rq_form_item.rq_id='".$rq_id."' ");

        return response()->json($materialRequest);
    }

}
