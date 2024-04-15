<?php
namespace App\Controller;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Nelmio\ApiDocBundle\Annotation\Model;  // Import the Model annotation
use OpenApi\Annotations as OA;  // Ensure this is also imported if not already


class CustomerController extends AbstractController
{

   /**
     * Retrieves a random private customer from the database.
     *
     * @Route("/get-random-private-customer", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns a random private customer",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Customer::class)
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="No private customer found"
     * )
     * @OA\Tag(name="Customer")
     */
    public function getRandomPrivateCustomer(EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['type' => 'private'], ['id' => 'ASC']);

        if (!$customer)
            return new Response('No private customer found', Response::HTTP_NOT_FOUND);

        $jsonContent = $serializer->serialize($customer, 'json');
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Retrieves a random business customer from the database.
     *
     * @Route("/get-random-business-customer", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns a random business customer",
     *     @OA\JsonContent(
     *         type="object",
     *         ref=@Model(type=Customer::class)
     *     )
     * )
     * @OA\Response(
     *     response=404,
     *     description="No business customer found"
     * )
     * @OA\Tag(name="Customer")
     */
    public function getRandomBusinessCustomer(EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $customer = $entityManager->getRepository(Customer::class)->findOneBy(['type' => 'business'], ['id' => 'ASC']);

        if (!$customer)
            return new Response('No business customer found', Response::HTTP_NOT_FOUND);

        $jsonContent = $serializer->serialize($customer, 'json');
        return new Response($jsonContent, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }

    /**
     * Creates a private customer with the provided JSON data.
     *
     * @Route("/private-customer", methods={"POST"})
     * @OA\RequestBody(
     *     description="JSON payload",
     *     required=true,
     *     @OA\JsonContent(
     *         ref=@Model(type=Customer::class)
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Customer created successfully"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request, validation errors",
     *     @OA\JsonContent(
     *         type="string",
     *         example="Validation errors string"
     *     )
     * )
     * @OA\Tag(name="Customer")
     */
    public function createPrivateCustomer(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $data = $request->getContent();
        try {
            $customer = $serializer->deserialize($data, Customer::class, 'json');
            $customer->setType('private');
            $customer->setCreatedAt(new \DateTime());
            $customer->setUpdatedAt(new \DateTime());

            $errors = $validator->validate($customer);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response($errorsString, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            return new Response('Customer created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new Response('Error processing request: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Updates an existing private customer with the provided JSON data.
     *
     * @Route("/private-customer/{id}", methods={"PUT"})
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="The ID of the private customer to update",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="JSON payload",
     *     required=true,
     *     @OA\JsonContent(
     *         ref=@Model(type=Customer::class)
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Customer updated successfully"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Customer not found"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request, validation errors",
     *     @OA\JsonContent(
     *         type="string",
     *         example="Validation errors string"
     *     )
     * )
     * @OA\Tag(name="Customer")
     */
    public function updatePrivateCustomer(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager, $id): Response
    {
        $existingCustomer = $entityManager->getRepository(Customer::class)->find($id);

        if (!$existingCustomer) {
            return new Response('Customer not found', Response::HTTP_NOT_FOUND);
        }

        $data = $request->getContent();
        try {
            $serializer->deserialize($data, Customer::class, 'json', ['object_to_populate' => $existingCustomer]);

            $existingCustomer->setUpdatedAt(new \DateTime());

            $errors = $validator->validate($existingCustomer);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response($errorsString, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->flush();

            return new Response('Customer updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Error processing request: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    private function getBusinessContent(Request $request) {
        $dataString = $request->getContent();
        $originalData = json_decode($dataString, true);

        // Create a new array with 'type' as the first key
        $data = ['type' => 'business'];

        // Copy other elements from the original array to the new array
        foreach ($originalData as $key => $value) {
            // Skip 'type' to avoid overwriting it if it's already set in original data
            if ($key !== 'type') {
                $data[$key] = $value;
            }
        }

        return json_encode($data);
    }

    /**
     * Creates a business customer with the provided JSON data.
     *
     * @Route("/business-customer", methods={"POST"})
     * @OA\RequestBody(
     *     description="JSON payload",
     *     required=true,
     *     @OA\JsonContent(
     *         ref=@Model(type=Customer::class)
     *     )
     * )
     * @OA\Response(
     *     response=201,
     *     description="Business customer created successfully"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request, validation errors",
     *     @OA\JsonContent(
     *         type="string",
     *         example="Validation errors string"
     *     )
     * )
     * @OA\Tag(name="Customer")
     */
    public function createBusinessCustomer(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $data = $this->getBusinessContent($request);
        try {
            $customer = $serializer->deserialize($data, Customer::class, 'json');
            $customer->setType('business');
            $customer->setCreatedAt(new \DateTime());
            $customer->setUpdatedAt(new \DateTime());

            $errors = $validator->validate($customer);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response($errorsString, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($customer);
            $entityManager->flush();

            return new Response('Business customer created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new Response('Error processing request: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Updates an existing business customer with the provided JSON data.
     *
     * @Route("/business-customer/{id}", methods={"PUT"})
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     description="The ID of the business customer to update",
     *     @OA\Schema(type="integer")
     * )
     * @OA\RequestBody(
     *     description="JSON payload for updating a business customer, including privileges and department",
     *     required=true,
     *     @OA\JsonContent(
     *         ref=@Model(type=Customer::class)
     *     )
     * )
     * @OA\Response(
     *     response=200,
     *     description="Business customer updated successfully"
     * )
     * @OA\Response(
     *     response=404,
     *     description="Customer not found"
     * )
     * @OA\Response(
     *     response=400,
     *     description="Bad request, validation errors",
     *     @OA\JsonContent(
     *         type="string",
     *         example="Validation errors string"
     *     )
     * )
     * @OA\Tag(name="Customer")
     */
    public function updateBusinessCustomer(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $entityManager, $id): Response
    {
        $existingCustomer = $entityManager->getRepository(Customer::class)->find($id);

        if (!$existingCustomer) {
            return new Response('Customer not found', Response::HTTP_NOT_FOUND);
        }

        $data = $this->getBusinessContent($request);
        try {
            $serializer->deserialize($data, Customer::class, 'json', ['object_to_populate' => $existingCustomer]);
            $existingCustomer->setUpdatedAt(new \DateTime());

            $errors = $validator->validate($existingCustomer);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response($errorsString, Response::HTTP_BAD_REQUEST);
            }

            $entityManager->flush();

            return new Response('Business customer updated successfully', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response('Error processing request: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}

