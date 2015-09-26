<?php

namespace Xaj\ErgoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xaj\ErgoBundle\Entity\Boat;

/**
 * Rower
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Xaj\ErgoBundle\Entity\RowerRepository")
 */
class Rower
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\ManyToOne(targetEntity="Xaj\ErgoBundle\Entity\Boat", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $boat;

    /**
     * @var date
     * @ORM\Column(name="birthdate", type="date", nullable=true)
     */
    private $birthdate;

    /**
     * @ORM\Column(name="license", type="string", length=255, nullable=true)
     */
    private $license;


    public function __construct(Boat $boat)
    {
        $this->setBoat($boat);
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
     * Set lastname
     *
     * @param string $lastname
     * @return Rower
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string 
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Rower
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set boat
     *
     * @param \Xaj\ErgoBundle\Entity\Boat $boat
     * @return Rower
     */
    public function setBoat(\Xaj\ErgoBundle\Entity\Boat $boat)
    {
        $this->boat = $boat;

        return $this;
    }

    /**
     * Get boat
     *
     * @return \Xaj\ErgoBundle\Entity\Boat 
     */
    public function getBoat()
    {
        return $this->boat;
    }

    /**
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return Rower
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set license
     *
     * @param string $license
     * @return Rower
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * Get license
     *
     * @return string 
     */
    public function getLicense()
    {
        return $this->license;
    }
}
