<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

class AuthenticationTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Clear all tables
        $this->entityManager->getConnection()->executeQuery('DELETE FROM refresh_token');
        $this->entityManager->getConnection()->executeQuery('DELETE FROM "user"');
        
        // Reset rate limiter
        static::getContainer()->get('cache.app')->clear();
    }

    public function testRegisterAndLogin(): void
    {
        // 1. Register a new user
        $this->client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@mod.io',
                'password' => 'Test1234@'
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_TOO_MANY_REQUESTS) {
            $this->markTestSkipped('Rate limit reached. Try again later.');
        }
        
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('test@mod.io', $response['data']['email']);

        // 2. Login with the registered user
        $this->client->request(
            'POST',
            '/api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@mod.io',
                'password' => 'Test1234@'
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $response);
        $this->assertArrayHasKey('refresh_token', $response);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@mod.io',
                'password' => 'wrong_password'
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('Invalid credentials', $response['message']);
    }

    public function testRegisterWithInvalidData(): void
    {
        $this->client->request(
            'POST',
            '/api/v1/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'invalid_email',
                'password' => 'weak'
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        
        if ($this->client->getResponse()->getStatusCode() === Response::HTTP_TOO_MANY_REQUESTS) {
            $this->markTestSkipped('Rate limit reached. Try again later.');
        }
        
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('not a valid email address', $response['message']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clear entity manager to avoid memory leaks
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }
} 