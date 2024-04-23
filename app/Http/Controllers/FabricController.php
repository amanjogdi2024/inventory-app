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
        // Query orders based on the search term and year
        $query = OrderMain::whereYear('order_main_date', 2024);
        // Check if search term is provided
        if (!empty($search)) {
            $query->where('order_main_code', 'like', '%'.$search.'%');
        }

        $orders = $query->get();
        
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

    public function adjust(){
        $materialRequest = DB::select("SELECT cat.cat_name_en, fabric.cat_id, COUNT(DISTINCT fabric.fabric_color) AS n_colors, COUNT(*) AS n_rolls, SUM(fabric.fabric_balance) AS f_bal FROM fabric LEFT JOIN cat ON fabric.cat_id=cat.cat_id WHERE fabric.fabric_balance <> 0 GROUP BY cat.cat_name_en, fabric.cat_id ORDER BY cat.cat_name_en ASC");
    
        return response()->json($materialRequest);
    }

    public function updateFabricStatus($rq_id, $fabric_id_list)
    {
        

        $a_fabric_id_old = DB::table('tbl_rq_form_item')
            ->where('rq_id', $rq_id)
            ->where('mark_cut_stock', '0')
            ->pluck('fabric_id')
            ->toArray();

        $a_fabric_id_new = explode(",", $fabric_id_list);

        $a_delete_id = array_diff($a_fabric_id_old, $a_fabric_id_new);
        $a_insert_id = array_diff($a_fabric_id_new, $a_fabric_id_old);

        if (!empty($a_delete_id)) {
            DB::table('fabric')
                ->whereIn('fabric_id', $a_delete_id)
                ->update(['on_producing' => 0]);

            DB::table('tbl_rq_form_item')
                ->where('rq_id', $rq_id)
                ->where('mark_cut_stock', '0')
                ->whereIn('fabric_id', $a_delete_id)
                ->delete();
        }


        if (!empty($a_insert_id)) {
            DB::table('fabric')
                ->whereIn('fabric_id', $a_insert_id)
                ->update(['on_producing' => 1]);

            foreach ($a_insert_id as $fabric_id) {
                $balance_before = DB::table('fabric')
                    ->where('fabric_id', $fabric_id)
                    ->value('fabric_balance');
            if ($balance_before !== null) {
                DB::table('tbl_rq_form_item')->insert([
                    'rq_id' => $rq_id,
                    'fabric_id' => $fabric_id,
                    'balance_before' => $balance_before
                ]);
            }
            }
        }

        return response()->json(['result' => 'success'], 200);
    }

    public function finishRq(Request $request)
    {        
        if ($request->filled('finish_rq_id')) {
            $finish_rq_id = $request->input('finish_rq_id');

            DB::table('tbl_rq_form')
                ->where('rq_id', $finish_rq_id)
                ->update([
                    'rq_status' => 'finish',
                    'finish_date' => now()
                ]);

            $order_code = DB::table('tbl_rq_form')
                ->where('rq_id', $finish_rq_id)
                ->value('order_code');

            DB::table('forecast_head')
                ->where('forecast_order', $order_code)
                ->update(['is_produced' => 1]);

            return response()->json(['result' => 'success'], 200);
        } else {
            return response()->json(['result' => 'Invalid parameter.'], 400);
        }
    }

    public function addRq(Request $request)
    {        
        $employee_id = $request->user()->employee_id;
        $order_lkr_title_id = $request->input("order_lkr_title_id");
        $order_code = $request->input("order_code");
        $fabric_id_list = $request->input("fabric_id_list");

        // Inserting data into tbl_rq_form
        $rq_id = DB::table('tbl_rq_form')->insertGetId([
            'order_lkr_title_id' => $order_lkr_title_id,
            'order_code' => $order_code,
            'rq_date' => now(),
            'employee_id' => $employee_id
        ]);

        // Exploding fabric_id_list
        $tmp_fabric_id = explode(",", $fabric_id_list);

        // Processing each fabric_id
        foreach ($tmp_fabric_id as $fabric_id) {
            // Fetching fabric balance
            $row_balance = DB::table('fabric')->select('fabric_balance')->where('fabric_id', $fabric_id)->first();

            // Inserting data into tbl_rq_form_item
            DB::table('tbl_rq_form_item')->insert([
                'rq_id' => $rq_id,
                'fabric_id' => $fabric_id,
                'balance_before' => $row_balance->fabric_balance
            ]);
        }

        // Updating tbl_order_lkr_title
        DB::table('tbl_order_lkr_title')->where('order_lkr_title_id', $order_lkr_title_id)->update([
            'to_producing' => 1
        ]);

        // Updating tbl_order_lkr_title for to_forecast
        DB::table('tbl_order_lkr_title')->where('order_lkr_title_id', $order_lkr_title_id)
            ->where('to_forecast', 0)
            ->update(['to_forecast' => 2]);

        // Updating fabric table for on_producing
        DB::table('fabric')->whereIn('fabric_id', $tmp_fabric_id)->update(['on_producing' => 1]);

        // Returning success response
        return response()->json(['result' => 'success']);
    }


    public function orderTitle(Request $request){

        $orderCodes = DB::table('tbl_order_lkr_title')
                    ->where('enable', 1)
                    ->where('add_date', '>=', '2022-01-01 00:00:00')
                    ->orderBy('order_title', 'ASC')
                    ->get();

        $options = [];
        foreach ($orderCodes as $orderCode) {
            $option = [
                'id' => $orderCode->order_lkr_title_id,
                'value' => $orderCode->order_title . ',' . $orderCode->folder_name . ',' . $orderCode->order_lkr_title_id . ',' . $orderCode->to_forecast,
                'code' => $orderCode->order_title
            ];
            if ($orderCode->to_forecast == "1") {
                $option['code'] .= ' *';
            }
            if ($orderCode->have_order_form == "no") {
                $option['class'] = 'bg-red';
            }
            $options[] = $option;
        }

        return response()->json($options);

    }

}
