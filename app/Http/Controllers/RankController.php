<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use App\Models\RankRequest;
use Illuminate\Http\Request;

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

        return view('ranks.requests', [
            'requests' => $requests,
        ]);
    }

    public function newRequest(Request $request) {
        $request->validate([
            'rank_id' => 'required|exists:ranks,id',
            'user_to_promote_id' => 'required|exists:users,id',
            'reason' => 'required',
        ]);

        RankRequest::create([
            'user_id' => auth()->id(),
            'rank_id' => $request->rank_id,
            'user_to_promote_id' => $request->user_to_promote_id,
            'status' => 'pending',
            'reason' => $request->reason,
        ]);

        return redirect()->route('users.edit', $request->user_to_promote_id)->with('success', 'Rank promotion request submitted.');
    }

    public function acceptRequest(RankRequest $request) {

        $user = $request->userToPromote;
        $user->rank_id = $request->rank_id;

        $request->status = 'approved';
        $request->approved_by = auth()->id();
        $request->approved_at = now();
        $request->save();

        return redirect()->route('ranks.requests');
    }

    public function acceptAllRequests() {
        $requests = RankRequest::where('status', 'pending')->get();

        foreach ($requests as $request) {
            $user = $request->userToPromote;
            $user->rank_id = $request->rank_id;

            $request->status = 'approved';
            $request->approved_by = auth()->id();
            $request->approved_at = now();
            $request->save();
        }


        return redirect()->route('ranks.requests');
    }

    public function rejectRequest(RankRequest $request) {
        $request->status = 'rejected';
        $request->approved_by = auth()->id();
        $request->approved_at = now();
        $request->save();

        return redirect()->route('ranks.requests');
    }

    public function deleteRequest(RankRequest $request) {

        if ($request->status === 'pending') {
            $request->delete();

            return redirect()->route('ranks.requests');
        } else {
            return redirect()->route('ranks.requests')->with('error', 'Cannot delete a request that has already been approved or rejected.');
        }
    }
}
