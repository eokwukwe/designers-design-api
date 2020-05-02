<?php

namespace App\Http\Controllers\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use App\Mail\SendInvitationToJoinTeam;
use App\Repositories\Contracts\IInvitation;

class InvitationsController extends Controller
{
    protected $invitations;
    protected $teams;
    protected $users;

    public function __construct(
        IInvitation $invitations,
        ITeam $teams,
        IUser $users
    ) {
        $this->invitations = $invitations;
        $this->teams = $teams;
        $this->users = $users;
    }

    public function invite(Request $request, $teamId)
    {
        // get the team
        $team = $this->teams->find($teamId);
        $this->validate($request, [
            'email' => ['required', 'email']
        ]);

        $user = auth()->user();

        // check if user owns the team
        if (!$user->isOwnerOfTeam($team)) {
            return response()->json([
                'message' => 'You must be a team owner to send invitation'
            ], 401);
        }

        // check if the email has a pending invitation
        if ($team->hasPendingInvite($request->email)) {
            return response()->json([
                'message' => 'This email already has a pending invitation'
            ], 422);
        }

        // get the recipient by email
        $recipient = $this->users->findByEmail($request->email);

        // if recipient doesn't exist, send invitation join the team
        if (!$recipient) {
            $this->createInvitation(false, $team, $request->email);

            return response()->json([
                'message' => 'Invitation sent to user'
            ], 200);
        }

        // if recipient, check if the team already has the recipient (user)
        if ($team->hasUser($recipient)) {
            return response()->json([
                'message' => 'This user is already a member of the team'
            ], 422);
        }

        // send invitation
        $this->createInvitation(true, $team, $recipient->email);

        return response()->json([
            'message' => 'Invitation sent to user'
        ], 200);

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

    protected function createInvitation(
        bool $user_exits,
        Team $team,
        string $email
    ) {
        $invitation = $this->invitations->create([
            'team_id' => $team->id,
            'sender_id' => auth()->id(),
            'recipient_email' => $email,
            'token' => md5(uniqid(microtime())),
        ]);

        // send mail to recipient
        Mail::to($email)
            ->send(new SendInvitationToJoinTeam($invitation, $user_exits));
    }
}
