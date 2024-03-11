<?php

namespace CodeBros\TwoStep\Traits;

use Carbon\Carbon;
use CodeBros\TwoStep\Models\TwoStepAuth;
use CodeBros\TwoStep\Notifications\SendVerificationCodeEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Random\RandomException;

trait TwoStepTrait
{
    /**
     * Check if the user is authorized.
     *
     * @param  Request  $request
     *
     * @throws RandomException
     */
    public function twoStepVerification($request): bool
    {
        $user = auth()->user();

        if ($user) {
            $twoStepAuthStatus = $this->checkTwoStepAuthStatus($user->id);

            if ($twoStepAuthStatus->authStatus !== true) {
                return false;
            } else {
                if ($this->checkTimeSinceVerified($twoStepAuthStatus)) {
                    return false;
                }
            }

            return true;
        }

        return true;
    }

    /**
     * Check time since user was last verified and take apprpriate action.
     *
     *
     * @throws RandomException
     */
    private function checkTimeSinceVerified(TwoStepAuth $twoStepAuth): bool
    {
        $expireMinutes = config('laravel2step.laravel2stepVerifiedLifetimeMinutes');
        $now = Carbon::now();
        $expire = Carbon::parse($twoStepAuth->authDate)->addMinutes($expireMinutes);
        $expired = $now->gt($expire);

        if ($expired) {
            $this->resetAuthStatus($twoStepAuth);

            return true;
        }

        return false;
    }

    /**
     * Reset TwoStepAuth collection item and code.
     *
     *
     * @throws RandomException
     */
    private function resetAuthStatus(TwoStepAuth $twoStepAuth): TwoStepAuth
    {
        $twoStepAuth->authCode = $this->generateCode();
        $twoStepAuth->authCount = 0;
        $twoStepAuth->authStatus = 0;
        $twoStepAuth->authDate = null;
        $twoStepAuth->requestDate = null;

        $twoStepAuth->save();

        return $twoStepAuth;
    }

    /**
     * Generate Authorization Code.
     *
     *
     * @throws RandomException
     */
    private function generateCode(int $length = 4, string $prefix = '', string $suffix = ''): string
    {
        for ($i = 0; $i < $length; $i++) {
            $prefix .= random_int(0, 9);
        }

        return $prefix.$suffix;
    }

    /**
     * Create/retrieve 2step verification object.
     */
    private function checkTwoStepAuthStatus(int $userId): TwoStepAuth
    {
        return TwoStepAuth::firstOrCreate(['userId' => $userId], ['authCode' => $this->generateCode()]);
    }

    /**
     * Retrieve the Verification Status.
     *
     *
     * @return Builder|Model
     */
    protected function getTwoStepAuthStatus(int $userId)
    {
        return TwoStepAuth::query()->where('userId', '=', $userId)->firstOrFail();
    }

    /**
     * Format verification exceeded timings with Carbon.
     *
     * @param  string  $time
     */
    protected function exceededTimeParser($time): Collection
    {
        $tomorrow = Carbon::parse($time)->addMinutes(config('laravel2step.laravel2stepExceededCountdownMinutes'))->format('l, F jS Y h:i:sa');
        $remaining = $time->addMinutes(config('laravel2step.laravel2stepExceededCountdownMinutes'))->diffForHumans(null, true);

        $data = [
            'tomorrow' => $tomorrow,
            'remaining' => $remaining,
        ];

        return collect($data);
    }

    /**
     * Check if time since account lock has expired and return true if account verification can be reset.
     *
     * @param  \DateTime  $time
     */
    protected function checkExceededTime($time): bool
    {
        $now = Carbon::now();
        $expire = Carbon::parse($time)->addMinutes(config('laravel2step.laravel2stepExceededCountdownMinutes'));
        $expired = $now->gt($expire);

        if ($expired) {
            return true;
        }

        return false;
    }

    /**
     * Method to reset code and count.
     *
     * @param  collection  $twoStepEntry
     * @return collection
     */
    protected function resetExceededTime($twoStepEntry)
    {
        $twoStepEntry->authCount = 0;
        $twoStepEntry->authCode = $this->generateCode();
        $twoStepEntry->save();

        return $twoStepEntry;
    }

    /**
     * Successful activation actions.
     *
     * @param  collection  $twoStepAuth
     * @return void
     */
    protected function resetActivationCountdown($twoStepAuth)
    {
        $twoStepAuth->authCode = $this->generateCode();
        $twoStepAuth->authCount = 0;
        $twoStepAuth->authStatus = 1;
        $twoStepAuth->authDate = Carbon::now();
        $twoStepAuth->requestDate = null;

        $twoStepAuth->save();
    }

    /**
     * Send verification code via notify.
     */
    protected function sendVerificationCodeNotification(TwoStepAuth $twoStepAuth): void
    {
        $user = auth()->user();

        if (! isset($user->mobiel)) {
            // If we don't have a mobile phone number we try to send the authentication code via email
            $user->notify(new SendVerificationCodeEmail($user, $twoStepAuth->authCode));
        } else {
            // If we do have a mobile phone number we try to send the authentication code via SMS
            $request = Http::baseUrl('https://rest.spryngsms.com/v1')
                ->withToken(env('OTP_AUTH_TOKEN'))
                ->post('messages', [
                    'body' => 'Code: '.$twoStepAuth->authCode,
                    'encoding' => 'auto',
                    'originator' => env('OTP_FROM'),
                    'recipients' => [$user->mobiel],
                    'route' => env('OTP_ROUTE'),
                ])->json();
        }

        $twoStepAuth->requestDate = Carbon::now();
        $twoStepAuth->save();
    }
}
