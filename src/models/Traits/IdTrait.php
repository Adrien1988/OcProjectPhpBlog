<?php

namespace App\Models\Traits;

trait IdTrait
{

    /**
     * The unique identifier of the entity.
     *
     * @var integer
     */
    private int $id;


    /**
     * Gets the unique identifier of the entity.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;

    }//end getId()


    /**
     * Sets the unique identifier of the entity.
     *
     * @param int $id The unique identifier of the entity.
     *
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;

    }//end setId()


}//end trait
