<?php
require_once __DIR__ . '/cookie.php';

class Cart
{
  private array $products = [];

  public function __construct()
  {
    foreach (readShopCookie('shop_cart') as $id => $amount) {
      if (is_int($id) && $id > 0 && $id <= 4294967295 &&
          is_int($amount) && $amount > 0 && $amount <= 4294967295) {
        $this->products[$id] = $amount;
      }
    }
  }

  public function getProducts(): array
  {
    return $this->products;
  }

  public function addProductCart(int $id, int $amount, int $stock): void
  {
    $new_amount = ($this->products[$id] ?? 0) + $amount;
    if ($new_amount > $stock) {
      throw new RuntimeException("No hay stock suficiente para esa cantidad");
    }

    $products = $this->products;
    $products[$id] = $new_amount;
    saveShopCookie('shop_cart', $products);
    $this->products = $products;
  }

  public function removeProductCart(int $id): void
  {
    unset($this->products[$id]);
    saveShopCookie('shop_cart', $this->products);
  }

  public function clearCart(): void
  {
    saveShopCookie('shop_cart', []);
    $this->products = [];
  }
}
