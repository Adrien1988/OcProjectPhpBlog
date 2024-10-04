<?php

namespace App\Models\Traits;

trait IdTrait
{

    /**
     * The unique identifier of the entity.
     *
     * @var integer
     */
    private ?int $entityId = null;


    /**
     * Gets the unique identifier of the entity.
     *
     * @return int
     */
    public function getId(): ?int
    {
        if ($this->entityId === null) {
            throw new \Exception('L\'ID de l\'utilisateur n\'est pas défini.');
        }

        return $this->entityId;

    }//end getId()


    /**
     * Sets the unique identifier of the entity.
     *
     * @param int $id The unique identifier of the entity.
     *
     * @return void
     */
    public function setId(?int $id): void
    {
        $this->entityId = $id;

    }//end setId()


}//end trait
