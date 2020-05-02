<?php

namespace App\Http\Controllers\Teams;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        # code...
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
