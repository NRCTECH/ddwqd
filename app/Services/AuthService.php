<?php

namespace App\Services;

use App\Contracts\Repositories\User\AuthRepositoryInterface;
use App\Contracts\Repositories\User\UserRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Exceptions\CustomException;
use App\Http\Resources\User\UserResource;
use App\Jobs\SendWelcomeMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService implements AuthServiceInterface
{
    use SendsPasswordResetEmails, ResetsPasswords {
        SendsPasswordResetEmails::broker insteadof ResetsPasswords;
        ResetsPasswords::credentials insteadof SendsPasswordResetEmails;

        SendsPasswordResetEmails::sendResetLinkEmail as sendResetPasswordMail;
        ResetsPasswords::reset as resetUserPassword;
    }

    protected AuthRepositoryInterface $authRepo;

    protected UserRepositoryInterface $userRepo;

    public function __construct(AuthRepositoryInterface $authRepo, UserRepositoryInterface $userRepo)
    {
        $this->authRepo = $authRepo;
        $this->userRepo = $userRepo;
    }

    public function register(array $data): Model
    {
        $fullname = strtolower($data['first_name'].' '.$data['last_name']);

        $data['slug'] = Str::slug($fullname).'-'.strtolower(Str::random(8));
        $data['password'] = Hash::make($data['password']);

        $user = $this->authRepo->create($data);

        SendWelcomeMail::dispatch($user);

        return $user;
    }

    public function login(array $data): array
    {
        $user = $this->userRepo->getUserByEmailAddress($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new CustomException('Incorrect login credentials', Response::HTTP_UNAUTHORIZED);
        }

        $token = $this->authRepo->createToken($user);

        return [
            'user' => new UserResource($user),
            'token' => $token,
        ];
    }

    public function forgotPassword(Request $request): string
    {
        $resetPassword = $this->sendResetPasswordMail($request);
        $content = json_decode($resetPassword->getContent());

        return $content->message;
    }

    public function updatePassword(Request $request): string
    {
        $resetUserPassword = $this->resetUserPassword($request);

        $content = json_decode($resetUserPassword->getContent());

        return $content->message;
    }
}
