<?php

namespace App\Init;

use App\Services\CsrfService;
use App\Services\SecurityService;
use App\Services\EmailService;
use App\Services\EnvService;
use Models\PostsRepository;
use Models\UsersRepository;
use Models\CommentsRepository;
use App\Core\DependencyContainer;
use Symfony\Component\Validator\Validation;

/**
 * Initialise les services principaux de l'application.
 */
class ServicesInit
{


    /**
     * Initialise les services et les dépendances nécessaires à l'application.
     *
     * @param EnvService          $envService Le service d'environnement.
     * @param DependencyContainer $container  Le conteneur de dépendances.
     *
     * @return array Un tableau contenant les services initialisés.
     */
    public function initialize(EnvService $envService, DependencyContainer $container): array
    {
        // Initialiser le traducteur.
        $translatorInit = new TranslatorInit();
        $translator     = $translatorInit->initialize('fr');

        // Initialiser le validateur avec le traducteur.
        $validator = Validation::createValidatorBuilder()
            ->setTranslator($translator)
            ->setTranslationDomain('validators')
            ->getValidator();

        // Initialiser les services spécifiques.
        $csrfService = new CsrfService();

        $sessionInit    = new SessionInit();
        $sessionService = $sessionInit->initialize();

        return [
            'csrfService' => $csrfService,
            'securityService' => new SecurityService(),
            'emailService' => new EmailService($envService),
            'envService' => $envService,
            'sessionService' => $sessionService,
            'postsRepository' => new PostsRepository($container->getDatabase(), $validator),
            'usersRepository' => new UsersRepository($container->getDatabase(), $validator),
            'commentsRepository' => new CommentsRepository($container->getDatabase()),
            'validator' => $validator,
            'translator' => $translator,
        ];

    }//end initialize()


}//end class
