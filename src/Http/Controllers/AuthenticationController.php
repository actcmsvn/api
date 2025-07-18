<?php

namespace ACTCMS\Api\Http\Controllers;

use App\Models\User;
use ACTCMS\Api\Facades\ApiHelper;
use ACTCMS\Api\Http\Requests\CheckEmailRequest;
use ACTCMS\Api\Http\Requests\LoginRequest;
use ACTCMS\Api\Http\Requests\RegisterRequest;
use ACTCMS\Base\Http\Responses\BaseHttpResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthenticationController extends BaseApiController
{
    /**
     * Register
     *
     * @bodyParam name string required The name of the user.
     * @bodyParam email string required The email of the user.
     * @bodyParam phone string required The phone of the user.
     * @bodyParam password string  required The password of user to create.
     * @bodyParam password_confirmation string  required The password confirmation.
     *
     * @response {
     * "error": false,
     * "data": null,
     * "message": "Registered successfully! We emailed you to verify your account!"
     * }
     * @response 422 {
     * "message": "The given data was invalid.",
     * "errors": {
     *     "name": [
     *         "The name field is required."
     *     ],
     *     "email": [
     *         "The email field is required."
     *     ],
     *     "password": [
     *         "The password field is required."
     *     ]
     *   }
     * }
     *
     * @group Authentication
     */
    public function register(RegisterRequest $request, BaseHttpResponse $response)
    {
        $request->merge(['password' => Hash::make($request->input('password'))]);

        if (! $request->has('name')) {
            $request->merge(['name' => $request->input('first_name') . ' ' . $request->input('last_name')]);
        }

        $user = ApiHelper::newModel()->create($request->only([
            'first_name',
            'last_name',
            'name',
            'email',
            'phone',
            'password',
        ]));

        if (ApiHelper::getConfig('verify_email')) {
            $token = Hash::make(Str::random(32));

            $user->email_verify_token = $token;

            /**
             * @var User $user
             */
            $user->sendEmailVerificationNotification();
        } else {
            $user->confirmed_at = Carbon::now();
        }

        $user->save();

        return $response
            ->setMessage(__('Registered successfully! We emailed you to verify your account!'));
    }

    /**
     * Login
     *
     * @bodyParam email string required The email of the user.
     * @bodyParam password string required The password of user to create.
     *
     * @response {
     * "error": false,
     * "data": {
     *    "token": "1|aF5s7p3xxx1lVL8hkSrPN72m4wPVpTvTs..."
     * },
     * "message": null
     * }
     *
     * @group Authentication
     */
    public function login(LoginRequest $request, BaseHttpResponse $response)
    {
        if (
            Auth::guard(ApiHelper::guard())
                ->attempt([
                    'email' => $request->input('email'),
                    'password' => $request->input('password'),
                ])
        ) {
            $user = $request->user(ApiHelper::guard());

            $token = $user->createToken($request->input('token_name', 'Personal Access Token'));

            return $response
                ->setData(['token' => $token->plainTextToken]);
        }

        return $response
            ->setError()
            ->setCode(422)
            ->setMessage(__('Email or password is not correct!'));
    }

    /**
     * Logout
     *
     * @group Authentication
     * @authenticated
     */
    public function logout(Request $request, BaseHttpResponse $response)
    {
        if (! $request->user()) {
            abort(401);
        }

        $request->user()->tokens()->delete();

        return $response
            ->setMessage(__('You have been successfully logged out!'));
    }

    /**
     * Check email existing or not
     *
     * @bodyParam email string required The email of the user.
     *
     * @response {
     *  "error": false,
     *  "data": {
     *     "exists": true
     *  },
     *  "message": null
     *  }
     *
     * @group Authentication
     */
    public function checkEmail(CheckEmailRequest $request, BaseHttpResponse $response)
    {
        $user = ApiHelper::newModel()->where('email', $request->input('email'))->first();

        $data = [
            'exists' => (bool) $user,
        ];

        if ($user) {
            $data['user'] = [];

            if ($user->first_name || $user->last_name) {
                $data['user']['first_name'] = $user->first_name;
                $data['user']['last_name'] = $user->last_name;
            }

            $data['user']['name'] = $user->name;
            $data['user']['email'] = $user->email;
        }

        return $response
            ->setData($data);
    }
}
