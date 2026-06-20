<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MediaItem;
use App\Models\Donation;
use App\Models\Blog;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function getStats()
    {
        try {
            $totalUsers = User::count();
            $totalMedia = MediaItem::count();
            $totalDonations = Donation::where('status', 'completed')->sum('amount');
            $totalBlogs = Blog::count();

            // Trends
            $userGrowth = $this->getGrowth(User::class);
            $mediaGrowth = $this->getGrowth(MediaItem::class);
            $blogGrowth = $this->getGrowth(Blog::class);
            $donationGrowth = $this->getDonationGrowth();

            return response()->json([
                'status' => true,
                'data' => [
                    'stats' => [
                        [
                            'label' => 'Total Users',
                            'value' => number_format($totalUsers),
                            'trend' => ($userGrowth >= 0 ? '+' : '') . round($userGrowth, 1) . '%',
                            'trendUp' => $userGrowth >= 0,
                            'color' => '#8b5cf6'
                        ],
                        [
                            'label' => 'Media Items',
                            'value' => number_format($totalMedia),
                            'trend' => ($mediaGrowth >= 0 ? '+' : '') . round($mediaGrowth, 1) . '%',
                            'trendUp' => $mediaGrowth >= 0,
                            'color' => '#f59e0b'
                        ],
                        [
                            'label' => 'Total Donations',
                            'value' => '₹' . number_format($totalDonations),
                            'trend' => ($donationGrowth >= 0 ? '+' : '') . round($donationGrowth, 1) . '%',
                            'trendUp' => $donationGrowth >= 0,
                            'color' => '#10b981'
                        ],
                        [
                            'label' => 'Total Blogs',
                            'value' => number_format($totalBlogs),
                            'trend' => ($blogGrowth >= 0 ? '+' : '') . round($blogGrowth, 1) . '%',
                            'trendUp' => $blogGrowth >= 0,
                            'color' => '#ec4899'
                        ]
                    ],
                    'recentDonations' => Donation::where('status', 'completed')
                        ->with('user')
                        ->latest()
                        ->take(5)
                        ->get()
                        ->map(function($d) {
                            return [
                                'id' => $d->id,
                                'donor' => $d->user->name ?? 'Guest',
                                'amount' => '₹' . number_format($d->amount),
                                'status' => 'Completed',
                                'date' => $d->created_at->diffForHumans()
                            ];
                        }),
                    'popularMedia' => MediaItem::withCount('favorites')
                        ->orderBy('favorites_count', 'desc')
                        ->take(3)
                        ->get()
                        ->map(function($m) {
                            return [
                                'id' => $m->id,
                                'title' => $m->title,
                                'views' => '0', // Views still placeholder as not tracked yet
                                'likes' => number_format($m->favorites_count),
                                'thumbnail' => $m->thumbnailUrl
                            ];
                        })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getGrowth($model, $days = 30)
    {
        $current = $model::where('created_at', '>=', now()->subDays($days))->count();
        $previous = $model::where('created_at', '>=', now()->subDays($days * 2))
                          ->where('created_at', '<', now()->subDays($days))
                          ->count();
        
        if ($previous > 0) {
            return (($current - $previous) / $previous) * 100;
        }
        return $current > 0 ? 100 : 0;
    }

    private function getDonationGrowth($days = 30)
    {
        $current = Donation::where('status', 'completed')->where('created_at', '>=', now()->subDays($days))->sum('amount');
        $previous = Donation::where('status', 'completed')
                          ->where('created_at', '>=', now()->subDays($days * 2))
                          ->where('created_at', '<', now()->subDays($days))
                          ->sum('amount');
        
        if ($previous > 0) {
            return (($current - $previous) / $previous) * 100;
        }
        return $current > 0 ? 100 : 0;
    }

    public function getUsers()
    {
        try {
            $users = User::latest()->paginate(20);
            return response()->json([
                'status' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
