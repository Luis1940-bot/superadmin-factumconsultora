<?php

class ErrorLogger
{
  private static string $logFile = __DIR__ . '/logs/error.log';

  public static function initialize(?string $logFilePath = null): void
  {
    if ($logFilePath) {
      self::$logFile = $logFilePath;
    }
    ini_set('error_log', self::$logFile);

    // Define callable explícito para el manejador de excepciones
    set_error_handler([self::class, 'customErrorHandler']);

    // Usamos una función anónima para especificar el tipo de callable explícitamente
    set_exception_handler([self::class, 'customExceptionHandler']);
  }

  public static function customErrorHandler(int $errno, string $errstr, string $errfile, int $errline): bool
  {
    $errorMessage = "[Error $errno] $errstr in $errfile on line $errline";
    error_log($errorMessage);
    return true;
  }

  // Aquí definimos claramente el tipo del parámetro $exception para evitar el error
  public static function customExceptionHandler(\Throwable $exception): void
  {
    $errorMessage = "[Exception] " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log($errorMessage);
  }
}
