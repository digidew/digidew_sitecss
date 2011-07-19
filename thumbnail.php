<?php
    # Nachfolgende Funktion ist für die Ausgabe eventueller Fehler als Bild zuständig
    function error_message($text = ''){
        $img = imagecreatetruecolor(strlen($text) * 7, 20); // Erstellt ein neues Bild
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255)); // Malt das bild weiß aus
        imagestring($img, 2, 0, 0, $text, imagecolorallocate($img, 0, 0, 0)); // Schreibt den Text der der Funktion übergeben wurde auf das Bild
        imagepng($img); // Gibt das Bild aus
        imagedestroy($img); // Löscht das Bild aus dem Arbeitsspeicher des Servers
    }
    $img_src = $_GET['src']; // Pfad zum Bild aus welchem das Thumbnail erstellt werden soll
    $cache_dir = './cache'; // Pfad zum Cache Verzeichnis wo später die Bilder gespeichert werden
    $cache = true; // Gibt an ob die Bilder aus dem Cache geladen werden sollen
    # Überprüft ob ein Bildpfad übergeben wurde
    if (!isset($_GET['src'])){
        error_message('Es wurde kein Bildpfad übergeben aus dem ein Thumbnail ezeugt werden könnte'); // Gibt eine Fehlermeldung aus
    }
    # Auslesen der Bildgröße und des Bildtyps
    $image_infos = @getimagesize($img_src) or error_message('Auf das Bild kann nicht zugegriffen werden');
    $width = $image_infos[0];
    $height = $image_infos[1];
    $type = $image_infos[2];
    $mime = $image_infos['mime'];
    # Berechnung der Maße des Thumbnails
    if (isset($_GET['p']) && !isset($_GET['w']) && !isset($_GET['h'])){ // Überprüfen ob die Bildgröße proportional berechnet werden soll
        if($width < $height) { // Überprüfen ob das Bild Hoch- oder Querformat ist
            $new_width  = ceil(($_GET['p'] / $height) * $width);
            $new_height = intval($_GET['p']); // Zuweisen der neuen Höhe
        } else {
            $new_height = ceil(($_GET['p'] / $width) * $height);
            $new_width = intval($_GET['p']); // Zuweisen der neuen Breite
        }
    } else if (isset($_GET['w']) && !isset($_GET['h']) && !isset($_GET['p'])){ // Überprüfen ob die Breite oder die Höhe berechnent werden soll
        $new_width = intval($_GET['w']); // Zuweisen der neuen Breite
        $new_height = ceil($height * $new_width / $width); // Berechnen der neuen Höhe
    } else if (isset($_GET['h']) && !isset($_GET['w']) && !isset($_GET['p'])){ // Überprüfen ob die Breite oder die Höhe berechnent werden soll
        $new_height = intval($_GET['h']); // Zuweisen der neuen Höhe
        $new_width = ceil($width * $new_height / $height); // Berechnen der neuen Breite
    } else if (isset($_GET['h']) && isset($_GET['w']) && isset($_GET['p'])){
        $new_height = intval($_GET['h']); // Zuweisen der neuen Höhe
        $new_width = intval($_GET['w']); // Zuweisen der neuen Breite
    } else {
        error_message('Es muss entweder die neu Höhe oder die neu Breite angegeben werden.'); // Fehlermeldung ausgeben
    }
    # Prüft ob das Chache Verzeichnis existiert bzw. benötigt wird und legt dieses eventuell an
    if ($cache === true && !file_exists($cache_dir)){
        mkdir($cache_dir) or error_message('Das Cache Verzeichnis konnte nicht angelegt werden'); // Legt das Cache Verzeichnis an. Sollte dies nicht möglich sein, so wird ein Fehler ausgegeben
        chmod($cache_dir, 0777); // Gibt dem Cache Verzeichniss die nötigen Schreib- und Lese Rechte
    }
    # Ermitteln des Bildtypes und Erstellung des Thumbnails
    switch ($type){
        case 1:
            header('Content-type: '.$mime); // Header ausgeben
            if (imagetypes() & IMG_GIF){ // Überprüfen ob das Bildformat untestützt wird
                if (!file_exists($cache_dir.'/'.md5($img_src).'.gif')){ // Wenn das Thumbnail nicht existiert wird es erstellt
                    $orginal = imagecreatefromgif($img_src) or error_message('Das Bild wurde nicht gefunden'); // Bild aus dem Orginalbild erstellen
                    $thumb = imagecreatetruecolor($new_width, $new_height); // Das Thumbnailbild erstellen
                    imagecopyresampled($thumb, $orginal, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    if ($cache === true){ // Prüft ob das Bild gespeichert werden soll
                        imagegif($thumb, $cache_dir.'/'.md5($img_src).'.gif') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild speichern
                    }
                    imagegif($thumb); // Bild ausgeben
                } else {
                    readfile($cache_dir.'/'.md5($img_src).'.gif') or error_message('Das Bild wurde nicht gefunden'); // Bild ausgeben
                }
            } else {
                error_message('GIF Bilder werden nicht unterstützt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterstützt wird
            }
            break;
        case 2:
            header('Content-type: '.$mime); // Header ausgeben
            if (imagetypes() & IMG_JPG){ // Überprüfen ob das Bildformat untestützt wird
                if (!file_exists($cache_dir.'/'.md5($img_src).'.jpg')){ // Wenn das Thumbnail nicht existiert wird es erstellt
                    $orginal = imagecreatefromjpeg($img_src) or error_message('Das Bild wurde nicht gefunden'); // Bild aus dem Orginabild erstellen
                    $thumb = imagecreatetruecolor($new_width, $new_height); // Das Thumbnailbild erstellen
                    imagecopyresampled($thumb, $orginal, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    if ($cache === true){ // Prüft ob das Bild gespeichert werden soll
                        imagejpeg($thumb, $cache_dir.'/'.md5($img_src).'.jpg') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild speichern
                    }
                    imagejpeg($thumb); // Bild ausgeben
                } else {
                    readfile($cache_dir.'/'.md5($img_src).'.jpg') or error_message('Das Bild wurde nicht gefunden'); // Bild ausgeben
                }
            } else {
                error_message('JPEG Bilder werden nicht unterstützt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterstützt wird
            }
            break;
        case 3:
            header('Content-type: '.$mime); // Header ausgeben
            if (imagetypes() & IMG_PNG){ // Überprüfen ob das Bildformat untestützt wird
                if (!file_exists($cache_dir.'/'.md5($img_src).'.png')){ // Wenn das Thumbnail nicht existiert wird es erstellt
                    $orginal = imageCreateFromPNG($img_src) or error_message('Das Bild wurde nicht gefunden'); // Bild aus dem Orginalbild erstellen
                    $thumb = imagecreatetruecolor($new_width, $new_height); // Das Thumbnailbild erstellen
                    imagecopyresampled($thumb, $orginal, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    if ($cache === true){ // Prüft ob das Bild gespeichert werden soll
                        imagepng($thumb, $cache_dir.'/'.md5($img_src).'.png') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild speichern
                    }
                    imagepng($thumb); // Bild ausgeben
                } else {
                    readfile($cache_dir.'/'.md5($img_src).'.png') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild ausgeben
                }
            } else {
                error_message('PNG Bilder werden nicht unterstützt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterstützt wird
            }
            break;
        default:
            error_message('Das Bildformat wird nicht unterstützt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterstützt wird
    }
    # Löscht das Bild aus dem Speicher des Servers falls es existiert
    if (isset($thumb)){
        imagedestroy($thumb);
    }
?>