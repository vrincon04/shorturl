# Short Url
API que se encarga de minificar una url larga usando una variante del algoritmo base_convert

La variante del algoritmo permite generar una cadena secuencial entre 1 y 14 caracteres de longitud en función del número pasado como argumento a la función. La cadena secuencial generada solo letras del alfabeto desde la A a la Z mayúscula y de la A a la Z minúscula.

## Descripción del Algoritmo

```php
/**
 *  Converts an integer into the alphabet base (A-z).
 * 
 * @param int $number This is the number to convert.
    * 
    * @return string
    */
public static function numberToAlphabet($number)
{
    // Create and fill an array with alphabet(A-Za-z).
    $alphabet = array_merge(range('A', 'Z'), range('a', 'z'));
    //Assign the size of $alphabet array to the variable $length.
    $length = count($alphabet);
    //This variable will hold the output generated.
    $result = '';
    //Make sure the parameter is greater than or equal to zero so we can star lopping.
    for ($i = 1; $number >= 0; $i++) {
        // The code below limits the number to the $alphabet array size
        $formula = abs(($number % pow($length, $i) / pow($length, $i - 1)));
        // Comcat the current result with the previous
        $result = $alphabet[$formula] . $result;
        // Reduce the number with the size of the array raised to the iteration
        $number -= pow($length, $i);
    }

    return $result;
}
```

## Usar el Algoritmo

```php
use App\Libraries\Helper;

Helper::numberToAlphabet(0) # returns 'A'
Helper::numberToAlphabet(26) # returns 'a'
Helper::numberToAlphabet(26) # returns 'Ac'
```

## Instalación de la Aplicación Usando Docker Compose
Descargar E Instalar Short Url 

## Official Documentation

Documentation for the framework can be found on the [Lumen website](https://lumen.laravel.com/docs).

## Security Vulnerabilities

If you discover a security vulnerability within Lumen, please send an e-mail to Taylor Otwell at taylor@laravel.com. All security vulnerabilities will be promptly addressed.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
