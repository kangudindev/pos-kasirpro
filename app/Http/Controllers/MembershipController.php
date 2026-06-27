<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact\Contact;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionPayment;
use Illuminate\Support\Facades\DB;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $businessId = session('current_business_id');

        $members = DB::table('memberships')
            ->where('memberships.business_id', $businessId)
            ->join('contacts', 'memberships.contact_id', '=', 'contacts.id')
            ->select('memberships.*', 'contacts.name', 'contacts.mobile', 'contacts.email')
            ->latest()
            ->paginate(20);

        return view('membership.index', compact('members'));
    }

    public function create()
    {
        $businessId = session('current_business_id');
        $customers = Contact::where('business_id', $businessId)->customers()->get();

        return view('membership.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
            'tier' => 'required|in:bronze,silver,gold,platinum',
        ]);

        $businessId = session('current_business_id');
        $membershipNo = 'MBR-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('memberships')->insert([
            'business_id' => $businessId,
            'contact_id' => $request->contact_id,
            'membership_no' => $membershipNo,
            'points_balance' => 0,
            'tier' => $request->tier,
            'expires_at' => $request->expires_at,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('memberships.index')
            ->with('success', "Member registered! No: {$membershipNo}");
    }

    public function show($id)
    {
        $member = DB::table('memberships')
            ->where('id', $id)
            ->join('contacts', 'memberships.contact_id', '=', 'contacts.id')
            ->select('memberships.*', 'contacts.name', 'contacts.mobile', 'contacts.email')
            ->first();

        $pointHistory = DB::table('reward_points')
            ->where('contact_id', $member->contact_id)
            ->latest()
            ->get();

        $totalSpent = Transaction::where('contact_id', $member->contact_id)
            ->where('type', 'sell')
            ->sum('final_total');

        return view('membership.show', compact('member', 'pointHistory', 'totalSpent'));
    }

    public function addPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string',
        ]);

        $member = DB::table('memberships')->where('id', $id)->first();

        // Add points
        DB::table('memberships')
            ->where('id', $id)
            ->increment('points_balance', $request->points);

        // Log points
        DB::table('reward_points')->insert([
            'business_id' => $member->business_id,
            'contact_id' => $member->contact_id,
            'points' => $request->points,
            'type' => 'earn',
            'description' => $request->description ?? 'Points earned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Auto upgrade tier based on points
        $this->updateTier($id);

        return redirect()->route('memberships.show', $id)
            ->with('success', "{$request->points} points added!");
    }

    public function redeemPoints(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $member = DB::table('memberships')->where('id', $id)->first();

        if ($member->points_balance < $request->points) {
            return back()->with('error', 'Insufficient points!');
        }

        // Deduct points
        DB::table('memberships')
            ->where('id', $id)
            ->decrement('points_balance', $request->points);

        // Log points
        DB::table('reward_points')->insert([
            'business_id' => $member->business_id,
            'contact_id' => $member->contact_id,
            'points' => $request->points,
            'type' => 'redeem',
            'description' => $request->description ?? 'Points redeemed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('memberships.show', $id)
            ->with('success', "{$request->points} points redeemed!");
    }

    protected function updateTier($membershipId)
    {
        $member = DB::table('memberships')->where('id', $membershipId)->first();

        $tier = match(true) {
            $member->points_balance >= 10000 => 'platinum',
            $member->points_balance >= 5000 => 'gold',
            $member->points_balance >= 2000 => 'silver',
            default => 'bronze',
        };

        DB::table('memberships')
            ->where('id', $membershipId)
            ->update(['tier' => $tier, 'updated_at' => now()]);
    }
}
