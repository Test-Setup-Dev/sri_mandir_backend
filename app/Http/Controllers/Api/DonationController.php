<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donation;
use App\Models\User; // User model import
use App\Libraries\RazorpayApi;

class DonationController extends Controller
{
    public function createOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        
        $user = $request->user(); 
        if (!$user) {
            return response()->json(['status'=>false, 'message'=>'Unauthorized']);
        }

        $api = new RazorpayApi(config('services.razorpay.key'), config('services.razorpay.secret'));

        $orderData = [
            'receipt' => 'donate_' . time(),
            'amount' => $request->amount * 100, // paise
            'currency' => 'INR',
        ];

        $razorpayOrder = $api->createOrder($orderData);

        
        $donation = Donation::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'amount' => $request->amount,
            'currency' => 'INR',
            'order_id' => $razorpayOrder['id'],
        ]);

        return response()->json([
            'status' => true,
            'order_id' => $razorpayOrder['id'],
            'razorpay_key' => config('services.razorpay.key'),
            'amount' => $request->amount * 100,
            'currency' => 'INR',
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required',
            'razorpay_order_id' => 'required',
            'razorpay_signature' => 'required',
        ]);

        $api = new RazorpayApi(config('services.razorpay.key'), config('services.razorpay.secret'));

        try {
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            $api->verifyPaymentSignature($attributes);

            $donation = Donation::where('order_id', $request->razorpay_order_id)->first();
            if ($donation) {
                $donation->update([
                    'payment_id' => $request->razorpay_payment_id,
                    'status' => 'success',
                ]);
            }

            return response()->json(['status' => true, 'message' => 'Payment verified successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Payment verification failed', 'error' => $e->getMessage()]);
        }
    }

    // get user donations
public function getAllDonations(Request $request)
{
    try {
        // ✅ Get logged-in user from token
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Invalid or missing token.'
            ], 401);
        }

        // ✅ Fetch only this user's donations without user details
        $donations = Donation::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'user_id', 'amount', 'currency', 'created_at']);

        if ($donations->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No payment history found.',
                'donations' => []
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment history fetched successfully.',
            'donations' => $donations
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch donations',
            'error' => $e->getMessage()
        ]);
    }
}


// get today top donors
public function getTodayTopDonors()
{
    try {
        $today = now()->toDateString();

        // ✅ Fetch top 3 donors of today
        $topDonors = \App\Models\Donation::with('user:id,name,email,image,city')
            ->selectRaw('user_id, SUM(amount) as total_donated')
            ->whereDate('created_at', $today)
            ->where('status', 'success')
            ->groupBy('user_id')
            ->orderByDesc('total_donated')
            ->take(3)
            ->get();

        // ✅ Format output
        $result = $topDonors->map(function ($donor) {
            return [
                'name' => $donor->user->name ?? null,
                'email' => $donor->user->email ?? null,
                'image' => $donor->user->image ?? null,
                'city' => $donor->user->city ?? null,
                'today_total_donation' => $donor->total_donated,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Top 3 donors fetched successfully.',
            'data' => $result
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}


}

