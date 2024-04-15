<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerControllerTest extends WebTestCase
{
    // Test `privileges` and `department` fields are not usable for private customers
    public function testFailCreatePrivateCustomer()
    {
        $client = static::createClient();
        $client->request('POST', '/private-customer', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'business@example.com',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'locale' => 'en_US',
                'privileges' => 'advanced',
                'department' => 'sales'
            ]));

        $this->assertResponseIsUnprocessable();
        $this->assertResponseStatusCodeSame(400);
    }

    // Test create private customer
    public function testCreatePrivateCustomer()
    {
        $client = static::createClient();
        $client->request('POST', '/private-customer', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'locale' => 'en_US'
            ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
    }

    // Test fail business customer by adding `privileges` value which is not allowed
    public function testFailCreateBusinessCustomer()
    {
        $client = static::createClient();
        $client->request('POST', '/business-customer', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'business@example.com',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'locale' => 'en_US',
                'privileges' => 'god',
                'department' => 'sales'
            ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
    }

    // Test create business customer
    public function testCreateBusinessCustomer()
    {
        $client = static::createClient();
        $client->request('POST', '/business-customer', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'business@example.com',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'locale' => 'en_US',
                'privileges' => 'advanced',
                'department' => 'sales'
            ]));

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(201);
    }

    // update will need an existing uuid, so disable for now
    // public function testFailUpdatePrivateCustomer()
    // {
    //     $client = static::createClient();
    //     $client->request('PUT', '/private-customer/1', [], [], ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'firstname' => 'UpdatedJohn',
    //             'lastname' => 'UpdatedDoe',
    //             'locale' => 'en_US',
    //             'privileges' => 'full',
    //             'department' => 'marketing'
    //         ]));
    //
    //     $this->assertResponseIsUnprocessable();
    //     $this->assertResponseStatusCodeSame(400);
    // }
    //
    // public function testUpdatePrivateCustomer()
    // {
    //     $client = static::createClient();
    //     $client->request('PUT', '/private-customer/1', [], [], ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'firstname' => 'UpdatedJohn',
    //             'lastname' => 'UpdatedDoe',
    //             'locale' => 'en_US'
    //         ]));
    //
    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(200);
    // }
    //
    // public function testFailUpdateBusinessCustomer()
    // {
    //     $client = static::createClient();
    //     $client->request('PUT', '/business-customer/1', [], [], ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'firstname' => 'UpdatedJane',
    //             'lastname' => 'UpdatedSmith',
    //             'locale' => 'en_US',
    //             'privileges' => 'god',
    //             'department' => 'marketing'
    //         ]));
    //
    //     $this->assertResponseIsUnprocessable();
    //     $this->assertResponseStatusCodeSame(400);
    // }
    //
    // public function testUpdateBusinessCustomer()
    // {
    //     $client = static::createClient();
    //     $client->request('PUT', '/business-customer/1', [], [], ['CONTENT_TYPE' => 'application/json'],
    //         json_encode([
    //             'firstname' => 'UpdatedJane',
    //             'lastname' => 'UpdatedSmith',
    //             'locale' => 'en_US',
    //             'privileges' => 'full',
    //             'department' => 'marketing'
    //         ]));
    //
    //     $this->assertResponseIsSuccessful();
    //     $this->assertResponseStatusCodeSame(200);
    // }
}

