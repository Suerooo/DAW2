<?php
function showSearchResults(): void
{
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  $search = is_string($_GET['search']) ? trim($_GET['search']) : '';
  if (mb_strlen($search) < 2 || mb_strlen($search) > 120) {
    echo '[]';
    return;
  }

  try {
    $products = searchProducts(connectDatabase(), $search);
    saveSearchCategories($products);
    echo json_encode($products);
  } catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(503);
    echo '[]';
  }
}
