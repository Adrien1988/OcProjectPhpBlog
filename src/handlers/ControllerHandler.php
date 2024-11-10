<?php

namespace App\Handlers;

use Exception;
use ReflectionMethod;
use Twig\Environment;
use App\Services\UrlGeneratorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gère l'instanciation des contrôleurs et l'exécution de leurs actions.
 */
class ControllerHandler
{


    /**
     * Instancie le contrôleur spécifié avec les dépendances nécessaires.
     *
     * @param string              $class               Nom de la classe du contrôleur.
     * @param Environment         $twig                Instance de Twig.
     * @param array               $services            Tableau des services.
     * @param UrlGeneratorService $urlGeneratorService Service de génération d'URL.
     *
     * @return object L'instance du contrôleur.
     */
    public function getControllerInstance(
        string $class,
        Environment $twig,
        array $services,
        UrlGeneratorService $urlGeneratorService
    ) {
        return new $class(
            $twig,
            $services['securityService'],
            $services['envService'],
            $services['csrfService'],
            $services['sessionService'],
            $services['emailService'],
            $services['validator'],
            $urlGeneratorService
        );

    }//end getControllerInstance()


    /**
     * Exécute l'action du contrôleur avec les paramètres appropriés.
     *
     * @param object  $controllerInstance Instance du contrôleur.
     * @param string  $method             Nom de la méthode à appeler.
     * @param array   $parameters         Paramètres de la route.
     * @param Request $request            Requête HTTP entrante.
     * @param array   $services           Tableau des services.
     *
     * @return Response La réponse de l'action du contrôleur.
     */
    public function executeControllerAction(
        $controllerInstance,
        string $method,
        array $parameters,
        Request $request,
        array $services
    ): Response {
        try {
            $reflectionMethod = new ReflectionMethod($controllerInstance, $method);
            if ($reflectionMethod->isPublic() === false) {
                throw new Exception('Méthode non accessible');
            }

            $methodParameters = [];
            foreach ($reflectionMethod->getParameters() as $param) {
                $paramType = $param->getType();
                $paramName = $param->getName();

                $methodParameters[] = match ($paramType?->getName()) {
                    Request::class => $request,
                    \Models\PostsRepository::class => $services['postsRepository'],
                    \Models\UsersRepository::class => $services['usersRepository'],
                    \Models\CommentsRepository::class => $services['commentsRepository'],
                    default => ($parameters[$paramName] ?? null),
                };
            }

            return $controllerInstance->$method(...$methodParameters);
        } catch (\ReflectionException $e) {
            return new Response('Méthode introuvable : '.$e->getMessage(), 404);
        } catch (Exception $e) {
            return new Response('Une erreur est survenue : '.$e->getMessage(), 500);
        }//end try

    }//end executeControllerAction()


}//end class
