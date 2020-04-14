<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\ResetPasswordAction;
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
 *          },
 *     "normalization_context"={
 * "groups"={"get"}
 *     }
 *     },
 *      "put-reset-password"={
 *      "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *      "method"="PUT",
 *     "path"="/users/{id}/reset-password",
 *     "controller"=ResetPasswordAction::class,
 *     "denormalization_context"={
 *          "groups"={"put-reset-password"}
 *          }
 *     }
 * },
 *     collectionOperations={
 *     "post"={
 *     "denormalization_context"={
 *          "groups"={"post"}
 *     },
 *     "normalization_context"={
 *          "groups"={"get"}
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

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "post", "get-comment-with-author", "get-blog-post-with-author"})
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(min=6, max=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"put", "post"})
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *     message="Passwort muss mind. 7 zeichen lang sein und sowohl Klein- als auch Großbuchstaben und eine Zahl beinhalten."
     * )
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"post"})
     * @Groups({"post"})
     * @Assert\Expression(
     *     "this.getPassword() === this.getRetypedPassword()",
     *     message="Passwörter stimmen nicht überein.",
     *     groups={"post"}
     * )
     */
    private $retypedPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{7,}/",
     *     message="Passwort muss mind. 7 zeichen lang sein und sowohl Klein- als auch Großbuchstaben und eine Zahl beinhalten.",
     *     groups={"put-reset-password"}
     * )
     */
    private $newPassword;

    /**
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @Groups({"put-reset-password"})
     * @Assert\Expression(
     *     "this.getNewPassword() === this.getNewRetypedPassword()",
     *     message="Passwörter stimmen nicht überein."
     * )
     */
    private $newRetypedPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank(groups={"put-reset-password"})
     * @UserPassword(groups={"put-reset-password"})
     */
    private $oldPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "post", "get-comment-with-author", "get-blog-post-with-author"})
     * @Assert\NotBlank(groups={"post", "put"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"put", "post", "get-admin", "get-owner"})
     * @Assert\NotBlank(groups={"post", "put"})
     * @Assert\Email(groups={"post", "put"})
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     * @Groups({"get"})
     *
     */

    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"get"})
     *
     */
    private $comments;

    /**
     * @ORM\Column(type="simple_array", length=200)
     * @Groups({"get-admin", "get-owner"})
     */
    private $roles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $passwordChangeDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $confirmationToken;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
        $this->enabled = false;
        $this->confirmationToken = null;
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

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;

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


    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }


    public function setNewPassword($newPassword): void
    {
        $this->newPassword = $newPassword;
    }


    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }


    public function setNewRetypedPassword($newRetypedPassword): void
    {
        $this->newRetypedPassword = $newRetypedPassword;
    }


    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }


    public function setOldPassword($oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }


    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }


    public function setPasswordChangeDate($passwordChangeDate): void
    {
        $this->passwordChangeDate = $passwordChangeDate;
    }


    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }


    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }


    public function setConfirmationToken($confirmationToken): void
    {
        $this->confirmationToken = $confirmationToken;
    }





}
