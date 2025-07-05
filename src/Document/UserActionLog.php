<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document(collection: 'user_action_logs')]
class UserActionLog
{
    #[ODM\Id]
    private $id;

    #[ODM\Field(type: 'string')]
    private $username;

    #[ODM\Field(type: 'string')]
    private $action;

    #[ODM\Field(type: 'bool')]
    private $success;

    #[ODM\Field(type: 'date')]
    private $date;

    public function __construct(string $username, string $action, bool $success)
    {
        $this->username = $username;
        $this->action = $action;
        $this->success = $success;
        $this->date = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }
    
    public function setDate(\DateTime $date): void
    {
        $this->date = $date;
    }

}
