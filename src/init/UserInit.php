<?php

namespace App\Init;

use App\Models\User;
use App\Services\SessionService;
use Models\UsersRepository;

/**
 * Gère l'initialisation de l'utilisateur actuel.
 */
class UserInit
{


    /**
     * Récupère l'utilisateur actuellement connecté à partir de la session.
     *
     * @param SessionService  $sessionService  Le service de session.
     * @param UsersRepository $usersRepository Le repository des utilisateurs.
     *
     * @return User|null L'utilisateur courant ou null s'il n'est pas connecté.
     */
    public function getCurrentUser(SessionService $sessionService, UsersRepository $usersRepository): ?User
    {
        if ($sessionService->has('user_id') === true) {
            $userId = $sessionService->get('user_id');
            return $usersRepository->findById($userId);
        }

        return null;

    }//end getCurrentUser()


}//end class
