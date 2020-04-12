<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ApiResource(
 *     normalizationContext={"groups"={"get"}},
 *     itemOperations={
 *     "get"={
 *     "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *     "normalization_context"={
 *          "groups"={"get"}
 *     }
 *     },
 *     "put"={
 *      "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *     "denormalization_context"={
 *          "groups"={"put"}
 *     }
 *     }
 * },
 *     collectionOperations={
 *     "post"={
 *     "denormalization_context"={
 *          "groups"={"post"}
 *     }
 *     }
 * }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 */
class User implements UserInterface
{

    const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    const ROLE_WRITER = 'ROLE_WRITER';
    const ROLE_EDITOR = 'ROLE_EDITOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "post"})
     * @Assert\NotBlank()
     * @Assert\Length(min=6, max=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
    * @Groups({"put", "post"})
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *     message="Passwort muss mind. 7 zeichen lang sein und sowohl Klein- als auch Großbuchstaben und eine Zahl beinhalten."
     * )
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Groups({"put", "post"})
     * @Assert\Expression(
     *     "this.getPassword() === this.getRetypedPassword()",
     *     message="Passwörter stimmen nicht überein."
     * )
     */
    private $retypedPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "put", "post"})
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"put", "post"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     *
     */

    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     *
     */
    private $comments;


    public function __construct()
    {
        $this->posts= new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {

    }


    public function getRetypedPassword()
    {
        return $this->retypedPassword;
    }


    public function setRetypedPassword($retypedPassword): void
    {
        $this->retypedPassword = $retypedPassword;
    }




}
