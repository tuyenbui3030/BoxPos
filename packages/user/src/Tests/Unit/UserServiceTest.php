<?php

namespace Packages\User\Tests\Unit;

use Packages\User\Models\User;
use Packages\User\Services\UserService;
use Packages\User\Repositories\UserRepository;
use Packages\User\Exceptions\UserNotFoundException;
use Packages\User\Exceptions\InvalidCredentialsException;
use PHPUnit\Framework\TestCase;
use Mockery;
use Illuminate\Support\Facades\Hash;

/**
 * User Service Test
 * 
 * Unit tests for UserService class.
 */
class UserServiceTest extends TestCase
{
    protected UserService $userService;
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->userRepository = Mockery::mock(UserRepository::class);
        $this->userService = new UserService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_user_by_id_returns_user_when_found(): void
    {
        // Arrange
        $userId = 1;
        $user = new User(['id' => $userId, 'name' => 'John Doe', 'email' => 'john@example.com']);
        
        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->userService->getUserById($userId);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($userId, $result->id);
    }

    public function test_get_user_by_id_throws_exception_when_not_found(): void
    {
        // Arrange
        $userId = 999;
        
        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage("User with ID {$userId} not found");

        // Act
        $this->userService->getUserById($userId);
    }

    public function test_create_user_hashes_password(): void
    {
        // Arrange
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'plaintext_password'
        ];
        
        $this->userRepository
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return $data['name'] === 'Jane Doe' && 
                       $data['email'] === 'jane@example.com' &&
                       Hash::check('plaintext_password', $data['password']);
            }))
            ->andReturn(new User($userData));

        // Act
        $result = $this->userService->createUser($userData);

        // Assert
        $this->assertInstanceOf(User::class, $result);
    }

    public function test_authenticate_returns_user_with_valid_credentials(): void
    {
        // Arrange
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'correct_password'
        ];
        
        $user = new User([
            'id' => 1,
            'email' => 'john@example.com',
            'password' => Hash::make('correct_password')
        ]);
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($credentials['email'])
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->userService->authenticate($credentials);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($credentials['email'], $result->email);
    }

    public function test_authenticate_throws_exception_with_invalid_credentials(): void
    {
        // Arrange
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrong_password'
        ];
        
        $user = new User([
            'id' => 1,
            'email' => 'john@example.com',
            'password' => Hash::make('correct_password')
        ]);
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($credentials['email'])
            ->once()
            ->andReturn($user);

        // Assert
        $this->expectException(InvalidCredentialsException::class);

        // Act
        $this->userService->authenticate($credentials);
    }

    public function test_authenticate_throws_exception_when_user_not_found(): void
    {
        // Arrange
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'any_password'
        ];
        
        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($credentials['email'])
            ->once()
            ->andReturn(null);

        // Assert
        $this->expectException(InvalidCredentialsException::class);

        // Act
        $this->userService->authenticate($credentials);
    }

    public function test_change_password_with_correct_current_password(): void
    {
        // Arrange
        $userId = 1;
        $currentPassword = 'current_password';
        $newPassword = 'new_password';
        
        $user = new User([
            'id' => $userId,
            'password' => Hash::make($currentPassword)
        ]);
        
        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->once()
            ->andReturn($user);
            
        $this->userRepository
            ->shouldReceive('update')
            ->once()
            ->with($user, Mockery::on(function ($data) use ($newPassword) {
                return Hash::check($newPassword, $data['password']);
            }))
            ->andReturn($user);

        // Act
        $result = $this->userService->changePassword($userId, $currentPassword, $newPassword);

        // Assert
        $this->assertTrue($result);
    }

    public function test_change_password_throws_exception_with_wrong_current_password(): void
    {
        // Arrange
        $userId = 1;
        $currentPassword = 'wrong_current_password';
        $newPassword = 'new_password';
        
        $user = new User([
            'id' => $userId,
            'password' => Hash::make('actual_current_password')
        ]);
        
        $this->userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->once()
            ->andReturn($user);

        // Assert
        $this->expectException(InvalidCredentialsException::class);

        // Act
        $this->userService->changePassword($userId, $currentPassword, $newPassword);
    }
}
