<?php

namespace App\Services;

class SessionService
{


    /**
     * Vérifie si la session a déjà été démarrée.
     *
     * @return bool
     */
    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;

    }//end isStarted()


    /**
     * Démarre la session si elle n'est pas déjà démarrée.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->isStarted() === false) {
            session_start();
        }

    }//end start()


    /**
     * Récupère une valeur de la session.
     *
     * @param string $key     La clé de la valeur à
     *                        récupérer.
     * @param mixed  $default La valeur par défaut si la clé n'existe
     *                        pas.
     *
     * @return mixed La valeur de la session ou la valeur par défaut.
     */
    public function get(string $key, $default=null)
    {
        return ($_SESSION[$key] ?? $default);

    }//end get()


    /**
     * Définit une valeur dans la session.
     *
     * @param string $key   La clé de la valeur à
     *                      définir.
     * @param mixed  $value La valeur à
     *                      définir.
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;

    }//end set()


    /**
     * Vérifie si une clé existe dans la session.
     *
     * @param string $key La clé à vérifier.
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);

    }//end has()


    /**
     * Supprime une valeur de la session.
     *
     * @param string $key La clé de la valeur à supprimer.
     *
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);

    }//end remove()


    /**
     * Détruit la session.
     *
     * @return void
     */
    public function destroy(): void
    {
        session_unset();
        session_destroy();

    }//end destroy()


}//end class
