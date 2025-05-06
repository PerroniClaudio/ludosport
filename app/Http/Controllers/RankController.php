<?php

namespace App\Http\Controllers;

use App\Models\Academy;
use App\Models\Rank;
use App\Models\RankRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RankController extends Controller {

    private function fetchRequests($authUserRole, $allowedRanks, $authEntities, $entityType) {
        $users = [];

        foreach ($authEntities as $entityId) {
            $entity = $entityType::find($entityId);
            $users = array_merge($users, $entity->athletes->toArray(), $entity->personnel->toArray());
        }

        $user_ids = collect($users)->pluck('id')->toArray();

        return RankRequest::where('status', 'pending')
            ->whereIn('user_to_promote_id', $user_ids)
            ->whereIn('rank_id', $allowedRanks)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function requests() {
        $authUserRole = User::find(Auth::user()->id)->getRole();

        if ($authUserRole === 'dean') {
            $authSchools = Auth::user()->schools->pluck('id')->toArray();
            $allowedRanks = Rank::whereIn('name', ['Initiate'])->pluck('id')->toArray();
            $requests = $this->fetchRequests($authUserRole, $allowedRanks, $authSchools, School::class);
        } else if ($authUserRole === 'rector') {
            $authAcademies = Auth::user()->academies->pluck('id')->toArray();
            $allowedRanks = Rank::whereIn('name', ['Initiate', 'Academic'])->pluck('id')->toArray();
            $requests = $this->fetchRequests($authUserRole, $allowedRanks, $authAcademies, Academy::class);
        } else {
            $requests = RankRequest::with('requestedBy', 'rank', 'userToPromote', 'approvedBy')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        }

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

    public function acceptAllRequests() {
        $authUserRole = User::find(Auth::user()->id)->getRole();

        if ($authUserRole === 'dean') {
            $authSchools = Auth::user()->schools->pluck('id')->toArray();
            $allowedRanks = Rank::whereIn('name', ['Initiate'])->pluck('id')->toArray();
            $requests = $this->fetchRequests($authUserRole, $allowedRanks, $authSchools, School::class);
        } else if ($authUserRole === 'rector') {
            $authAcademies = Auth::user()->academies->pluck('id')->toArray();
            $allowedRanks = Rank::whereIn('name', ['Initiate', 'Academic'])->pluck('id')->toArray();
            $requests = $this->fetchRequests($authUserRole, $allowedRanks, $authAcademies, Academy::class);
        } else {
            $requests = RankRequest::with('requestedBy', 'rank', 'userToPromote', 'approvedBy')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        }

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

    public function countPendingRequests() {
        $authUserRole = User::find(Auth::user()->id)->getRole();
        $pending_requests = 0;

        if ($authUserRole === 'dean') {
            $authSchools = Auth::user()->schools->pluck('id')->toArray();
            $allowedRanks = Rank::whereIn('name', ['Initiate'])->pluck('id')->toArray();
            $pending_requests = $this->fetchRequests($authUserRole, $allowedRanks, $authSchools, School::class)->count();
        } else if ($authUserRole === 'rector') {
            $authAcademies = Auth::user()->academies->pluck('id')->toArray();
            $allowedRanks = Rank::whereIn('name', ['Initiate', 'Academic'])->pluck('id')->toArray();
            $pending_requests = $this->fetchRequests($authUserRole, $allowedRanks, $authAcademies, Academy::class)->count();
        } else {
            $pending_requests = RankRequest::where('status', 'pending')->count();
        }

        return $pending_requests;
    }

    public function rankRequestForm() {

        $authUserRole = User::find(Auth::user()->id)->getRole();
        $users = [];

        if (in_array($authUserRole, ['instructor', 'dean'])) {

            // Solo iniziato ed accademico 
            $ranks = Rank::whereIn('name', ['Initiate', 'Academic'])->get();
            $authSchools = Auth::user()->schools->pluck('id')->toArray();

            foreach ($authSchools as $school) {
                $school = School::find($school);
                $users = array_merge($users, $school->athletes->toArray(), $school->personnel->toArray());
            }
        } else if ($authUserRole === 'rector') {

            // Anche cavaliere
            $ranks = Rank::whereIn('name', ['Initiate', 'Academic', 'Chevalier'])->get();
            $authAcademies = Auth::user()->academies->pluck('id')->toArray();

            foreach ($authAcademies as $academy) {
                $academy = Academy::find($academy);
                $users = array_merge($users, $academy->athletes->toArray(), $academy->personnel->toArray());
            }
        } else {

            $ranks = Rank::whereIn('name', ['Initiate', 'Academic'])->get();

            $authSchools = Auth::user()->schools->pluck('id')->toArray();

            foreach ($authSchools as $school) {
                $school = School::find($school);
                $users = array_merge($users, $school->athletes->toArray(), $school->personnel->toArray());
            }

            $authAcademies = Auth::user()->academies->pluck('id')->toArray();

            foreach ($authAcademies as $academy) {
                $academy = Academy::find($academy);
                $users = array_merge($users, $academy->athletes->toArray(), $academy->personnel->toArray());
            }
        }

        $users = collect($users)->unique('id');

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

    public function rejectRequest(RankRequest $request) {
        $request->status = 'rejected';
        $request->approved_by = Auth::user()->id;
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
}
