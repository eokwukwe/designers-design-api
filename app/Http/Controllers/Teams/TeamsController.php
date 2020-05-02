<?php

namespace App\Http\Controllers\Teams;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Repositories\Contracts\ITeam;

class TeamsController extends Controller
{
    protected $teams;

    public function __construct(ITeam $teams)
    {
        $this->teams = $teams;
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
    public function update(Request $request)
    {
        # code...
    }

    /**
     * Get a team by ID
     */
    public function findById($id)
    {
        # code...
    }

    /**
     * Get the teams current user belongs to
     */
    public function getUserTeams()
    {
        # code...
    }
    /**
     * Get team by slug for public view
     */
    public function findBySlug($slug)
    {
        # code...
    }
    /**
     * Delete a team
     */
    public function destroy($id)
    {
        # code...
    }
}
