<?php

namespace App\Services;

class SessionStorage
{


    /**
     * Récupère une valeur de la session.
     *
     * @param string $key La clé de la valeur à récupérer.
     *
     * @return mixed|null La valeur de la session ou null si elle n'existe pas.
     */
    public function get(string $key)
    {
        return ($_SESSION[$key] ?? null);

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
        $_SESSION[$key] = $value;

    }//end set()


    /**
     * Vérifie si une clé existe dans la session.
     *
     * @param string $key La clé à vérifier.
     *
     * @return bool Retourne true si la clé existe, sinon false.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);

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


}//end class
