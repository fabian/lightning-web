<?php

namespace Lightning\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\SerializerBundle\Annotation\Exclude;
use JMS\SerializerBundle\Annotation\Groups;

/**
 * Lightning\ApiBundle\Entity\AccessToken
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class AccessToken
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
     * @var string $challenge
     *
     * @ORM\Column(name="challenge", type="string", length=255)
     * @Exclude
     */
    private $challenge;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(name="created", type="datetime")
     * @Exclude
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     * @Exclude
     */
    protected $account;

    /**
     * @var boolean $approved
     *
     * @ORM\Column(name="approved", type="boolean")
     */
    private $approved = false;

    public function __construct(Account $account)
    {
        $this->account = $account;
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
     * Set challenge
     *
     * @param string $challenge
     * @return AccessToken
     */
    public function setChallenge($challenge)
    {
        $this->challenge = $challenge;
    
        return $this;
    }

    /**
     * Get challenge
     *
     * @return string 
     */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return AccessToken
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
     * Set approved
     *
     * @param boolean $approved
     * @return AccessToken
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;
    
        return $this;
    }

    /**
     * Get approved
     *
     * @return boolean 
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Set account
     *
     * @param Lightning\ApiBundle\Entity\Account $account
     * @return AccessToken
     */
    public function setAccount(\Lightning\ApiBundle\Entity\Account $account = null)
    {
        $this->account = $account;
    
        return $this;
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
}