<?php

namespace App\Controller;

use Doctrine\DBAL\DriverManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Alias;

use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
    public function request(Request $request)
    {
        $connection = $this->getConnection();

        $tableExists = $this->executeRequest(
            "SELECT * FROM information_schema.tables WHERE table_schema = 'symfony' AND table_name = 'user' LIMIT 1;",
            [],
            $connection
        );

        if (empty($tableExists)) {
            $this->executeRequest("CREATE TABLE user (id int, data varchar(255))", [], $connection);
            $this->executeRequest("INSERT INTO user(id, data) values(1, 'Barack - Obama - White House')", [], $connection);
            $this->executeRequest("INSERT INTO user(id, data) values(2, 'Britney - Spears - America')", [], $connection);
            $this->executeRequest("INSERT INTO user(id, data) values(3, 'Leonardo - DiCaprio - Titanic')", [], $connection);
        }

        if ($request->getMethod() === "GET") {
            $id = $request->query->get("id");
            $action = $request->query->get("action");

            if ($action === "delete" && $id) {
                $this->executeRequest("DELETE FROM user WHERE id = ?", [$id], $connection);
            }
        } elseif ($request->getMethod() === "POST") {
            $firstname = $request->request->get("firstname");
            $lastname = $request->request->get("lastname");
            $address = $request->request->get("address");

            $this->executeRequest(
                "INSERT INTO user(id, data) values(?, ?)",
                [time(), "$firstname - $lastname - $address"],
                $connection
            );
        }

        $users = $this->executeRequest("SELECT * FROM user;", [], $connection);

        return $this->render('user.html.twig', [
            'obj' => $request->getMethod(),
            'users' => $users
        ]);
    }

    private function getConnection()
    {
        $connectionParams = [
            'dbname' => 'symfony',
            'user' => 'symfony',
            'password' => '',
            'host' => 'mariadb',
            'driver' => 'pdo_mysql',
        ];
        return DriverManager::getConnection($connectionParams);
    }

    private function executeRequest($sql, $params = [], $connection)
    {
        $stmt = $connection->prepare($sql);
        return $stmt->executeQuery($params)->fetchAllAssociative();
    }
}
