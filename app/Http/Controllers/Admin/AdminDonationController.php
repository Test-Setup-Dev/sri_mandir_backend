<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Donation;

class AdminDonationController extends Controller
{
    /**
     * Display a listing of all donations.
     */
    public function index(Request $request)
    {
        $query = Donation::with('user:id,name,email');

        if ($request->has('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $donations = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $donations
        ]);
    }
}
