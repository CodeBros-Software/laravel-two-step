<?php

namespace CodeBros\TwoStep\Http\Controllers;

use Carbon\Carbon;
use CodeBros\TwoStep\Traits\TwoStepTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Random\RandomException;

class TwoStepController extends Controller
{
    use TwoStepTrait;

    private $_authCount;

    private $_authStatus;

    private $_twoStepAuth;

    private $_remainingAttempts;

    private $_user;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            $this->setUser2StepData();

            return $next($request);
        });
    }

    /**
     * Set the User2Step Variables.
     */
    private function setUser2StepData(): void
    {
        $user = auth()->user();
        $twoStepAuth = $this->getTwoStepAuthStatus($user->id);
        $authCount = $twoStepAuth->authCount;
        $this->_user = $user;
        $this->_twoStepAuth = $twoStepAuth;
        $this->_authCount = $authCount;
        $this->_authStatus = $twoStepAuth->authStatus;
        $this->_remainingAttempts = config('laravel2step.laravel2stepExceededCount') - $authCount;
    }

    /**
     * Validation and Invalid code failed actions and return message.
     *
     * @param  array  $errors  (optional)
     * @return array
     */
    private function invalidCodeReturnData($errors = null)
    {
        $this->_authCount = $this->_twoStepAuth->authCount += 1;
        $this->_twoStepAuth->save();

        $returnData = [
            'message' => trans('laravel2step::laravel-verification.titleFailed'),
            'authCount' => $this->_authCount,
            'remainingAttempts' => $this->_remainingAttempts,
        ];

        if ($errors) {
            $returnData['errors'] = $errors;
        }

        return $returnData;
    }

    /**
     * Show the twostep verification form.
     *
     * @return \Illuminate\Http\Response
     *
     * @throws RandomException
     */
    public function showVerification()
    {
        abort_if(! config('laravel-two-step.laravel2stepEnabled'), 404);

        $twoStepAuth = $this->_twoStepAuth;
        $authStatus = $this->_authStatus;

        if ($this->checkExceededTime($twoStepAuth->updated_at)) {
            $this->resetExceededTime($twoStepAuth);
        }

        $data = [
            'user' => $this->_user,
            'remainingAttempts' => $this->_remainingAttempts + 1,
        ];

        if ($this->_authCount > config('laravel2step.laravel2stepExceededCount')) {
            $exceededTimeDetails = $this->exceededTimeParser($twoStepAuth->updated_at);

            $data['timeUntilUnlock'] = $exceededTimeDetails['tomorrow'];
            $data['timeCountdownUnlock'] = $exceededTimeDetails['remaining'];

            return view('laravel-two-step::twostep.exceeded', $data);
        }

        $now = new Carbon();
        $sentTimestamp = $twoStepAuth->requestDate;

        if (! $twoStepAuth->authCode) {
            $twoStepAuth->authCode = $this->generateCode();
            $twoStepAuth->save();
        }

        if (! $sentTimestamp) {
            $this->sendVerificationCodeNotification($twoStepAuth);
        } else {
            $timeBuffer = config('laravel2step.laravel2stepTimeResetBufferSeconds');
            $timeAllowedToSendCode = $sentTimestamp->addSeconds($timeBuffer);

            if ($now->gt($timeAllowedToSendCode)) {
                $this->sendVerificationCodeNotification($twoStepAuth);
                $twoStepAuth->requestDate = new Carbon();
                $twoStepAuth->save();
            }
        }

        return view('laravel-two-step::twostep.verification', $data);
    }

    /**
     * Verify the user code input.
     *
     *
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        abort_if(! config('laravel-two-step.laravel2stepEnabled'), 404);

        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'v_input_1' => 'required|min:1|max:1',
                'v_input_2' => 'required|min:1|max:1',
                'v_input_3' => 'required|min:1|max:1',
                'v_input_4' => 'required|min:1|max:1',
            ]);

            if ($validator->fails()) {
                $returnData = $this->invalidCodeReturnData($validator->errors());

                return response()->json($returnData, 418);
            }

            $code = $request->v_input_1.$request->v_input_2.$request->v_input_3.$request->v_input_4;
            $validCode = $this->_twoStepAuth->authCode;

            if ($validCode !== $code) {
                $returnData = $this->invalidCodeReturnData();

                return response()->json($returnData, 418);
            }

            $this->resetActivationCountdown($this->_twoStepAuth);

            $returnData = [
                'nextUri' => session('nextUri', '/'),
                'message' => trans('laravel-two-step::laravel-verification.titlePassed'),
            ];

            return response()->json($returnData, 200);
        }

        abort(404);
    }

    /**
     * Resend the validation code triggered by user.
     */
    public function resend(): JsonResponse
    {
        abort_if(! config('laravel-two-step.laravel2stepEnabled'), 404);

        $twoStepAuth = $this->_twoStepAuth;
        $this->sendVerificationCodeNotification($twoStepAuth);

        $returnData = [
            'title' => trans('laravel-two-step::laravel-verification.verificationEmailSuccess'),
            'message' => trans('laravel-two-step::laravel-verification.verificationEmailSentMsg'),
        ];

        return response()->json($returnData);
    }
}
