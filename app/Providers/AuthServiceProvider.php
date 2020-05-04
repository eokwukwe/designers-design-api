<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\Design;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Invitation;
use App\Policies\TeamPolicy;
use App\Policies\DesignPolicy;
use App\Policies\CommentPolicy;
use App\Policies\MessagePolicy;
use App\Policies\InvitationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Team::class => TeamPolicy::class,
        Design::class => DesignPolicy::class,
        Comment::class => CommentPolicy::class,
        Message::class => MessagePolicy::class,
        Invitation::class => InvitationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
