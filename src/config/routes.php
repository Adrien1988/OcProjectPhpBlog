<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


/**
 * Crée une nouvelle collection de routes et ajoute une route pour la page d'accueil.
 *
 * @return RouteCollection La collection de routes avec la route ajoutée.
 */
function createRoutes(): RouteCollection
{
    $routes = new RouteCollection();
    $routes->add('home', new Route('/', ['_controller' => 'App\Controllers\HomeController::index']));
    $routes->add('download_cv', new Route('/download-cv', ['_controller' => 'App\Controllers\HomeController::downloadCv']));
    $routes->add('terms', new Route('/terms-of-service', ['_controller' => 'App\Controllers\HomeController::showTerms']));
    $routes->add('privacy', new Route('/privacy-policy', ['_controller' => 'App\Controllers\HomeController::showPrivacyPolicy']));
    $routes->add('contact_submit', new Route('/contact/submit', ['_controller' => 'App\Controllers\HomeController::submitContact'], [], [], '', [], ['POST']));
    $routes->add('posts_list', new Route('/posts', ['_controller' => 'App\Controllers\PostController::listPosts']));
    $routes->add('create_post', new Route('/posts/create', ['_controller' => 'App\Controllers\PostController::createPost'], [], [], '', [], ['GET', 'POST']));
    $routes->add('post_detail', new Route('/posts/{postId}', ['_controller' => 'App\Controllers\PostController::detailPost']));
    return $routes;

}//end createRoutes()


// Appelle la fonction pour créer et retourner la collection de routes.
return createRoutes();
