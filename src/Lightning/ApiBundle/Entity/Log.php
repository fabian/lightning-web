<?php

namespace Lightning\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

use Lightning\ApiBundle\Entity\Item;
use Lightning\ApiBundle\Entity\Account;

/**
 * Lightning\ApiBundle\Entity\Log
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Log
{
    const ACTION_ADDED = 'added';
    const ACTION_MODIFIED = 'modified';
    const ACTION_COMPLETED = 'completed';
    const ACTION_DELETED = 'deleted';

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $action
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @var \DateTime $happened
     *
     * @ORM\Column(name="happened", type="datetime")
     */
    private $happened;

    /**
     * @var string $old
     *
     * @ORM\Column(name="old", type="string", length=255, nullable=true)
     */
    private $old;

    /**
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * @Exclude
     */
    protected $account;

    /**
     * @ORM\ManyToOne(targetEntity="Item", inversedBy="logs")
     * @ORM\JoinColumn(name="item_id", referencedColumnName="id")
     * @Exclude
     */
    protected $item;

    public function __construct(Account $account, Item $item)
    {
        $this->account = $account;
        $this->item = $item;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Log
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set happened
     *
     * @param \DateTime $happened
     * @return Log
     */
    public function setHappened($happened)
    {
        $this->happened = $happened;

        return $this;
    }

    /**
     * Get happened
     *
     * @return \DateTime 
     */
    public function getHappened()
    {
        return $this->happened;
    }

    /**
     * Set old
     *
     * @param string $old
     * @return Log
     */
    public function setOld($old)
    {
        $this->old = $old;

        return $this;
    }

    /**
     * Get old
     *
     * @return string 
     */
    public function getOld()
    {
        return $this->old;
    }

    /**
     * Get account
     *
     * @return Lightning\ApiBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Get item
     *
     * @return Lightning\ApiBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->item;
    }
}
