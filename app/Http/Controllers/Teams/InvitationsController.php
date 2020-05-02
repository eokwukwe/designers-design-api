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
        $invitation = $this->invitations->find($id);

        // check if user owns the team
        $this->authorize('resend', $invitation);

        // get the recipient by email
        $recipient = $this->users->findByEmail($invitation->recipient_email);

        // resent invitation email
        Mail::to($recipient->email)
            ->send(new SendInvitationToJoinTeam(
                $invitation,
                !is_null($recipient)
            ));

        return response()->json([
            'message' => 'Invitation resent successfully'
        ], 200);
    }

    public function respond(Request $request, $id)
    {
        $this->validate($request, [
            'token' => ['required'],
            'decision' => ['required']
        ]);

        $invitation = $this->invitations->find($id);

        // check if invitation belongs to current user
        $this->authorize('respond', $invitation);

        // check for token match
        if ($invitation->token !== $request->token) {
            return response()->json([
                'message' => 'Invalid token'
            ], 401);
        }

        // check for acceptance
        if ($request->decision !== 'deny') {
            $this->invitations->addUserToTeam(
                $invitation->team,
                auth()->id()
            );
        }

        // delete invitation from the database
        $invitation->delete();

        return response()->json([
            'message' => 'Successful'
        ], 200);
    }

    public function destroy($id)
    {
        $invitation = $this->invitations->find($id);

        // check if user is the sender of the invitation
        $this->authorize('delete', $invitation);

        // delete invitation from the database
        $invitation->delete();

        return response()->json([
            'message' => 'Deleted'
        ], 200);
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
