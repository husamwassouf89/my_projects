<?php


namespace App\Services;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public $isLogged;
    public $loginData;

    public function __construct()
    {
        $this->isLogged  = Auth::guard('api')->check();
        $this->loginData = false;
    }

    public function login(string $email, string $password)
    {

        $user = User::whereEmail($email)->first();

        if (!($user and Hash::check($password, $user->password))) return 1;

        $credentials = ['email' => $email, 'password' => $password];

        if (!Auth::attempt($credentials)) return 2;
        $user = Auth::user();

        $tokenResult       = $user->createToken('Personal Access Token');
        $token             = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(4);
        $token->save();


        $this->loginData = $this->initializeLoginData($tokenResult, $user);
        $this->isLogged  = true;

        return 3;

    }

    private function initializeLoginData($tokenResult, $user): array
    {

        return [
            'access_token' => 'Bearer ' . $tokenResult->accessToken,
            'expires_at'   => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'email'        => $user->email,
            'name'         => $user->name,
            'role'         => $user->role()->with('permissions')->first(),
        ];
    }

    public function logout(): bool
    {
        if (Auth::user()->token()->revoke()) {
            $this->isLogged = false;
            return !$this->isLogged;
        } else return false;
    }







}
