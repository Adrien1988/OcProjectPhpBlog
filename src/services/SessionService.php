<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionService
{

    /**
     * Le gestionnaire de stockage pour les données de session.
     *
     * @var SessionInterface
     */
    private SessionInterface $session;


    /**
     * Constructeur de la classe SessionService.
     *
     * @param SessionInterface $session Le gestionnaire de session Symfony.
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;

    }//end __construct()


    /**
     * Démarre la session si elle n'est pas déjà démarrée.
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->session->isStarted() === false) {
            $this->session->start();
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
        // Utilisation de la méthode has pour vérifier la présence de la clé.
        return $this->session->get($key, $default);

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
        $this->session->set($key, $value);

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
        $this->session->remove($key);

    }//end remove()


    /**
     * Détruit la session.
     *
     * @return void
     */
    public function destroy(): void
    {
        if ($this->session->isStarted() === true) {
            $this->session->invalidate();
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
        return $this->session->has($key);

    }//end has()


    /**
     * Vérifie si la session est démarrée.
     *
     * @return bool Retourne true si la session est démarrée, sinon false.
     */
    public function isStarted(): bool
    {
        return $this->session->isStarted();

    }//end isStarted()


}//end class
