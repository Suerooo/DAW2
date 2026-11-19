<?php
class Cart
{
  private string $;
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

  public function get_name(): string
  {
    return $this->name;
  }

  public function get_surname(): string
  {
    return $this->surname;
  }

  public function get_email(): string
  {
    return $this->email;
  }

  public function get_password(): string
  {
    return $this->password;
  }
  
  public function showCart() {}

  public function addProductCart() {}

  public function removeProductCart() {}
}
