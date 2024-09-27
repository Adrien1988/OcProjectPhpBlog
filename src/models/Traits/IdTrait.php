<?php

namespace App\Models\Traits;

trait IdTrait
{

    /**
     * The unique identifier of the entity.
     *
     * @var integer
     */
    private int $entityId;


    /**
     * Gets the unique identifier of the entity.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->entityId;

    }//end getId()


    /**
     * Sets the unique identifier of the entity.
     *
     * @param int $entityId The unique identifier of the entity.
     *
     * @return void
     */
    public function setId(int $entityId): void
    {
        $this->id = $entityId;

    }//end setId()


}//end trait
