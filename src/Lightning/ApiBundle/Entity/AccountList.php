<?php

namespace Lightning\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\SerializerBundle\Annotation\Exclude;

use Lightning\ApiBundle\Entity\Account;
use Lightning\ApiBundle\Entity\ItemList;

/**
 * Lightning\ApiBundle\Entity\AccountList
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class AccountList
{
    const PERMISSION_OWNER = 'owner';
    const PERMISSION_GUEST = 'guest';

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="lists")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * @Exclude
     */
    protected $account;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ItemList", inversedBy="accounts")
     * @ORM\JoinColumn(name="list_id", referencedColumnName="id")
     * @Exclude
     */
    protected $list;

    /**
     * @var string $permission
     *
     * @ORM\Column(name="permission", type="string", length=255)
     */
    private $permission;

    /**
     * @var boolean $deleted
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted = false;

    /**
     * The last time the list was marked as read.
     *
     * @var \DateTime $read
     *
     * @ORM\Column(name="`read`", type="datetime")
     * @Exclude
     */
    private $read;

    /**
     * The time the list was pushed by this device.
     *
     * @var \DateTime $pushed
     *
     * @ORM\Column(name="pushed", type="datetime")
     * @Exclude
     */
    private $pushed;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created", type="datetime")
     * @Exclude
     */
    private $created;

    /**
     * @var \DateTime $modified
     *
     * @ORM\Column(name="modified", type="datetime")
     * @Exclude
     */
    private $modified;

    /**
     * @var integer $id
     */
    public $id;

    /**
     * @var string $title
     */
    public $title;

    /**
     * @var string $url
     */
    public $url;

    public function __construct(Account $account, ItemList $list)
    {
        $this->account = $account;
        $this->list = $list;
    }

    /**
     * Set permission
     *
     * @param  string      $permission
     * @return AccountList
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set deleted
     *
     * @param  boolean     $deleted
     * @return AccountList
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set read
     *
     * @param  \DateTime   $read
     * @return AccountList
     */
    public function setRead($read)
    {
        $this->read = $read;

        return $this;
    }

    /**
     * Get read
     *
     * @return \DateTime
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Set pushed
     *
     * @param  \DateTime   $pushed
     * @return AccountList
     */
    public function setPushed($pushed)
    {
        $this->pushed = $pushed;

        return $this;
    }

    /**
     * Get pushed
     *
     * @return \DateTime
     */
    public function getPushed()
    {
        return $this->pushed;
    }

    /**
     * Set created
     *
     * @param  \DateTime   $created
     * @return AccountList
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set modified
     *
     * @param  \DateTime   $modified
     * @return AccountList
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set account
     *
     * @param  null|Account $account
     * @return AccountList
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account|null
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set list
     *
     * @param  null|ItemList $list
     * @return AccountList
     */
    public function setList(ItemList $list = null)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Get list
     *
     * @return ItemList|null
     */
    public function getList()
    {
        return $this->list;
    }
}
