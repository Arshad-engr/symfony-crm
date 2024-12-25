<?php

namespace App\Controller;

use App\Form\TaskType;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'tasks')]
    public function index(): Response
    {
        return $this->render('task/index.html.twig');
    }

    #[Route('fetchTasks', name: 'fetchTasks', methods: ['GET', 'POST'])]
    public function fetchTasks(EntityManagerInterface $entityManager, Request $request): Response
    {

        $repository = $entityManager->getRepository(Task::class);
        // get all query parameters of datatables 
        $queryParameters = $request->query->all();
        // Get parameters for pagination and sorting
        $start = $queryParameters['start'];
        $length = $queryParameters['length'];  // Limit for pagination
        // Retrieve the search array safely
        $search = $queryParameters['search']['value'];
        // column sequence 
        $columns = ['id', 'name', 'description', 'User', 'createdAt'];
        $orderByIndex = $queryParameters['order'][0]['column'] ?? 0;
        $orderBy = $columns[$orderByIndex];
        $sortBy = $queryParameters['order'][0]['dir'] ?? 'asc';
        // Modify your repository query to handle pagination and sorting
        $query = $repository->createQueryBuilder('e')
            ->setFirstResult($start)
            ->setMaxResults($length);
        if ($search) {
            $query->andWhere('e.name LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $query->orderBy('e.' . $orderBy, $sortBy);
        $data = $query->getQuery()->getResult();
        $users = $entityManager->getRepository(User::class)->findAll();
        $dataArray = $this->formateData($data, $users);

        $totalRecords = $repository->count([]);
        return new JsonResponse([
            'draw' => $request->query->get('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => count($data),
            'data' => $dataArray
        ]);

    }
    #[Route('my-tasks', name: 'my_tasks')]
    public function myTasks(): Response
    {
        return $this->render('task/mytasks.html.twig');
    }

    #[Route('fetchMyTasks', name: 'fetchMyTasks', methods: ['GET', 'POST'])]
    public function fetchMyTasks(EntityManagerInterface $entityManager, Request $request): Response
    {

        $repository = $entityManager->getRepository(Task::class);
        // get all query parameters of datatables 
        $queryParameters = $request->query->all();
        // Get parameters for pagination and sorting
        $start = $queryParameters['start'];
        $length = $queryParameters['length'];  // Limit for pagination
        // Retrieve the search array safely
        $search = $queryParameters['search']['value'];
        // column sequence 
        $columns = ['id', 'name', 'description', 'User', 'createdAt'];
        $orderByIndex = $queryParameters['order'][0]['column'] ?? 0;
        $orderBy = $columns[$orderByIndex];
        $sortBy = $queryParameters['order'][0]['dir'] ?? 'asc';
        // Modify your repository query to handle pagination and sorting
        $query = $repository->createQueryBuilder('e')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->where('e.user = :loggedInUserId')
            ->setParameter('loggedInUserId', $this->getUser()->getId());
        if ($search) {
            $query->andWhere('e.name LIKE :search OR e.description LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $query->orderBy('e.' . $orderBy, $sortBy);
        $data = $query->getQuery()->getResult();
        $dataArray = $this->formateMyTasksData($data);

        $totalRecords = $repository->count([]);
        return new JsonResponse([
            'draw' => $request->query->get('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => count($data),
            'data' => $dataArray
        ]);

    }

    private function formateMyTasksData($data): array
    {
        // Return data in the format DataTables expects
        $dataArray = [];
        foreach ($data as $item) {
            $dataArray[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'status' => $item->getStatus(),
                'created_at' => $item->getCreatedAt(),
                'action' => sprintf(
                    '<div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Action
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item" data-id=' . $item->getId() . ' onclick="updateStatus(' . $item->getId() . ',\'pending\')" href="#">Pending</a>
                                <a class="dropdown-item" href="#" data-id=' . $item->getId() . ' onclick="updateStatus(' . $item->getId() . ',\'completed\')">Completed</a>
                            </div>
                            </div>'
                )
            ];
        }
        return $dataArray;
    }

    #[Route('updateStatus', name: 'updateStatus')]
    public function updateStatus(Request $request, EntityManagerInterface $entityManager)
    {
        $taskId = $request->query->get('task_id');
        $status = $request->query->get('status');
        $task = $entityManager->getRepository(Task::class)->find($taskId);
        // if task exist and that task assigned to loggedin user
        if (!$task) {
            // If task doesn't exist
            return new JsonResponse([
                'success' => false,
                'message' => 'task not found.',
            ], 404);  // HTTP 404 for not found
        }
        // update status
        $task->setStatus($status);
        // Persist the changes (though persist is optional when using find)
        $entityManager->persist($task);
        $entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Task has been successfully updated.',
        ]);
    }


    private function formateData($data, $users): array
    {
        // Return data in the format DataTables expects
        $dataArray = [];
        foreach ($data as $item) {
            $dataArray[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'description' => $item->getDescription(),
                'assigned_user' => $item->getUser() ? $item->getUser()->getFirstName() : $this->getAllUsers($users,$item->getId()),
                'created_at' => $item->getCreatedAt(),
                'action' => sprintf(
                    '<a href="#" class="btn btn-danger btn-icon-split" data-id="%d" onclick="deleteTask(%d)">
                        <span class="icon text-white-50">
                            <i class="fas fa-trash"></i>
                        </span>
                        <span class="text">Delete</span>
                    </a>',
                    $item->getId(),
                    $item->getId()
                )
            ];
        }
        return $dataArray;
    }

    private function getAllUsers($users,$taskId)
    {
        // Start the dropdown list
        $dropdownList = '<select class="form-control assign-user" name="assign_user" aria-label="Default select user">';
        $dropdownList .= '<option selected>Assign User</option>'; // Default option
        // Loop through the users and add each as an option
        foreach ($users as $user) {
            $dropdownList .= sprintf(
                '<option value="%d" task-id="%d">%s %s</option>',
                $user->getId(),         // User ID
                $taskId,         // Task ID
                htmlspecialchars($user->getFirstName()), // First Name (escaped for security)
                htmlspecialchars($user->getLastName())   // Last Name (escaped for security)
            );
        }
        // Close the dropdown list
        $dropdownList .= '</select>';
        // Return the generated HTML
        return $dropdownList;
    }

    #[Route('/assignUser', name: 'assignUser')]
    public function assignUser(Request $request, EntityManagerInterface $entityManager)
    {
       $taskId = $request->query->get('task_id');
       $userId = $request->query->get('user_id');
       $user = $entityManager->getRepository(User::class)->find($userId);
       $task = $entityManager->getRepository(Task::class)->find($taskId);
       if (!$task) {
           // If task doesn't exist
           return new JsonResponse([
               'success' => false,
               'message' => 'task not found.',
           ], 404);  // HTTP 404 for not found
       }
       // update status
       $task->setUser($user);
       // Persist the changes (though persist is optional when using find)
       $entityManager->persist($task);
       $entityManager->flush();
       return new JsonResponse([
           'success' => true,
           'message' => 'User assigned successfully.',
       ]);
    }


    #[Route('/create-task', name: 'create_task')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();
            $this->addFlash('success', 'Task added successfully!');

        }
        return $this->render('task/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('destroyTask', name: 'destroyTask')]
    public function deleteUser(Request $request, EntityManagerInterface $entityManager)
    {
        $taskId = $request->query->get('task_id');
        $task = $entityManager->getRepository(Task::class)->find($taskId);
        if (!$task) {
            // If task doesn't exist
            return new JsonResponse([
                'success' => false,
                'message' => 'task not found.',
            ], 404);  // HTTP 404 for not found
        }
        // Delete the user
        $entityManager->remove($task);
        $entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'Task has been successfully deleted.',
        ]);
    }
}
