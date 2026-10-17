<?php
class Client
{
  private int $user_id;
  private string $dni;
  private string $name;
  private string $surname;
  private string $birthdate;
  private string $driver_license;

  public function __construct(int $user_id, string $dni, string $name, string $surname, string $birthdate, string $driver_license)
  {
    $this->user_id = $user_id;
    $this->dni = $dni;
    $this->name = $name;
    $this->surname = $surname;
    $this->birthdate = $birthdate;
    $this->driver_license = $driver_license;
  }

  public function get_user_id(): int
  {
    return $this->user_id;
  }

  public function get_dni(): string
  {
    return $this->dni;
  }

  public function get_name(): string
  {
    return $this->name;
  }

  public function get_surname(): string
  {
    return $this->surname;
  }

  public function get_birthdate(): string
  {
    return $this->birthdate;
  }

  public function get_driver_license(): string
  {
    return $this->driver_license;
  }
}
