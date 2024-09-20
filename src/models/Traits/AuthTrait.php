<?php

namespace App\Models\traits;

trait AuthTrait
{

    /**
     * The ID of the author who created the entity.
     *
     * @var integer
     */
    private int $author;


    /**
     * Gets the ID of the author.
     *
     * @return int
     */
    public function getAuthor(): int
    {
        return $this->author;

    }//end getAuthor()


    /**
     * Sets the ID of the author.
     *
     * @param int $author The ID of the author.
     *
     * @return void
     */
    public function setAuthor(int $author): void
    {
        $this->author = $author;

    }//end setAuthor()


}//end trait
