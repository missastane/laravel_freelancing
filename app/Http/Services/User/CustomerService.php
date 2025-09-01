<?php

namespace App\Http\Services\User;

use App\Http\Services\Contracts\User\CustomerServiceInterface;
use App\Jobs\SendResetPasswordUrl;
use App\Models\User\User;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CustomerService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected WalletRepositoryInterface $walletRepository
    ) {

    }
    public function getCustomers($message)
    {
        return $this->userRepository->getUsers(1, $message);
    }
    public function searchCustomers(string $search, $message)
    {
        return $this->userRepository->searchUsers(1, $search, $message);
    }
    public function showCustomer(User $customer)
    {
        return $this->userRepository->showUser($customer);
    }
    public function storeNewCustomer(array $data)
    {
        $customer = DB::transaction(function () use ($data) {
            $username = Str::random(16);
            $password = Str::random(24);

            $data['password'] = Hash::make($password);
            $data['username'] = $username;
            $data['user_type'] = 1;
            $data['active_role'] = $data['role'] == 1 ? 'employer' : 'freelancer';
            $customer = $this->userRepository->create($data);
            $this->userRepository->assignRole($customer,$customer->active_role);
            $this->walletRepository->create([
                'user_id' => $customer->id,
                'balance' => 0,
                'hold_balance' => 0,
                'currency' => 1
            ]);
            return $customer;
        });
        $passToken = Password::createToken($customer);
        SendResetPasswordUrl::dispatch($customer, $passToken);
        return $customer;
    }
    public function toggleActivation(User $user): string|null
    {
        return $this->userRepository->toggleActivation($user);
    }
    public function update(User $user, array $data)
    {
        return $this->userRepository->update($user, $data);
    }
    public function delete(User $user)
    {
        return $this->userRepository->delete($user);
    }

    public function getEployerProjects(User $customer)
    {
        return $this->userRepository->showWithRelations($customer->projects, ['employer:id,first_name,last_name', 'category:id,name']);
    }

    public function getFreelancerProposals(User $customer)
    {
        return $this->userRepository->showWithRelations($customer->proposals, ['freelancer:id,first_name,last_name', 'project:id,title']);
    }

}