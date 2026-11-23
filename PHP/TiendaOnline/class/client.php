<?php
class Client
{
  private string $email;
  private string $password;
  private string $name;
  private string $surname;

  public function __construct(string $name, string $surname, string $email, string $password)
  {
    $this->email = $email;
    $this->password = $password;
    $this->name = $name;
    $this->surname = $surname;
  }

  public function getName(): string
  {
    return $this->name;
  }

  public function getSurname(): string
  {
    return $this->surname;
  }

  public function getEmail(): string
  {
    return $this->email;
  }

  public function getPassword(): string
  {
    return $this->password;
  }
}
