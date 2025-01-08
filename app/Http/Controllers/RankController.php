<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use App\Models\RankRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RankController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Rank $rank) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rank $rank) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rank $rank) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rank $rank) {
        //
    }

    public function requests() {

        $requests = RankRequest::with('requestedBy', 'rank', 'userToPromote', 'approvedBy')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $reduced = $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'requested_by' => $request->requestedBy->name . ' ' . $request->requestedBy->surname,
                'rank' => __('users.' . strtolower($request->rank->name)),
                'user_to_promote' => $request->userToPromote->name . ' ' . $request->userToPromote->surname,
                'reason' => $request->reason,
                'created_at' => $request->created_at->format('d/m/Y'),
            ];
        })->toArray();

        return view('ranks.requests', [
            'requests' => $reduced,
        ]);
    }

    public function rankRequestForm() {
        $ranks = Rank::all();
        $authUserRole = User::find(auth()->user()->id)->getRole();

        if ($authUserRole === 'instructor') {
            $authSchools = auth()->user()->schools->pluck('id')->toArray();

            $users = User::whereHas('schools', function ($query) use ($authSchools) {
                $query->whereIn('school_id', $authSchools);
            })->get();
        }

        $formattedRanks = [];

        foreach ($ranks as $rank) {
            $formattedRanks[] = [
                'value' => $rank->id,
                'label' => __('users.' . strtolower($rank->name)),
            ];
        }

        return view('ranks.create-request', [
            'ranks' => $formattedRanks,
            'users' => $users ?? User::all(),
        ]);
    }

    public function rankRequestFormUser(User $user) {



        $ranks = Rank::all();
        $authUserRole = User::find(Auth::user()->id)->getRole();

        if ($authUserRole === 'instructor') {
            $authSchools = Auth::user()->schools->pluck('id')->toArray();

            $users = User::whereHas('schools', function ($query) use ($authSchools) {
                $query->whereIn('school_id', $authSchools);
            })->get();
        }

        $formattedRanks = [];

        foreach ($ranks as $rank) {
            $formattedRanks[] = [
                'value' => $rank->id,
                'label' => __('users.' . strtolower($rank->name)),
            ];
        }

        return view('ranks.create-request', [
            'ranks' => $formattedRanks,
            'users' =>  collect([$user])
        ]);
    }

    public function newRequest(Request $request) {
        $request->validate([
            'rank_id' => 'required|exists:ranks,id',
            'user_to_promote_id' => 'required|exists:users,id',
            'reason' => 'required',
        ]);

        RankRequest::create([
            'user_id' => Auth::user()->id,
            'rank_id' => $request->rank_id,
            'user_to_promote_id' => $request->user_to_promote_id,
            'status' => 'pending',
            'reason' => $request->reason,
        ]);


        return redirect()->route('users.rank.request')->with('success', 'Rank promotion request submitted.');
    }

    public function acceptRequest(RankRequest $request) {

        $user = User::find($request->userToPromote->id);
        $user->rank_id = $request->rank_id;
        $user->save();

        $request->status = 'approved';
        $request->approved_by = Auth::user()->id;
        $request->approved_at = now();
        $request->save();

        return redirect()->route('rank-requests.index');
    }

    public function acceptAllRequests() {
        $requests = RankRequest::where('status', 'pending')->get();

        foreach ($requests as $request) {
            $user = User::find($request->userToPromote->id);
            $user->rank_id = $request->rank_id;
            $user->save();

            $request->status = 'approved';
            $request->approved_by = Auth::user()->id;
            $request->approved_at = now();
            $request->save();
        }


        return redirect()->route('rank-requests.index');
    }

    public function rejectRequest(RankRequest $request) {
        $request->status = 'rejected';
        $request->approved_by = auth()->id();
        $request->approved_at = now();
        $request->save();

        return redirect()->route('rank-requests.index');
    }

    public function deleteRequest(RankRequest $request) {

        if ($request->status === 'pending') {
            $request->delete();

            return redirect()->route('rank-requests.index');
        } else {
            return redirect()->route('rank-requests.index')->with('error', 'Cannot delete a request that has already been approved or rejected.');
        }
    }

    public function countPendingRequests() {
        return RankRequest::where('status', 'pending')->count();
    }
}
