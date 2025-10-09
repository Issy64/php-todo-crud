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

function stickyInput(): ?string
{
  if(!isset($_SESSION['oldTitle'])){
    return null;
  }
  $title = $_SESSION['oldTitle'];
  unset($_SESSION['oldTitle']);
  return $title;

}