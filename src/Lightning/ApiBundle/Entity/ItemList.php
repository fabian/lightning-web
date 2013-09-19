<?php

namespace Lightning\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * Lightning\ApiBundle\Entity\ItemList
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ItemList
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * Invitation code for sharing list with others.
     *
     * @var string $invitation
     *
     * @ORM\Column(name="invitation", type="string", length=255)
     * @Exclude
     */
    private $invitation;

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
     */
    private $modified;

    /**
     * @ORM\OneToMany(targetEntity="AccountList", mappedBy="list", cascade={"persist", "remove"})
     * @Exclude
     */
    protected $accounts;

    /**
     * @var string $url
     */
    public $url;

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
     * Set title
     *
     * @param  string   $title
     * @return ItemList
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set created
     *
     * @param  \DateTime $created
     * @return ItemList
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
     * @param  \DateTime $modified
     * @return ItemList
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
     * Constructor
     */
    public function __construct()
    {
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get accounts
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set invitation
     *
     * @param string $invitation
     * @return ItemList
     */
    public function setInvitation($invitation)
    {
        $this->invitation = $invitation;

        return $this;
    }

    /**
     * Get invitation
     *
     * @return string 
     */
    public function getInvitation()
    {
        return $this->invitation;
    }
}
