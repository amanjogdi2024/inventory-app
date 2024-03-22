<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee; // Make sure to import the Employee model
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;


class EmployeeController extends Controller
{
    // Method to fetch all employees
    public function index()
    {
        //$employees = DB::table('employee')->get();
        $employees = Employee::all();
        return response()->json($employees);
    }

    // Method to fetch a specific employee by ID
    public function show($id)
    {
        $employee = Employee::where('employee_id', $id)->first();;
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 404);
        }
        return response()->json($employee);
    }

    public function showPosition(Request $request)
    {
        $user  =   $request->user();

        if ($user) {
            return response()->json(['employee_position_id' => $user['employee_position_id']], 200);
        } else {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
    }

    public function geoposition(Request $request)
    {   
        $latitude = $request->header('X-Latitude');
        $longitude = $request->header('X-Longitude');
       
        echo "sknmdkfsnlfnlsdnfl";
        die;


        $user  =   $request->user();

        if ($user) {
            return response()->json(['employee_position_id' => $user['employee_position_id']], 200);
        } else {
            return response()->json(['error' => 'Unauthenticatedsadasdsda'], 401);
        }
    }
    
}
