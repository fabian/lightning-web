<?php

namespace Lightning\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\SerializerBundle\Annotation\Exclude;

/**
 * Lightning\ApiBundle\Entity\Account
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Account implements UserInterface
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
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     * @Exclude
     */
    private $code;

    /**
     * @var string $secret
     *
     * @ORM\Column(name="secret", type="string", length=255)
     * @Exclude
     */
    private $secret;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=255)
     * @Exclude
     */
    private $salt;

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
     * @ORM\OneToMany(targetEntity="AccountList", mappedBy="account", cascade={"persist", "remove"})
     * @Exclude
     */
    protected $lists;

    /**
     * @var string $url
     */
    public $url;

    /**
     * @var string $shortUrl
     */
    public $shortUrl;

    /**
     * @var string $account
     */
    public $account;

    /**
     * @var string $listsUrl
     */
    public $listsUrl;

    /**
     * Secret in plain text, only set just after creation.
     *
     * @var string $revealed
     *
     * @Exclude
     */
    public $revealed;

    public function __construct()
    {
        $this->lists = new ArrayCollection();
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
     * Set code
     *
     * @param  string  $code
     * @return Account
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set secret
     *
     * @param  string  $secret
     * @return Account
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;

        return $this;
    }

    /**
     * Get secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Set created
     *
     * @param  \DateTime $created
     * @return Account
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
     * @return Account
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
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->id;
    }

    /**
     * Set salt
     *
     * @param  string  $salt
     * @return Account
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->getSecret();
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return array('ROLE_USER');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * Get lists
     *
     * @return ArrayCollection
     */
    public function getLists()
    {
        return $this->lists;
    }
}
