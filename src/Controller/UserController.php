<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Controller\DbConnection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use PDO;

class UserController extends AbstractController
{
    public function __construct() {
        $this->connection = DbConnection::getInstance();
    }

    #[Route('/create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $email = $request->get('email');
        $emailError = $this->validateEmail($email);

        if (!empty($emailError)) {
            return $this->json(['error' => $emailError]);
        }

        if (empty($request->get('password'))) {
            return $this->json(['error' => 'Password can not be empty']);
        }

        $password = password_hash($request->get('password'), PASSWORD_DEFAULT);

        $stmt = $this->connection->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");

        if ($stmt->execute([':email' => $email, ':password' => $password])) {
            return $this->json(['response' => 'User created successfully'], 200);
        }

        return $this->json(['error' => 'Something went wrong']);
    }

    #[Route('/get/{id}', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $userError = $this->checkIfUserExists($id);

        if (!empty($userError)) {
            return $this->json(['error' => $userError]);
        }

        $stmt = $this->connection->prepare("SELECT id, email FROM users WHERE id = :id");

        if($stmt->execute([':id' => $id])) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            return $this->json(['user' => $user], 200);
        }

        return $this->json(['user' => 'Something went wrong']);
    }

    #[Route('/update/{id}', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $userError = $this->checkIfUserExists($id);

        if (!empty($userError)) {
            return $this->json(['error' => $userError]);
        }
        
        $email = $request->get('email');
        $emailError = $this->validateEmail($email);

        if (!empty($emailError)) {
            return $this->json(['error' => $emailError]);
        }

        if (empty($request->get('password'))) {
            return $this->json(['error' => 'Password can not be empty']);
        }

        $password = password_hash($request->get('password'), PASSWORD_DEFAULT);

        $stmt = $this->connection->prepare("UPDATE users SET email = :email, password = :password WHERE id = :id");

        if ($stmt->execute([':id' => $id, ':email' => $email, ':password' => $password])) {
            return $this->json(['user' => 'User updated successfully'], 200);
        }

        return $this->json(['user' => 'Something went wrong']);
    }

    #[Route('/delete/{id}', methods: ['DELETE'])]
    public function delete($id)
    {
        $userError = $this->checkIfUserExists($id);

        if (!empty($userError)) {
            return $this->json(['error' => $userError]);
        }

        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = :id");

        if ($stmt->execute([':id' => $id])) {
            return $this->json(['response' => 'User deleted successfully'], 200);
        }

        return $this->json(['error' => 'Something went wrong']);
    }

    protected function validateEmail($email): string
    {
        $error = '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email';
        }

        $stmt = $this->connection->prepare("SELECT email FROM users WHERE email = :email");

        if ($stmt->execute([':email' => $email])) {
            $emailTaken = $stmt->fetchColumn();

            if ($emailTaken) {
                $error =  "Email already in use";
            }

            return $error;
        }

        return 'Something went wrong';
    }

    protected function checkIfUserExists($id): string
    {
        $error = '';

        $stmt = $this->connection->prepare("SELECT id FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $userExists = $stmt->fetchColumn();

        if (!$userExists) {
            $error =  'User not found';
        }

        return $error;
    }
}
