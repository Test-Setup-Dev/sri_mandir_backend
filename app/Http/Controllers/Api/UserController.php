<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Helpers\Logger;
use App\Helpers\LogType;
use App\Helpers\SendGridHelper;




class UserController extends Controller
{
    /**
     * User Registration API
     */
    public function register(Request $request)
    {
    
        // ✅ Validation rules
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'required|string|max:20|unique:users,phone',
            'password'      => 'required|min:6',
            'date_of_birth' => 'nullable|date',
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:255',
            'pincode'       => 'nullable|string|max:20',
            'state'         => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'gender'        => 'nullable|in:male,female,other',
            'image'         => 'nullable|url',
            'fcm_token'     => 'required|string',
        ]);

        // ❌ If validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // ✅ Create user
        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'image'         => $request->image,
            'date_of_birth' => $request->date_of_birth,
            'city'          => $request->city,
            'address'       => $request->address,
            'pincode'       => $request->pincode,
            'state'         => $request->state,
            'country'       => $request->country,
            'gender'        => $request->gender,
            'fcm_token'     => $request->fcm_token,
            'token'         => bin2hex(random_bytes(20)), // generate random token
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'User registered successfully',
            'user'    => $user,
        ], 201);
    }


//login function
public function login(Request $request)
{
    try {
        // ✅ Validate input fields
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
            'fcm_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // ✅ Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        
        $user->tokens()->delete();

        // ✅ Generate API token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ Update token and FCM token
        $user->update([
            'token' => $token,
            'fcm_token' => $request->fcm_token, // 🔥 Update FCM token here
        ]);

        // ✅ Success response
        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
        ], 200);

    } catch (\Exception $e) {
        // ⚠️ Catch unexpected errors
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong. Please try again later.',
            'error'   => $e->getMessage(), // remove in production
        ], 500);
    }
}


// social login function
public function socialLogin(Request $request)
{
    //************LOG START************//
    Logger::$logType = LogType::MESSAGE;
    Logger::$className = __CLASS__;
    Logger::$methodName = __METHOD__;
    Logger::$lineNo = __LINE__;
    Logger::$tag = 'SOCIAL LOGIN';
    Logger::$message = '';
    Logger::$extra = $request->all(); // request data
    Logger::print();
    //************LOG END************//

    $socialId = $request->input('socialId');
    $authType = $request->input('authType');
    $name = $request->input('name');
    $email = $request->input('email');
    $profilePic = $request->input('profilePic');
    $acType = $request->input('acType');

    $user = null;

    if ($authType == 'GOOGLE') {
        $user = User::where('email', $email)->first();

        if ($user != null && $user->auth_type != 'GOOGLE') {
            return ['success' => false, 'message' => 'Account already exist for this email id'];
        }

    } else if (in_array($authType, ['FACEBOOK', 'APPLE'])) {
        $user = User::where('social_id', $socialId)->first(); // findBySocialId alternative
    }

    // Create New if null
    if ($user == null) {
        $user = new User();
        $user->activated = 1;

        // SendGridHelper::addContactByCurl($email); // SKIPPED
    }

    $user->social_id = $socialId;
    $user->auth_type = $authType;
    $user->name = $name;
    $user->email = $email;
    $user->profile_pic = $profilePic;
    $user->about = "I am a $acType";
    $user->ac_type = $acType;
    $user->email_verified = ($authType == 'GOOGLE' ? '1' : '0');

    if ($user->save()) {
        $user->saveAuthToken();
        $user->success = true;
        $user->gender = '';
        return $user;
    } else {
        return ['success' => false, 'message' => 'Social Login Failed! Please try again later or contact support'];
    }
}




// get profile function
public function profile(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile fetched successfully',
            'data' => $user,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again later.',
            'error' => $e->getMessage(), // remove in production
        ], 500);
    }
}


//update profile function
public function updateProfile(Request $request)
{
    try {
        $user = $request->user(); // Authenticated user

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        // ✅ Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15',
            'date_of_birth' => 'nullable|date',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'pincode' => 'nullable|string|max:10',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // ✅ Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/users'), $imageName);
            $user->image = url('/public/uploads/users/' . $imageName);
        }

        // ✅ Update user fields
        $user->fill($request->only([
            'name',
            'phone',
            'date_of_birth',
            'city',
            'address',
            'pincode',
            'state',
            'country',
            'gender'
        ]));

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $user,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again later.',
            'error' => $e->getMessage(), // remove in production
        ], 500);
    }
}
 

// forget password function
public function forgetPassword(Request $request)
{
    try {
        // Validate email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Get user
        $user = \App\Models\User::where('email', $request->email)->first();

        // Generate OTP
        $otp = rand(100000, 999999);

        // Save OTP in DB
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        // Get SMTP settings from DB table (assuming you have smtp table)
        $smtp = DB::table('smtp_settings')->first(); // adjust table name

        config([
            'mail.mailers.smtp.host' => $smtp->host,
            'mail.mailers.smtp.port' => $smtp->port,
            'mail.mailers.smtp.username' => $smtp->username,
            'mail.mailers.smtp.password' => $smtp->password,
            'mail.mailers.smtp.encryption' => $smtp->encryption,
            'mail.from.address' => $smtp->from_email,
            'mail.from.name' => $smtp->from_name,
        ]);

        // Send OTP email
        Mail::raw("Your OTP for password reset is: $otp. It is valid for 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Password Reset OTP');
        });

        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again later.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


// reset password function
public function resetPassword(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
            'password' => 'required|min:6|confirmed', // expects password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = \App\Models\User::where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        // Check OTP expiry
        if (now()->gt($user->otp_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP has expired.',
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->password);

        // Clear OTP
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Password has been reset successfully.',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again later.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

//logout function
public function logout(Request $request)
{
    try {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        // ✅ Delete current token (Sanctum)
        $user->currentAccessToken()->delete();

        // ✅ Optionally clear token from users table
        $user->update(['token' => null]);

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong. Please try again later.',
            'error' => $e->getMessage(), // remove in production
        ], 500);
    }
}

public function index() {
        return view('admin.users');
    }
}