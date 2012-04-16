<?php
/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Lib;

class SubjectIdentification implements SubjectIdentificationInterface
{
    
    /**
     * {@inheritdoc}
     */
    public function identify($longCode, $subjectName) {
        $mediumCode = $this->getMediumCode($longCode);
        $faculty = $this->getFaculty($mediumCode);
        if ($faculty == 'FMFI') {
            $shortCode = $this->getShortCode($mediumCode);
            return array(
                'code' => $shortCode,
                'name' => $subjectName,
                'slug' => $this->slugify($shortCode),
            );
        }
        else {
            return array(
                'code' => $mediumCode,
                'name' => $subjectName,
                'slug' => $this->slugify($mediumCode . '-' . $subjectName),
            );
        }
    }
    
    private function transliterate($string)
    {
        $oldLocale = setlocale(LC_CTYPE, "0");
        $newLocale = 'en_US.UTF-8';

        $status = setlocale(LC_CTYPE, $newLocale);
        $result = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $test = iconv('UTF-8', 'ASCII//TRANSLIT', 'Žltučký kôň Üʿö');
        setlocale(LC_CTYPE, $oldLocale);

        if ($status === FALSE) {
            throw new \Exception("Nepodarilo sa setlocale($newLocale).");
        }
        if ($test !== 'Zltucky kon U?o') {
            throw new \Exception('Transliteracia nefunguje.');
        }
        return $result;
    }
    
    private function slugify($string)
    {
        $slug = $this->transliterate($string);
        $slug = preg_replace('@[^a-zA-Z0-9_]@', '-', $slug);
        $slug = preg_replace('@-+@', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    private function getFaculty($longCode)
    {
        $parts = explode(".", $longCode);
        return $parts[0];
    }
    
    private function getMediumCode($longCode)
    {
        // Ozajstny "dlhy kod" je (aspon na FMFI) tvaru "FMFI.KI/1-INF-150/1",
        // ale v importe z AISu su len prve dve casti, "FMFI.KI" a "1-INF-150".
        // Aby sme mohli zarucit, ze algoritmus sa bude spravat rovnako, ci uz
        // sa predmet importuje z niekoho zapisneho listu alebo pri uvodnom
        // importe, musime tu poslednu cast zahodit. To je skoda, lebo by mozno
        // mohla byt uzitocna pri odlisovani duplikatov, ale holt chcelo by to
        // lepsi import.
        return preg_replace('@^([^/]*/[^/]*).*$@', '\1', $longCode);
    }

    private function getShortCode($longCode)
    {
        $matches = array();
        if (preg_match('@^[^/]*/([^/]+)@', $longCode, $matches) !== 1) {
            // Nevieme zistit kratky kod
            return $longCode;
        }

        return $matches[1];
    }
    
}
