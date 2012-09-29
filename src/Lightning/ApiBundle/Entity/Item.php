<?php

namespace Lightning\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\SerializerBundle\Annotation\Exclude;

use Lightning\ApiBundle\Entity\ItemList;

/**
 * Lightning\ApiBundle\Entity\Item
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Item
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
     * @var string $value
     *
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="ItemList", inversedBy="accounts")
     * @ORM\JoinColumn(name="list_id", referencedColumnName="id")
     * @Exclude
     */
    protected $list;

    /**
     * @var boolean $done
     *
     * @ORM\Column(name="done", type="boolean")
     */
    private $done = false;

    /**
     * @var boolean $deleted
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted = false;

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
     * @var string $url
     */
    public $url;

    public function __construct(ItemList $list)
    {
        $this->list = $list;
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
     * Set value
     *
     * @param string $value
     * @return Item
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set done
     *
     * @param boolean $done
     * @return Item
     */
    public function setDone($done)
    {
        $this->done = $done;

        return $this;
    }

    /**
     * Get done
     *
     * @return boolean 
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Item
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
     * Set created
     *
     * @param \DateTime $created
     * @return Item
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
     * @param \DateTime $modified
     * @return Item
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
     * Set list
     *
     * @param Lightning\ApiBundle\Entity\ItemList $list
     * @return Item
     */
    public function setList(ItemList $list)
    {
        $this->list = $list;

        return $this;
    }

    /**
     * Get list
     *
     * @return Lightning\ApiBundle\Entity\ItemList 
     */
    public function getList()
    {
        return $this->list;
    }
}
