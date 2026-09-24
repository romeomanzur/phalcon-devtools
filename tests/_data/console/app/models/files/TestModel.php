<?php

declare(strict_types=1);

/**
 * @extends \Phalcon\Mvc\Model<TestModel>
 */
class TestModel extends \Phalcon\Mvc\Model
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
     * @Column(column="some-col", type="string", length=20, nullable=false)
     */
    public $someCol;

    /**
     * @var string
     * @Column(column="someCol2", type="string", length=20, nullable=false)
     */
    public $someCol2;

    /**
     * @var string
     * @Column(column="SomeCol3", type="string", length=20, nullable=false)
     */
    public $someCol3;

    /**
     * Initialize the model.
     */
    public function initialize(): void
    {
        $this->setSchema("devtools");
        $this->setSource("testModel");
    }

}
