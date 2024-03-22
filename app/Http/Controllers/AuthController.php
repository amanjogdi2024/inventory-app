<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
    
        $employee = Employee::where('employee_email', $email)->first();
    
        if ($employee && md5($password) == ($employee->employee_password)) {
            // Debugging: Ensure the user ID is not null           
          
            $token = $employee->createToken('YourAppName')->plainTextToken;
    
            return response()->json([
                'accessToken' => $token,
                'token_type' => 'Bearer',
                'message' => 'Login successful',
                'employee' => $employee
            ]);
        }
    
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function logout(Request $request)
    {
        // Revoke the current user's access token
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }


    public function changePassword(Request $request)
    {
        
        $current_password = $request->input('current_password');
        $new_password = $request->input('new_password');

    
        $user =  $request->user();

        if (md5($current_password) != ($user->employee_password)) {
            
            return response()->json(['message' => 'The current password is incorrect.'], 200);
        }

        $user->employee_password = md5($new_password);
        $user->employee_auth = $new_password;
        $user->save();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|',
            'telephone' => 'required|int|',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Add other fields as needed
        ]);

        if ($validator->fails()) {
            
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user->employee_name = $request->name;
        $user->employee_email = $request->email;
        $user->employee_tel = $request->telephone;

        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();  
            $request->image->move(public_path(), $imageName);
            $user->employee_image = $imageName;;
        }
        
        // Update other fields as needed
        $user->save();

        return response()->json(['message' => 'Profile updated successfully'], 200);
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new ValidationException($validator, response()->json($validator->errors(), 422));
    }
}
