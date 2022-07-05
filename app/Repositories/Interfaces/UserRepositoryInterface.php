<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function getUsers(): Collection;

    public function getUser(int $id): ?User;

    public function getUserByEmailAddress(string $email): ?User;

    public function getDuplicateUserByPhoneNumber(string $phoneNumber, int $id): ?User;

    public function updateProfile(array $data, int $id): User;

    public function updatePassword(array $data, int $id): User;
}