<?php

namespace App\Models\Traits;

use Symfony\Component\Config\Definition\Exception\Exception;

trait IdTrait
{

    /**
     * L'identifiant unique de l'entité.
     *
     * @var integer|null
     */
    private ?int $entityId = null;


    /**
     * Obtient l'identifiant unique de l'entité.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        if ($this->entityId === null) {
            throw new Exception('L\'ID de l\'entité n\'est pas défini.');
        }

        return $this->entityId;

    }//end getId()


    /**
     * Définit l'identifiant unique de l'entité.
     *
     * @param int|null $entityId L'identifiant unique de l'entité.
     *
     * @return void
     */
    public function setId(?int $entityId): void
    {
        $this->entityId = $entityId;

    }//end setId()


}//end trait
