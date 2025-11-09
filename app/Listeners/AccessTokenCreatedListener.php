<?php

namespace App\Listeners;

use App\Models\User;
use Laravel\Passport\Token;
use Illuminate\Support\Facades\DB;
use App\Libraries\Notification\Reminder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Laravel\Passport\Events\AccessTokenCreated;

class AccessTokenCreatedListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AccessTokenCreated $event): void
    {
        // // send notification login when generate token
        // try {
        //     $user = User::find($event->userId);

        //     resolve(Reminder::class)->dispatch($user, [
        //         'title' => 'Login Attempt',
        //         'content' => [
        //             'Your account has been login successfully',
        //             'Login Date: '.\Carbon\Carbon::now()->isoFormat('DD MMM YYYY hh:mm A'),
        //             'IP Address: '.request()->ip(),
        //             "If you didn't make this attempt, please kindly reset your password."
        //         ]
        //     ]);
        // } catch (\Throwable $th) {}

        // revoke old token access
        try {
            $token = Token::find($event->tokenId);

            // dd((array) $token->scopes);
            // when user generate new token then revoked all of old token of that user
            // delete token that generate by it own client id
            DB::table('oauth_access_tokens')
                ->where('id', '<>', $event->tokenId)
                ->where('user_id', $event->userId)
                ->where('client_id', $event->clientId)
                // only revoke when token has same scope at least 1
                ->where(function ($q) use ($token) {
                    foreach((array) $token->scopes as $k => $scope) {
                        if ($k > 0) {
                            $q->orWhereJsonContains('scopes', $scope);
                        } else {
                            $q->whereJsonContains('scopes', $scope);
                        }
                    }
                })

                // prevent revoke client type token
                ->whereNotNull('user_id')
                // ->join('oauth_clients', 'oauth_access_tokens.client_id', '=', 'oauth_clients.id')
                // ->where(function ($query) {
                //     $query->where('oauth_clients.personal_access_client', true)
                //           ->orWhere('oauth_clients.password_client', true);
                // })

                // ->whereJsonContains('scopes', (array) $token->scopes) // must be all is same
                ->update(['revoked' => true]);
        } catch (\Throwable $th) {}
    }
}
