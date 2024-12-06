<?php

namespace App\Init;

use Models\PostsRepository;
use Models\UsersRepository;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\EmailService;
use Models\CommentsRepository;
use App\Core\DependencyContainer;
use App\Services\SecurityService;
use App\Services\UrlGeneratorService;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;

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
            ->enableAttributeMapping()
            ->getValidator();

        // Initialiser les services spécifiques.
        $csrfService = new CsrfService();

        $sessionInit    = new SessionInit();
        $sessionService = $sessionInit->initialize();

        // Initialisation des routes et du générateur d'URL.
        $routes  = include __DIR__.'/../config/routes.php';
        $context = new RequestContext();

        // Créer le générateur d'URL et le matcher.
        $urlGenerator = new UrlGenerator($routes, $context);
        $urlMatcher   = new UrlMatcher($routes, $context);

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
            'urlGeneratorService' => new UrlGeneratorService($urlGenerator),
            'urlMatcher' => $urlMatcher,
        ];

    }//end initialize()


}//end class
