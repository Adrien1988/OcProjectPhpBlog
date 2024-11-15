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

    // Routes pour HomeController.
    $routes->add('home', new Route('/', ['_controller' => 'App\Controllers\HomeController::index']));
    $routes->add('download_cv', new Route('/download-cv', ['_controller' => 'App\Controllers\HomeController::downloadCv']));
    $routes->add('terms', new Route('/terms-of-service', ['_controller' => 'App\Controllers\HomeController::showTerms']));
    $routes->add('privacy', new Route('/privacy-policy', ['_controller' => 'App\Controllers\HomeController::showPrivacyPolicy']));
    $routes->add('contact_submit', new Route('/contact/submit', ['_controller' => 'App\Controllers\HomeController::submitContact'], [], [], '', [], ['POST']));

    // Routes pour PostController.
    $routes->add('posts_list', new Route('/posts', ['_controller' => 'App\Controllers\PostController::listPosts']));
    $routes->add('create_post', new Route('/posts/create', ['_controller' => 'App\Controllers\PostController::createPost'], [], [], '', [], ['GET', 'POST']));
    $routes->add('post_detail', new Route('/posts/{postId}', ['_controller' => 'App\Controllers\PostController::detailPost'], ['postId' => '\d+']));
    $routes->add('edit_post', new Route('/posts/edit/{postId}', ['_controller' => 'App\Controllers\PostController::editPost'], [], [], '', [], ['GET', 'POST']));
    $routes->add('delete_post', new Route('/posts/delete/{postId}', ['_controller' => 'App\Controllers\PostController::deletePost'], [], [], '', [], ['POST']));

    // Routes pour AuthController.
    $routes->add('register', new Route('/register', ['_controller' => 'App\Controllers\AuthController::register'], [], [], '', [], ['GET', 'POST']));
    $routes->add('login', new Route('/login', ['_controller' => 'App\Controllers\AuthController::login'], [], [], '', [], ['GET', 'POST']));
    $routes->add('logout', new Route('/logout', ['_controller' => 'App\Controllers\AuthController::logout'], [], [], '', [], ['GET']));
    $routes->add('password_reset_request', new Route('/password-reset', ['_controller' => 'App\Controllers\AuthController::passwordResetRequest',]));
    $routes->add('password_reset', new Route('/password-reset/{token}', ['_controller' => 'App\Controllers\AuthController::passwordReset',]));
    $routes->add(
        'password_reset_request_success',
        new Route(
            '/password-reset-request/success',
            [
                '_controller' => 'App\Controllers\AuthController::passwordResetRequestSuccess',
            ]
        )
    );

    // Routes pour CommentController.
    $routes->add('add_comment', new Route('/posts/{postId}/comment', ['_controller' => 'App\Controllers\CommentController::createComment'], ['postId' => '\d+'], [], '', [], ['POST']));
    $routes->add('list_pending_comments', new Route('/admin/pending', ['_controller' => 'App\Controllers\CommentController::listPendingComments']));
    $routes->add(
        'validate_comment',
        new Route(
            '/admin/validate/{commentId}',
            [
                '_controller' => 'App\Controllers\CommentController::validateComment'
            ],
            ['commentId' => '\d+'],
            [],
            '',
            [],
            ['POST']
        )
    );
    $routes->add('list_validated_comments', new Route('/admin/validated', ['_controller' => 'App\Controllers\CommentController::listValidatedComments']));
    $routes->add('invalidate_comment', new Route('/admin/invalidate/{commentId}', ['_controller' => 'App\Controllers\CommentController::invalidateComment'], ['commentId' => '\d+'], [], '', [], ['POST']));
    $routes->add('list_invalidated_comments', new Route('/admin/invalidated', ['_controller' => 'App\Controllers\CommentController::listInvalidatedComments']));
    $routes->add(
        'delete_invalidated_comment',
        new Route(
            '/admin/delete/{commentId}',
            [
                '_controller' => 'App\Controllers\CommentController::deleteInvalidatedComment'
            ],
            [],
            [],
            '',
            [],
            ['POST']
        )
    );

    return $routes;

}//end createRoutes()


// Appelle la fonction pour créer et retourner la collection de routes.
return createRoutes();
