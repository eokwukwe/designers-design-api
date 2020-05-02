<?php

namespace App\Repositories\Eloquent;

use App\Models\Team;
use App\Repositories\Contracts\ITeam;

class TeamRepository extends BaseRepository implements ITeam
{
    public function model()
    {
        return Team::class;  // === return 'App\Models\Team'
    }

    public function getUserTeams()
    {
        return auth()->user()->teams;
    }
}
