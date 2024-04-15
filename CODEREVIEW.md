# Code review

Scenario: A junior submits the following code for review:

```php
<?php
public function validateStringFromInput(string $input, string $target): void {
    $t = intval(trim($target));

    for ($i = 0; $i < $t; $i++) {
        $s = trim($input);
        $t = trim($target);

        $a = str_split($s);
        $b = str_split($t);

        $a = array_reverse($a);
        $b = array_reverse($b);

        $c = [];
        while (count($b) !== 0 && count($a) !== 0) {
            if ($a[0] === $b[0]) {
                $c[] = array_shift($b);
                array_shift($a);
            } elseif ($a[0] !== $b[0] && count($a) !== 1) {
                array_shift($a);
                array_shift($a);
            } elseif ($a[0] !== $b[0] && count($a) === 1) {
                array_shift($a);
            }
        }

        if (count($b) == 0) {
            echo “True”;
        } else {
            echo “False”;
        }
    }
}
```

Based on the following Ticket description:

```txt
You get two strings. You "type" the first string letter by letter, but instead of typing a letter you can also press backspace. Is it possible to
create the second string this way? The result should be shown on the screen.
Note: You can press a maximum of the number of keys equal to the length of the first string
```
