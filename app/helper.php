<?php

declare(strict_types=1);

function flash(string $key): ?string
{
  if (!isset($_SESSION['flash'][$key]))
    return null;
  $msg = $_SESSION['flash'][$key];
  unset($_SESSION['flash'][$key]);
  return $msg;
}

function http_response_405(): void
{
  http_response_code(405);
  header("Allow: POST");
  echo 'Method Not Allowed';
}

function http_response_404(): void
{
  http_response_code(404);
  header("Allow: POST");
  echo 'Not Found';
}

function setSticky(string $title): void
{
  // $_SESSION['flash']['error'] = 'checked';
  // $_SESSION['flash']['error'] = $title;
  $_SESSION['oldTitle'] = $title;
}

function getSticky(): ?string
{
  if (!isset($_SESSION['oldTitle'])) {
    return null;
  }
  $title = $_SESSION['oldTitle'];
  unset($_SESSION['oldTitle']);
  return $title;
}

function html_helper($s): string
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
