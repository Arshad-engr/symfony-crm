<?php

namespace App\Controller;

use App\Entity\Task;
use Symfony\Bridge\Twig\Node\SearchAndRenderBlockNode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use phpDocumentor\Reflection\Types\Object_;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DashboardController extends AbstractController
{
    private $slugger;
    // Inject SluggerInterface via constructor
    // private $InputBag;
    public function __construct(SluggerInterface $slugger, AuthorizationCheckerInterface $authChecker)
    {
        $this->slugger = $slugger;
        $this->authChecker = $authChecker;
    }
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->redirectToRoute('dashboard');
    }
    #[Route('/dashboard', name: 'dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $repository = $entityManager->getRepository(Task::class);
        $totalCreatedTasks = $repository->count();
        $totalPendingTasks = $repository->count(['status' => 'pending']);
        $totalcompletedTasks = $repository->count(['status' => 'completed']);

        $totalPendingAssignedTasks = count($repository->findBy([
            'user' => $this->getUser()->getId(),
            'status' => 'pending'
        ]));
        $totalCompletedAssignedTasks = count($repository->findBy([
            'user' => $this->getUser()->getId(),
            'status' => 'completed'
        ]));
        $totalAssignedTasks = count($repository->findBy(['user' => $this->getUser()->getId()]));
        return $this->render('dashboard/index.html.twig', [
            'totalCreatedTasks' => $totalCreatedTasks,
            'totalPendingTasks' => $totalPendingTasks,
            'totalcompletedTasks' => $totalcompletedTasks,
            'totalPendingAssignedTasks' => $totalPendingAssignedTasks,
            'totalCompletedAssignedTasks' => $totalCompletedAssignedTasks,
            'totalAssignedTasks' => $totalAssignedTasks
        ]);
    }

    #[Route('profile', name: 'profile', methods: ['GET'])]
    public function profile(): Response
    {
        return $this->render('dashboard/profile.html.twig');
    }

    #[Route('updateProfile', name: 'updateProfile', methods: ['POST'])]
    public function updateAccount(Request $request, EntityManagerInterface $entityManager, UserInterface $user)
    {
        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('first_name');
            $lastName = $request->request->get('last_name');
            $email = $request->request->get('email');
            $dob = $request->request->get('dob');
            $dobDateType = new \DateTime($dob);
            $address = $request->request->get('address');
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setAddress($address);
            $user->setDob($dobDateType);
            $roles = $request->get('roles');
            if (!is_null($roles)) {
                $user->setRoles($roles);
            }
            $file = $request->files->get('profile');
            // update profile image if provided
            if ($file) {
                $this->updateProfileImage($user, $file);
            }
            // Persist the entity
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully');
        }
        return $this->redirectToRoute('profile');
    }
    private function updateProfileImage($user, $profile)
    {
        $originalFilename = pathinfo($profile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $profile->guessExtension();
        // Move the file to the directory where brochures are stored
        try {
            $profile->move($_ENV['PROFILE_DIRECTORY'], $newFilename);
            unlink($_ENV['PROFILE_DIRECTORY'] . '/' . $user->getProfile());
        } catch (FileException $e) {
            dd($e->getMessage());
        }
        // set profile fields
        $user->setProfile($newFilename);
        return true;
    }

    #[Route('users', name: 'users', methods: 'GET')]
    public function users(): Response
    {
        return $this->render('dashboard/users.html.twig');
    }
    #[Route('fetchUsers', name: 'fetchUsers', methods: ['GET', 'POST'])]
    public function fetchUsers(EntityManagerInterface $entityManager, Request $request): Response
    {

        $repository = $entityManager->getRepository(User::class);
        // get all query parameters of datatables 
        $queryParameters = $request->query->all();
        // Get parameters for pagination and sorting
        $start = $queryParameters['start'];
        $length = $queryParameters['length'];  // Limit for pagination
        // Retrieve the search array safely
        $search = $queryParameters['search']['value'];
        // column sequence 
        $columns = ['id', 'firstName', 'lastName', 'email', 'address', 'dob'];
        $orderByIndex = $queryParameters['order'][0]['column'] ?? 0;
        $orderBy = $columns[$orderByIndex];
        $sortBy = $queryParameters['order'][0]['dir'] ?? 'asc';
        // Modify your repository query to handle pagination and sorting
        $query = $repository->createQueryBuilder('e')
            ->setFirstResult($start)
            ->setMaxResults($length)
            ->where('e.id != :loggedInUserId')
            ->setParameter('loggedInUserId', $this->getUser()->getId());
        if ($search) {
            $query->andWhere('e.firstName LIKE :search OR e.lastName LIKE :search
                            OR e.email LIKE :search OR e.address LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }
        $query->orderBy('e.' . $orderBy, $sortBy);
        $data = $query->getQuery()->getResult();
        $dataArray = $this->formateData($data);

        $totalRecords = $repository->count([]);
        return new JsonResponse([
            'draw' => $request->query->get('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => count($data),
            'data' => $dataArray
        ]);

    }

    private function formateData($data): array
    {
        // Return data in the format DataTables expects
        $dataArray = [];
        foreach ($data as $item) {
            $dataArray[] = [
                'id' => $item->getId(),
                'first_name' => $item->getFirstName(),
                'last_name' => $item->getLastName(),
                'email' => $item->getEmail(),
                'address' => $item->getAddress(),
                'dob' => $item->getDob()->format('d-m-Y'),
                'action' => sprintf(
                    '<a href="#" class="btn btn-danger btn-icon-split" data-id="%d" onclick="deleteUser(%d)">
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

    #[Route('destroyUser', name: 'destroyUser')]
    public function deleteUser(Request $request, EntityManagerInterface $entityManager)
    {
        $userId = $request->query->get('user_id');
        $user = $entityManager->getRepository(User::class)->find($userId);
        if (!$user) {
            // If user doesn't exist
            return new JsonResponse([
                'success' => false,
                'message' => 'User not found.',
            ], 404);  // HTTP 404 for not found
        }
        // Delete the user
        $entityManager->remove($user);
        $entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => 'User has been successfully deleted.',
        ]);
    }

    #[Route('generate-report', name: 'generate_report')]
    public function generateReport(EntityManagerInterface $entityManager)
    {
        $repository = $entityManager->getRepository(Task::class);
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            // report for ADMIN
            $totalCreatedTasks = $repository->count();
            $totalPendingTasks = $repository->count(['status' => 'pending']);
            $totalcompletedTasks = $repository->count(['status' => 'completed']);
            $headers = ['Total Tasks Created', 'Total Pending Tasks', 'Total Completed Tasks'];
            $data = [$totalCreatedTasks, $totalPendingTasks, $totalcompletedTasks];

        } else {
            // report for USER
            $headers = ['Total Assigned Tasks', 'Pending Tasks', 'Completed Tasks'];
            $totalPendingAssignedTasks = count($repository->findBy([
                'user' => $this->getUser()->getId(),
                'status' => 'pending'
            ]));
            $totalCompletedAssignedTasks = count($repository->findBy([
                'user' => $this->getUser()->getId(),
                'status' => 'completed'
            ]));
            $totalAssignedTasks = count($repository->findBy(['user' => $this->getUser()->getId()]));
            $data = [$totalAssignedTasks, $totalPendingAssignedTasks, $totalCompletedAssignedTasks];
        }
        return $this->generateCSV($data,$headers);
    }

    private function generateCSV($data,$headers)
    {
        // Create a temporary file handle
        $handle = fopen('php://temp', 'w+');
        // Add the headers to the CSV
        fputcsv($handle, $headers);
        // Add data rows to the CSV
         fputcsv($handle, $data);
        // Rewind the handle so the data can be read
        rewind($handle);
        // Capture the CSV content
        $csvContent = stream_get_contents($handle);
        // Close the handle
        fclose($handle);
        // Create the response
        $response = new Response($csvContent);
        // Set headers for file download
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="data.csv"');
        return $response;
    }

}
