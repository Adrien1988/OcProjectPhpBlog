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
    $routes->add('post_detail', new Route('/posts/{postId}', ['_controller' => 'App\Controllers\PostController::detailPost'], ['postId' => '\d+']));

    // Routes pour AuthController.
    $routes->add('register', new Route('/register', ['_controller' => 'App\Controllers\AuthController::register'], [], [], '', [], ['GET', 'POST']));
    $routes->add('login', new Route('/login', ['_controller' => 'App\Controllers\AuthController::login'], [], [], '', [], ['GET', 'POST']));
    $routes->add('logout', new Route('/logout', ['_controller' => 'App\Controllers\AuthController::logout'], [], [], '', [], ['GET']));
    $routes->add('password_reset_request', new Route('/password-reset', ['_controller' => 'App\Controllers\AuthController::passwordResetRequest',]));
    $routes->add('password_reset', new Route('/password-reset/{token}', ['_controller' => 'App\Controllers\AuthController::passwordReset',]));
    $routes->add('password_reset_request_success', new Route('/password-reset-request/success', ['_controller' => 'App\Controllers\AuthController::passwordResetRequestSuccess',]));

    // Routes pour CommentController.
    $routes->add('add_comment', new Route('/posts/{postId}/comment', ['_controller' => 'App\Controllers\CommentController::createComment'], ['postId' => '\d+'], [], '', [], ['POST']));

     // **Routes pour AdminController.**
    // Routes pour l'administration des posts.
    $routes->add('admin_dashboard', new Route('/admin/dashboard', ['_controller' => 'App\Controllers\AdminController::dashboard']));

    $routes->add('admin_posts_list', new Route('/admin/posts', ['_controller' => 'App\Controllers\AdminController::listAdminPosts']));
    $routes->add('admin_create_post', new Route('/admin/posts/create', ['_controller' => 'App\Controllers\AdminController::createPost'], [], [], '', [], ['GET', 'POST']));
    $routes->add('admin_edit_post', new Route('/admin/posts/edit/{postId}', ['_controller' => 'App\Controllers\AdminController::editPost'], ['postId' => '\d+'], [], '', [], ['GET', 'POST']));
    $routes->add('admin_delete_post', new Route('/admin/posts/delete/{postId}', ['_controller' => 'App\Controllers\AdminController::deletePost'], ['postId' => '\d+'], [], '', [], ['POST']));

    // Routes pour l'administration des commentaires.
    $routes->add('list_pending_comments', new Route('/admin/pending', ['_controller' => 'App\Controllers\AdminController::listPendingComments']));
    $routes->add('validate_comment', new Route('/admin/validate/{commentId}', ['_controller' => 'App\Controllers\AdminController::validateComment'], ['commentId' => '\d+'], [], '', [], ['POST']));
    $routes->add('invalidate_comment', new Route('/admin/invalidate/{commentId}', ['_controller' => 'App\Controllers\AdminController::invalidateComment'], ['commentId' => '\d+'], [], '', [], ['POST']));
    $routes->add('list_validated_comments', new Route('/admin/validated', ['_controller' => 'App\Controllers\AdminController::listValidatedComments']));
    $routes->add('list_invalidated_comments', new Route('/admin/invalidated', ['_controller' => 'App\Controllers\AdminController::listInvalidatedComments']));
    $routes->add('delete_invalidated_comment', new Route('/admin/delete/{commentId}', ['_controller' => 'App\Controllers\AdminController::deleteInvalidatedComment'], ['commentId' => '\d+'], [], '', [], ['POST']));

    // Routes pour la gestion des utilisateurs.
    $routes->add('admin_users_list', new Route('/admin/users', ['_controller' => 'App\Controllers\AdminController::listUsers']));
    $routes->add('admin_edit_user', new Route('/admin/users/edit/{userId}', ['_controller' => 'App\Controllers\AdminController::editUser'], ['userId' => '\d+'], [], '', [], ['GET', 'POST']));
    $routes->add('admin_delete_user', new Route('/admin/users/delete/{userId}', ['_controller' => 'App\Controllers\AdminController::deleteUser'], ['userId' => '\d+'], [], '', [], ['POST']));

    // **Routes pour les erreurs HTTP**.
    $routes->add('error_400', new Route('/error/400', ['_controller' => 'App\Controllers\ErrorController::handle', 'code' => 400]));
    $routes->add('error_403', new Route('/error/403', ['_controller' => 'App\Controllers\ErrorController::handle', 'code' => 403]));
    $routes->add('error_404', new Route('/error/404', ['_controller' => 'App\Controllers\ErrorController::handle', 'code' => 404]));
    $routes->add('error_500', new Route('/error/500', ['_controller' => 'App\Controllers\ErrorController::handle', 'code' => 500]));

    return $routes;

}//end createRoutes()


// Appelle la fonction pour créer et retourner la collection de routes.
return createRoutes();
