<?php

require __DIR__ . '/../../utils/constants.php';
require __DIR__ . '/../../utils/service_helpers.php';
class LokeridRequest {

    public static function getDetailLoker($url) {

        $result = curlGet2($url);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($result);
        libxml_clear_errors();
        $xpathDetailLoker = new DOMXPath($dom);
        
        $panelInformasi = $xpathDetailLoker->query('//div[contains(@class, "panel-body padding-horizontal-double padding-vertical-double")]');
        $panelInformasi2= $xpathDetailLoker->query('//div[contains(@class, "panel-heading padding-horizontal-double padding-vertical-double")]'); 
        $posteDate      = trim($panelInformasi2->item(0)->nodeValue);
        $description    = trim($panelInformasi->item(1)->nodeValue);
        $levelPekerjaan = trim($panelInformasi->item(0)->getElementsByTagName('a')->item(0)->nodeValue);
        $pendidikan     = trim($panelInformasi->item(0)->getElementsByTagName('a')->item(1)->nodeValue);
        $tipePekerjaan  = trim($panelInformasi->item(0)->getElementsByTagName('a')->item(2)->nodeValue);

        return array(
            'posteDate'      => $posteDate,
            'description'    => $description,
            'levelPekerjaan' => $levelPekerjaan,
            'pendidikan'     => $pendidikan,
            'tipePekerjaan'  => $tipePekerjaan
        );
    }

    public function getAll($url, $headers = "") {

        $result = curlGet2($url, $headers);
        $dom    = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($result);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        
        // get tag h2
        // $h2 = $dom->getElementsByTagName('h2');
        $h2        = $xpath->query('//h2[contains(@class, "media-heading")]');
        $tables    = $xpath->query('//table');
        $namaPT    = $xpath->query('//div[contains(@class, "media m-b-25")]');
        $dataLoker = array();
        if($h2->length > 0) {
            $no = 1;
            for($i = 0; $i < $h2->length; $i++) {
                $tablesItems = $tables->item($i);
                $gaji        = $tablesItems->getElementsByTagName('tr')->item(0)->getElementsByTagName('td')->item(1)->nodeValue;
                $lokasi      = $tablesItems->getElementsByTagName('tr')->item(1)->getElementsByTagName('td')->item(1)->nodeValue;
                $h2Loop      = $h2->item($i);
                $anchor      = $h2Loop->getElementsByTagName('a');
                if($anchor->length > 0) {
                    $anchorsItems   = $anchor->item(0);
                    $href           = trim($anchorsItems->getAttribute('href'));
                    $titleWork      = $h2Loop->nodeValue;
                    $namaPT         = $h2Loop->nextSibling->textContent;
                    $getDetailLoker = LokeridRequest::getDetailLoker($href);

                    $dataLoker[] = array(
                        'pt'                => trim($namaPT),
                        'title'             => trim($titleWork),
                        'lokasi'            => trim($lokasi),
                        'gaji'              => trim($gaji),
                        'level'             => $getDetailLoker['levelPekerjaan'],
                        'pendidikan'        => $getDetailLoker['pendidikan'],
                        'tipe_pekerjaan'    => $getDetailLoker['tipePekerjaan'],
                        'description'       => $getDetailLoker['description'],
                        'link'              => $href,
                        'post_date'         => $getDetailLoker['posteDate'],
                    );

                    echo $no . ". " . $titleWork . " - " . $href . "\n";
                }

                $no++;
            }
        }else{
            echo "Tidak ada data loker .\n";
        }

        return $dataLoker;

        
    }
}