<?php

use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Factory\AppFactory;
use App\Services\Container;

class ApiTest extends TestCase
{
    public $app;
    public $pdo;
    protected $pdoStatement;

    public $container;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->pdo = $this->createMock(PDO::class);
        $this->pdoStatement = $this->createMock(PDOStatement::class);
        $this->pdo->method('prepare')
            ->willReturn($this->pdoStatement);
        $this->pdo->method('query')
            ->willReturn($this->pdoStatement);

        $this->container = new Container();
        $this->container->set('pdo', fn() => $this->pdo);
        $this->app = AppFactory::create();

        (require __DIR__ . '/../src/Routes/api.php')($this->app, $this->container);
    }

    public function testGetUsersRouteWithUsers()
    {
        $this->pdoStatement->method('execute')
            ->willReturn(true);
        $this->pdoStatement->method('fetchAll')
            ->willReturn([
                ['id' => 1, 'name' => 'John Doe', 'email' => 'john.doe@example.com', 'points_balance' => 0]
            ]);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/users');
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson((string)$response->getBody());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertEquals('John Doe', $responseData[0]['name']);
    }

    public function testGetUsersRouteNoUsers()
    {
        $this->pdoStatement->method('execute')
            ->willReturn(true);
        $this->pdoStatement->method('fetchAll')
            ->willReturn([]);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/users');
        $response = $this->app->handle($request);

        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testPostUserRoute()
    {
        $this->pdoStatement->method('execute')
            ->willReturn(true);
        $this->pdoStatement->method('rowCount')
            ->willReturn(1);

        $this->pdo->method('prepare')
            ->willReturn($this->pdoStatement);

        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users')
            ->withParsedBody(['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);
        $response = $this->app->handle($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJson((string)$response->getBody());

        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['success']);
    }

    public function testPostUserRouteMissingName()
    {
        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users')
            ->withParsedBody(['email' => 'jane.doe@example.com']);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Name cannot be empty.', $responseData['message']);
    }

    public function testPostUserRouteMissingEmail()
    {
        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users')
            ->withParsedBody(['name' => 'Jane Doe']);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Email cannot be empty.', $responseData['message']);
    }

    public function testPostUserRouteMissingAll()
    {
        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users');
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Name cannot be empty.', $responseData['message']);
    }

    public function testPostUserRouteNameTooLong()
    {
        $longName = str_repeat('a', 256);
        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users')
            ->withParsedBody(['name' => $longName, 'email' => 'jane.doe@example.com']);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Name cannot be more than 255 characters.', $responseData['message']);
    }

    public function testPostUserRouteInvalidEmailFormat()
    {
        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users')
            ->withParsedBody(['name' => 'Jane Doe', 'email' => 'invalid-email']);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Invalid email address.', $responseData['message']);
    }

    public function testPostUserRouteEmailTooLong()
    {
        $longEmail = str_repeat('a', 247) . '@example.com';
        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users')
            ->withParsedBody(['name' => 'Jane Doe', 'email' => $longEmail]);
        $response = $this->app->handle($request);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Email cannot be more than 255 characters.', $responseData['message']);
    }

    public function testPostUserRouteThrowsPDOException()
    {
        $this->pdoStatement->method('execute')
            ->willThrowException(new PDOException('Simulated database error'));

        $request = (new ServerRequestFactory)->createServerRequest('POST', '/users')
            ->withParsedBody(['name' => 'Jane Doe', 'email' => 'jane.doe@example.com']);

        $response = $this->app->handle($request);

        $this->assertEquals(500, $response->getStatusCode());
        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertTrue($responseData['error']);
        $this->assertEquals('Database Error: Error adding user: Simulated database error', $responseData['message']);
    }
}
