<?php

namespace App\Services;

class SessionService
{


    /**
     * Démarre la session si elle n'est pas déjà démarrée.
     *
     * @return void
     */
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

    }//end start()


    /**
     * Récupère une valeur de la session.
     *
     * @param string $key     La clé de la valeur à récupérer.
     * @param mixed  $default La valeur par défaut si la clé n'existe pas.
     *
     * @return mixed La valeur de la session ou la valeur par défaut.
     */
    public function get(string $key, $default=null)
    {
        $this->start();
        // Assurez-vous que la session est démarrée avant d'accéder à $_SESSION.
        return $this->has($key) === true ? $_SESSION[$key] : $default;

    }//end get()


    /**
     * Définit une valeur dans la session.
     *
     * @param string $key   La clé de la valeur à définir.
     * @param mixed  $value La valeur à définir.
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->start();
        // Assurez-vous que la session est démarrée avant de manipuler $_SESSION.
        $_SESSION[$key] = $value;

    }//end set()


    /**
     * Supprime une valeur de la session.
     *
     * @param string $key La clé de la valeur à supprimer.
     *
     * @return void
     */
    public function remove(string $key): void
    {
        $this->start();
        // Assurez-vous que la session est démarrée.
        unset($_SESSION[$key]);

    }//end remove()


    /**
     * Détruit la session.
     *
     * @return void
     */
    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

    }//end destroy()


    /**
     * Vérifie si une clé existe dans la session.
     *
     * @param string $key La clé à vérifier.
     *
     * @return bool Retourne true si la clé existe, sinon false.
     */
    public function has(string $key): bool
    {
        $this->start();
        // Assurez-vous que la session est démarrée.
        return isset($_SESSION[$key]);

    }//end has()


    /**
     * Vérifie si la session est démarrée.
     *
     * @return bool Retourne true si la session est démarrée, sinon false.
     */
    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;

    }//end isStarted()


}//end class
