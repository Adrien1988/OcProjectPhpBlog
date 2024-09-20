<?php

namespace App\Models\traits;

use DateTime;

trait TimestampableTrait
{

    /**
     * The date and time when the entity was created.
     *
     * @var DateTime
     */
    private DateTime $createdAt;

    /**
     * The date and time when the entity was last updated.
     *
     * @var ?DateTime
     */
    private ?DateTime $updatedAt = null;


    /**
     * Gets the creation date and time.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;

    }//end getCreatedAt()


    /**
     * Sets the creation date and time.
     *
     * @param DateTime $createdAt The creation date and time.
     *
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;

    }//end setCreatedAt()


    /**
     * Gets the last update date and time.
     *
     * @return ?DateTime The last update date and time, or null if not set.
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;

    }//end getUpdatedAt()


    /**
     * Sets the last update date and time.
     *
     * @param ?DateTime $updatedAt The last update date and time, or null.
     *
     * @return void
     */
    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;

    }//end setUpdatedAt()


}//end trait
