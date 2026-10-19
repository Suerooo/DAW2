<?php
class Vehicle
{
  private string $plate;
  private string $brand;
  private string $model;
  private string $color;
  private int $doors;
  private int $seats;
  private string $transmission;
  private string $motorization;
  private string $vehicle_class;

  public function __construct(string $plate, string $brand, string $model, string $color, int $doors, int $seats, string $transmission, string $motorization, string $vehicle_class)
  {
    $this->plate = $plate;
    $this->brand = $brand;
    $this->model = $model;
    $this->color = $color;
    $this->doors = $doors;
    $this->seats = $seats;
    $this->transmission = $transmission;
    $this->motorization = $motorization;
    $this->vehicle_class = $vehicle_class;
  }

  public function get_plate(): string
  {
    return $this->plate;
  }

  public function get_brand(): string
  {
    return $this->brand;
  }

  public function get_model(): string
  {
    return $this->model;
  }

  public function get_color(): string
  {
    return $this->color;
  }

  public function get_doors(): int
  {
    return $this->doors;
  }

  public function get_seats(): int
  {
    return $this->seats;
  }

  public function get_transmission(): string
  {
    return $this->transmission;
  }

  public function get_motorization(): string
  {
    return $this->motorization;
  }

  public function get_vehicle_class(): string
  {
    return $this->vehicle_class;
  }
}
