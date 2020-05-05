<?php

namespace App\Http\Controllers\Teams;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DesignResource;
use App\Http\Resources\TeamResource;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use App\Repositories\Contracts\IInvitation;

class TeamsController extends Controller
{
    protected $teams;
    protected $users;
    protected $invitations;

    public function __construct(
        ITeam $teams,
        IUser $users,
        IInvitation $invitations
    ) {
        $this->teams = $teams;
        $this->users = $users;
        $this->invitations = $invitations;
    }

    /**
     * Get all teams
     */
    public function index(Request $request)
    {
        # code...
    }

    /**
     * Create a new team
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:80', 'unique:teams,name']
        ]);

        $team = $this->teams->create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }

    /**
     * Update a team
     */
    public function update(Request $request, $id)
    {
        $team = $this->teams->find($id);
        $this->authorize('update', $team);

        $this->validate($request, [
            'name' => [
                'required', 'string', 'max:80', 'unique:teams,name,' . $id
            ]
        ]);

        $team = $this->teams->update($id, [
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return new TeamResource($team);
    }

    /**
     * Get a team by ID
     */
    public function findById($id)
    {
        $team = $this->teams->find($id);
        return new TeamResource($team);
    }

    /**
     * Get the teams current user belongs to
     */
    public function getUserTeams()
    {
        return TeamResource::collection(
            $this->teams->getUserTeams()
        );
    }
    /**
     * Get team by slug for public view
     */
    public function findBySlug($slug)
    {
    }
    /**
     * Delete a team
     */
    public function destroy($id)
    {
        $team = $this->teams->find($id);
        $this->authorize('delete', $team);

        $this->teams->delete($id);

        return response()->json([
            'message' => 'Record deleted successfully'
        ], 200);
    }

    /**
     * Remove user from team
     */
    public function removeFromTeam($teamId, $userId)
    {
        $team = $this->teams->find($teamId);
        $user = $this->users->find($userId);

        // check that logged in user is not the owner
        if ($user->isOwnerOfTeam($team)) {
            return response()->json([
                'message' => 'You are the team owner. You cannot remove yourself from the team'
            ], 401);
        }

        // check that the person sending the request is either
        // the team owner or the person who want to leave the team
        if (
            !auth()->user()->isOwnerOfTeam($team) &&
            auth()->id() !== $user->id
        ) {
            return response()->json([
                'message' => 'You are not allow to perform this action'
            ], 401);
        }

        $this->invitations->removeUserFromTeam($team, $userId);

        return response()->json([
            'message' => 'User removed from team'
        ], 200);
    }
}
