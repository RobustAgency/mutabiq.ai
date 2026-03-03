<?php

namespace App\Imports;

use App\Enums\UserRole;
use App\Services\UserService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UserImport implements ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    private int $organizationId;

    private string $password;

    public function __construct(
        int $organizationId,
        private UserService $userService,
    ) {
        $this->organizationId = $organizationId;
        $this->password = 'asdzxc123';
    }

    /**
     * @param  Collection<int, \Illuminate\Support\Collection<string,mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {

            $user = [
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => (string) ($row['password'] ?? $this->password),
                'role' => UserRole::USER,
            ];

            $this->userService->createUserForOrganization($user, $this->organizationId);
        }
    }

    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string'],
            '*.email' => ['required', 'email', 'unique:users,email'],
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
