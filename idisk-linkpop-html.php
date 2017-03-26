<?php
/**
 * Integration der von ImmobilienDiskussion.de bereitgestellten Link-Popularity.
 *
 * Informationen zur Verwendung dieses PHP-Skripts finden Sie unter
 * https://immobiliendiskussion.de/wiki/idisk-link-popularity-php
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author Andreas Rudolph <andy@openindex.de>
 * @copyright 2006-2017 OpenIndex.de
 * @license MIT
 */

// Kennung Ihrer Teilnahme an der Link-Popularity
define('LINKPOP_KEY', 'HIER-DIE-KENNUNG-IHRER-LINK-POPULARITY-EINTRAGEN');

// Art der Ausgabe (erlaubt ist: 'html_detailed' oder 'html_table')
define('LINKPOP_TYPE', 'html_detailed');

// Web-Adresse zum Abruf der Link-Popularity
define('LINKPOP_URL', 'https://immobiliendiskussion.de/linkpopularity');

?><!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="keywords" content="Immobilien, immobiliendiskussion, mitglieder, teilnehmer, partner">
  <meta name="description" content="Mitglieder und Partner der ImmobilienDiskussion">
  <meta name="language" content="de">
  <meta name="revisit-after" content="5 days">
  <meta name="robots" content="index,follow">
  <title>Unsere Partner</title>
</head>
<body>
  <h1>Unsere Partner</h1>

<!-- Beginn der Link-Popularity -->
<?php
if (ini_get('allow_url_fopen')=='0' && ini_set('allow_url_fopen', '1')===false) {
  echo '<p>'
    . '<strong>ACHTUNG:</strong> '
    . 'Die PHP-Installation auf Ihrer Webseite erlaubt keinen Zugriff auf den Server der ImmobilienDiskussion. '
    . 'Kontaktieren Sie ggf. Ihren Webspace-Provider und fragen Sie nach Aktivierung der PHP-Option <q>allow_url_fopen</q>. '
    . '</p>';
}
else {
  echo file_get_contents(LINKPOP_URL.'/'.LINKPOP_TYPE.'/'.LINKPOP_KEY);
}
?>
<!-- Ende der Link-Popularity -->

</body>
</html>
