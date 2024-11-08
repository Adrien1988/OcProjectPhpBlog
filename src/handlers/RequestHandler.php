<?php

namespace App\Handlers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use App\Services\UrlGeneratorService;
use App\Middlewares\CsrfMiddleware;
use App\Handlers\ControllerHandler;
use Twig\Environment;
use Exception;

/**
 * Gère le traitement de la requête HTTP entrante.
 */
class RequestHandler
{


    /**
     * Traite la requête HTTP et retourne la réponse correspondante.
     *
     * @param array       $services Tableau des services initialisés.
     * @param Environment $twig     Instance de Twig pour le rendu des templates.
     * @param Request     $request  Requête HTTP entrante.
     *
     * @return Response La réponse HTTP à renvoyer au client.
     */
    public function handle(
        array $services,
        Environment $twig,
        Request $request
    ): Response {
        try {
            // Initialiser le contexte, les routes et le générateur d'URL.
            $context = new RequestContext();
            $context->fromRequest($request);
            $routes  = include __DIR__.'/../../src/config/routes.php';
            $matcher = new UrlMatcher($routes, $context);
            $urlGeneratorService = new UrlGeneratorService(new UrlGenerator($routes, $context));

            // Correspondance de la route.
            $parameters = $matcher->match($request->getPathInfo());

            // Extraction du contrôleur et de la méthode de la route.
            if (isset($parameters['_controller']) === false) {
                throw new Exception('Le contrôleur n\'est pas défini dans les paramètres de la route.');
            }

            $controller       = $parameters['_controller'];
            [$class, $method] = explode('::', $controller);

            if (empty($method) === true) {
                throw new Exception('La méthode est indéfinie ou vide.');
            }

            // Suppression des clés réservées avant de passer les paramètres.
            unset($parameters['_controller'], $parameters['_route']);

            // Instanciation du contrôleur.
            $controllerHandler  = new ControllerHandler();
            $controllerInstance = $controllerHandler->getControllerInstance(
                $class,
                $twig,
                $services,
                $urlGeneratorService
            );

            // Exécution des middlewares et de l'action du contrôleur.
            $middlewares = $request->isMethod('POST') === true ? [new CsrfMiddleware($services['csrfService'])] : [];
            $response    = $this->handleMiddlewares(
                $request,
                $middlewares,
                function () use ($controllerHandler, $controllerInstance, $method, $parameters, $request, $services) {
                    return $controllerHandler->executeControllerAction(
                        $controllerInstance,
                        $method,
                        $parameters,
                        $request,
                        $services
                    );
                }
            );

            return $response;
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            return new Response('Page non trouvée : '.$e->getMessage(), 404);
        } catch (Exception $e) {
            return new Response('Une erreur est survenue : '.$e->getMessage(), 500);
        }//end try

    }//end handle()


    /**
     * Gère l'exécution des middlewares avant l'action du contrôleur.
     *
     * @param Request  $request          Requête HTTP entrante.
     * @param array    $middlewares      Liste des middlewares à exécuter.
     * @param callable $controllerAction Fonction de rappel pour l'action du contrôleur.
     *
     * @return Response La réponse après l'exécution des middlewares.
     */
    private function handleMiddlewares(
        Request $request,
        array $middlewares,
        callable $controllerAction
    ): Response {
        $middleware = array_shift($middlewares);
        return $middleware === null ? $controllerAction($request) : $middleware->handle($request, fn($req) => $this->handleMiddlewares($req, $middlewares, $controllerAction));

    }//end handleMiddlewares()


}//end class
