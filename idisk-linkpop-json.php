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

// Web-Adresse zum Abruf der Link-Popularity
define('LINKPOP_URL', 'https://immobiliendiskussion.de/linkpopularity');

// SSL-Zertifikat der ImmobilienDiskussion beim Abruf prüfen (erlaubt ist false, true)
define('LINKPOP_VALIDATE_CERTIFICATE', true);

// Pfad zum Verzeichnis zur Speicherung temporärer Dateien. Wenn der Wert leer ist,
// wird das Verzeichnis automatisch aus den PHP-Einstellungen ermittelt.
// Es müssen Schreibrechte für das PHP-Skript auf dem Verzeichnis vorliegen.
define('LINKPOP_TEMP_DIR', '');

// Dauer in Sekunden, wie lange eine zwischengespeicherte Linkliste vorgehalten
// wird. (86400 Sekunden = 1 Tag)
define('LINKPOP_TEMP_LIFETIME', 86400);

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
$url = LINKPOP_URL.'/json/'.LINKPOP_KEY;
$content = null;

// detect path to temporary directory
$tempDir = null;
if (defined('LINKPOP_TEMP_DIR') && LINKPOP_TEMP_DIR!='') {
  $tempDir = LINKPOP_TEMP_DIR;
}
if ($tempDir==null || !is_dir($tempDir) || !is_writable($tempDir)) {
  $tempDir = ini_get('upload_tmp_dir');
  if ($tempDir==='' || $tempDir===false) {
    $tempDir = null;
  }
}
if ($tempDir==null || !is_dir($tempDir) || !is_writable($tempDir)) {
  $tempDir = sys_get_temp_dir();
}

// create path to temporary file
$tempFile = null;
if ($tempDir!=null && is_dir($tempDir) && is_writable($tempDir)) {
  $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'idisk-linkpop-' . md5( $url ) . '.txt';
}

// load content from temporary file
$tempContent = null;
if ($tempFile!=null && is_file($tempFile)) {
  $tempContent = file_get_contents($tempFile);
}

// use content from temporary file, if its maximal age is not exceeded
$minAge = time() - LINKPOP_TEMP_LIFETIME;
if ($tempFile!=null && is_file($tempFile) && filemtime($tempFile)>$minAge) {
  $content =& $tempContent;
}

// download content otherwise
else {

  // load the content via file_get_contents,
  // if allow_url_fopen is enabled in the PHP runtime
  if (ini_get('allow_url_fopen')=='1' || ini_set('allow_url_fopen', '1')!==false) {
    $context = null;

    // disable certificate checks, if it was explicitly disabled
    // or if PHP does not support SNI (Server Name Indication)
    if (!defined('LINKPOP_VALIDATE_CERTIFICATE') || !LINKPOP_VALIDATE_CERTIFICATE || !defined('OPENSSL_TLSEXT_SERVER_NAME') || !OPENSSL_TLSEXT_SERVER_NAME) {
      $opts = array(
        'ssl'=>array(
          'verify_peer' => false,
          'verify_peer_name' => false,
        )
      );
      $context = stream_context_create($opts);
    }

    $content = file_get_contents($url, false, $context);
  }

  // alternatively load the content via cURL,
  // if it is available in the PHP runtime
  else if (function_exists('curl_init')) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);

    // disable certificate checks, if it was explicitly disabled
    if (!defined('LINKPOP_VALIDATE_CERTIFICATE') || !LINKPOP_VALIDATE_CERTIFICATE) {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    }

    $content = curl_exec($curl);
    curl_close($curl);
  }

  // show an error message, if the download is not possible
  else {
    echo '<p>'
      . '<strong>ACHTUNG:</strong> '
      . 'Die PHP-Installation auf Ihrer Webseite erlaubt keinen Zugriff auf den Server der ImmobilienDiskussion. '
      . 'Kontaktieren Sie ggf. Ihren Webspace-Provider und fragen Sie nach Aktivierung der PHP-Option <q>allow_url_fopen</q> oder des PHP-Moduls <q>cURL</q>. '
      . '</p>';
  }

  if ($content===false || $content=='') {
    $content = null;
  }

  // write downloaded content into the temporary file
  if ($content!=null && $tempFile!=null) {
    file_put_contents($tempFile, $content);
  }

  // use temporary content, in case the download failed
  if ($content==null && $tempContent!=null) {
    $content =& $tempContent;
  }
}

// parse downloaded content as JSON and generate output
if ($content!=null) {
  $result = json_decode($content);
  if ($result==null) {
    echo '<p>'
      . '<strong>ACHTUNG:</strong> '
      . 'Die Linkliste konnte nicht abgerufen werden. Sollte das Problem dauerhaft auftreten, kontaktieren Sie bitte die Moderation der ImmobilienDiskussion. '
      . '</p>';
  }
  else {
    echo '<ul class="idisk-linkpop" data-idisk-key="' . htmlspecialchars($result->key) .'" data-idisk-stamp="' . htmlspecialchars($result->stamp) . '">' . "\n";
    foreach ($result->items as $item) {
      echo '<li class="idisk-linkpop-item"><p>'
        . '<a href="' . htmlspecialchars($item->link) . '" target="_blank" class="idisk-linkpop-item-title">' . htmlspecialchars($item->title) . '</a><br />'
        . '<span>aus ' . htmlspecialchars($item->postCode) . ' ' . htmlspecialchars($item->city) . ' / ' . htmlspecialchars($item->countryName) . '</span>'
        . '</p></li>' . "\n";
    }
    echo '</ul>' . "\n";
  }
}
?>
<!-- Ende der Link-Popularity -->

</body>
</html>
