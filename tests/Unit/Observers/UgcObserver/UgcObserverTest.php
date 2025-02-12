<?php

namespace Wm\WmPackage\Tests\Unit\Observers\UgcObserver;

use Illuminate\Support\Facades\Auth;
use Wm\WmPackage\Tests\TestCase;
use Wm\WmPackage\Models\User;
use Wm\WmPackage\Observers\UgcObserver;
use Illuminate\Database\Eloquent\Model;

class UgcObserverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Bind a fake user repository inside the test itself
        app()->bind(User::class, MockUser::class);
        app()->bind(Auth::class, MockAuth::class);
    }

    public function test_creating_assigns_authenticated_user_as_author()
    {
        // Mock Auth facade
        $auth = $this->mock(Auth::class);
        $auth->shouldReceive('user')->once()->andReturn(new MockUser());

        // Mock model
        $mockModel = $this->mock(Model::class);
        $mockModel->shouldReceive('author')->andReturnSelf();
        $mockModel->shouldReceive('associate')->with(new MockUser())->once();

        // Call observer
        $observer = new UgcObserver();
        $observer->creating($mockModel);
    }

    public function test_creating_assigns_fallback_user_when_no_authenticated_user()
    {
        // No authenticated user
        $auth = $this->mock(Auth::class);
        $auth->shouldReceive('user')->once()->andReturn(null);

        // Mock model
        $mockModel = $this->mock(Model::class);
        $mockModel->shouldReceive('author')->andReturnSelf();
        $mockModel->shouldReceive('associate')->with(\Mockery::type(User::class))->once();

        // Call observer
        $observer = new UgcObserver();
        $observer->creating($mockModel);
    }
}

class MockUser implements User
{
        public function where($column, $value)
        {
            return $this;
        }

        public function first()
        {
            // Return a fake user instance
            $fakeUser = new User();
            $fakeUser->id = 2;
            return $fakeUser;
        }
};

class MockAuth
{
    public function user()
    {
        return new MockUser();
    }
}
