<?php

namespace App\Http\Controllers\Teams;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\IInvitation;

class InvitationsController extends Controller
{
    protected $invitations;

    public function __construct(IInvitation $invitations)
    {
        $this->invitations = $invitations;
    }

    public function invite(Request $request, $teamId)
    {
        # code...
    }

    public function resend($id)
    {
        # code...
    }

    public function response(Request $request, $id)
    {
        # code...
    }

    public function destroy($id)
    {
        # code...
    }
}
