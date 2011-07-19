<?php
    # Nachfolgende Funktion ist f�r die Ausgabe eventueller Fehler als Bild zust�ndig
    function error_message($text = ''){
        $img = imagecreatetruecolor(strlen($text) * 7, 20); // Erstellt ein neues Bild
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255)); // Malt das bild wei� aus
        imagestring($img, 2, 0, 0, $text, imagecolorallocate($img, 0, 0, 0)); // Schreibt den Text der der Funktion �bergeben wurde auf das Bild
        imagepng($img); // Gibt das Bild aus
        imagedestroy($img); // L�scht das Bild aus dem Arbeitsspeicher des Servers
    }
    $img_src = $_GET['src']; // Pfad zum Bild aus welchem das Thumbnail erstellt werden soll
    $cache_dir = './cache'; // Pfad zum Cache Verzeichnis wo sp�ter die Bilder gespeichert werden
    $cache = true; // Gibt an ob die Bilder aus dem Cache geladen werden sollen
    # �berpr�ft ob ein Bildpfad �bergeben wurde
    if (!isset($_GET['src'])){
        error_message('Es wurde kein Bildpfad �bergeben aus dem ein Thumbnail ezeugt werden k�nnte'); // Gibt eine Fehlermeldung aus
    }
    # Auslesen der Bildgr��e und des Bildtyps
    $image_infos = @getimagesize($img_src) or error_message('Auf das Bild kann nicht zugegriffen werden');
    $width = $image_infos[0];
    $height = $image_infos[1];
    $type = $image_infos[2];
    $mime = $image_infos['mime'];
    # Berechnung der Ma�e des Thumbnails
    if (isset($_GET['p']) && !isset($_GET['w']) && !isset($_GET['h'])){ // �berpr�fen ob die Bildgr��e proportional berechnet werden soll
        if($width < $height) { // �berpr�fen ob das Bild Hoch- oder Querformat ist
            $new_width  = ceil(($_GET['p'] / $height) * $width);
            $new_height = intval($_GET['p']); // Zuweisen der neuen H�he
        } else {
            $new_height = ceil(($_GET['p'] / $width) * $height);
            $new_width = intval($_GET['p']); // Zuweisen der neuen Breite
        }
    } else if (isset($_GET['w']) && !isset($_GET['h']) && !isset($_GET['p'])){ // �berpr�fen ob die Breite oder die H�he berechnent werden soll
        $new_width = intval($_GET['w']); // Zuweisen der neuen Breite
        $new_height = ceil($height * $new_width / $width); // Berechnen der neuen H�he
    } else if (isset($_GET['h']) && !isset($_GET['w']) && !isset($_GET['p'])){ // �berpr�fen ob die Breite oder die H�he berechnent werden soll
        $new_height = intval($_GET['h']); // Zuweisen der neuen H�he
        $new_width = ceil($width * $new_height / $height); // Berechnen der neuen Breite
    } else if (isset($_GET['h']) && isset($_GET['w']) && isset($_GET['p'])){
        $new_height = intval($_GET['h']); // Zuweisen der neuen H�he
        $new_width = intval($_GET['w']); // Zuweisen der neuen Breite
    } else {
        error_message('Es muss entweder die neu H�he oder die neu Breite angegeben werden.'); // Fehlermeldung ausgeben
    }
    # Pr�ft ob das Chache Verzeichnis existiert bzw. ben�tigt wird und legt dieses eventuell an
    if ($cache === true && !file_exists($cache_dir)){
        mkdir($cache_dir) or error_message('Das Cache Verzeichnis konnte nicht angelegt werden'); // Legt das Cache Verzeichnis an. Sollte dies nicht m�glich sein, so wird ein Fehler ausgegeben
        chmod($cache_dir, 0777); // Gibt dem Cache Verzeichniss die n�tigen Schreib- und Lese Rechte
    }
    # Ermitteln des Bildtypes und Erstellung des Thumbnails
    switch ($type){
        case 1:
            header('Content-type: '.$mime); // Header ausgeben
            if (imagetypes() & IMG_GIF){ // �berpr�fen ob das Bildformat untest�tzt wird
                if (!file_exists($cache_dir.'/'.md5($img_src).'.gif')){ // Wenn das Thumbnail nicht existiert wird es erstellt
                    $orginal = imagecreatefromgif($img_src) or error_message('Das Bild wurde nicht gefunden'); // Bild aus dem Orginalbild erstellen
                    $thumb = imagecreatetruecolor($new_width, $new_height); // Das Thumbnailbild erstellen
                    imagecopyresampled($thumb, $orginal, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    if ($cache === true){ // Pr�ft ob das Bild gespeichert werden soll
                        imagegif($thumb, $cache_dir.'/'.md5($img_src).'.gif') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild speichern
                    }
                    imagegif($thumb); // Bild ausgeben
                } else {
                    readfile($cache_dir.'/'.md5($img_src).'.gif') or error_message('Das Bild wurde nicht gefunden'); // Bild ausgeben
                }
            } else {
                error_message('GIF Bilder werden nicht unterst�tzt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterst�tzt wird
            }
            break;
        case 2:
            header('Content-type: '.$mime); // Header ausgeben
            if (imagetypes() & IMG_JPG){ // �berpr�fen ob das Bildformat untest�tzt wird
                if (!file_exists($cache_dir.'/'.md5($img_src).'.jpg')){ // Wenn das Thumbnail nicht existiert wird es erstellt
                    $orginal = imagecreatefromjpeg($img_src) or error_message('Das Bild wurde nicht gefunden'); // Bild aus dem Orginabild erstellen
                    $thumb = imagecreatetruecolor($new_width, $new_height); // Das Thumbnailbild erstellen
                    imagecopyresampled($thumb, $orginal, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    if ($cache === true){ // Pr�ft ob das Bild gespeichert werden soll
                        imagejpeg($thumb, $cache_dir.'/'.md5($img_src).'.jpg') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild speichern
                    }
                    imagejpeg($thumb); // Bild ausgeben
                } else {
                    readfile($cache_dir.'/'.md5($img_src).'.jpg') or error_message('Das Bild wurde nicht gefunden'); // Bild ausgeben
                }
            } else {
                error_message('JPEG Bilder werden nicht unterst�tzt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterst�tzt wird
            }
            break;
        case 3:
            header('Content-type: '.$mime); // Header ausgeben
            if (imagetypes() & IMG_PNG){ // �berpr�fen ob das Bildformat untest�tzt wird
                if (!file_exists($cache_dir.'/'.md5($img_src).'.png')){ // Wenn das Thumbnail nicht existiert wird es erstellt
                    $orginal = imageCreateFromPNG($img_src) or error_message('Das Bild wurde nicht gefunden'); // Bild aus dem Orginalbild erstellen
                    $thumb = imagecreatetruecolor($new_width, $new_height); // Das Thumbnailbild erstellen
                    imagecopyresampled($thumb, $orginal, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    if ($cache === true){ // Pr�ft ob das Bild gespeichert werden soll
                        imagepng($thumb, $cache_dir.'/'.md5($img_src).'.png') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild speichern
                    }
                    imagepng($thumb); // Bild ausgeben
                } else {
                    readfile($cache_dir.'/'.md5($img_src).'.png') or error_message('Das Bild konnte nicht gespeichert werden'); // Bild ausgeben
                }
            } else {
                error_message('PNG Bilder werden nicht unterst�tzt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterst�tzt wird
            }
            break;
        default:
            error_message('Das Bildformat wird nicht unterst�tzt'); // Fehlermeldung ausgeben, wenn das Bildformat nicht unterst�tzt wird
    }
    # L�scht das Bild aus dem Speicher des Servers falls es existiert
    if (isset($thumb)){
        imagedestroy($thumb);
    }
?>