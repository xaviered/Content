<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ApiJsonResponse;

/**
 * Class RootController is main controller for site that manages the api
 *
 * @package App\Http\Controllers
 */
class RootController extends AuthController
{
    public function ping()
    {
        return new ApiJsonResponse('pong');
    }

    /**
     * Show login page
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function login(Request $request)
    {
        return view('login');
    }

    /**
     * Handle the login
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postLogin(Request $request)
    {
        $response = parent::login($request)->getOriginalContent();
        if (!isset($response['success'])) {
            $request->session()->flash('error', $response['error'] ?? 'Could not login');
        } else {
            $request->session()->flash('message', 'Token: '.$response['data']['token']);
        }

        return redirect('/login');
    }

    /**
     * API Register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function register(Request $request)
    {
        return view('register');
    }
}
