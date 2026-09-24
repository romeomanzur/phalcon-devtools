<?php

declare(strict_types=1);

/**
 * @extends \Phalcon\Mvc\Model<Testmodel4>
 */
class Testmodel4 extends \Phalcon\Mvc\Model
{

    /**
     * @var int|null
     * @Primary
     * @Identity
     * @Column(column="id", type="int", length=10, nullable=false)
     */
    public $id;

    /**
     * @var string
     * @Column(column="name", type="string", length=45, nullable=false)
     */
    public $name;

    /**
     * Initialize the model.
     */
    public function initialize(): void
    {
        $this->setSchema("devtools");
        $this->setSource("Testmodel4");
    }

}
