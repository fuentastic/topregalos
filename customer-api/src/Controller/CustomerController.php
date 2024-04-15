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

class CustomerController extends AbstractController
{

   /**
     * @Route("/get-random-private-customer", methods={"GET"})
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
     * @Route("/get-random-business-customer", methods={"GET"})
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
     * @Route("/private-customer", methods={"POST"})
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
     * @Route("/private-customer/{id}", methods={"PUT"})
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
     * @Route("/business-customer", methods={"POST"})
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
     * @Route("/business-customer/{id}", methods={"PUT"})
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

