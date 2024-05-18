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
        
        $panelInformasi     = $xpathDetailLoker->query('//div[contains(@class, "panel-body padding-horizontal-double padding-vertical-double")]');
        $panelInformasi2    = $xpathDetailLoker->query('//div[contains(@class, "panel-heading padding-horizontal-double padding-vertical-double")]'); 
        $detailPerusahaan   = $xpathDetailLoker->query('//div[contains(@id, "job-company-details")]');
        
        $posteDate      = trim($panelInformasi2->item(0)->nodeValue);
        $description    = trim($panelInformasi->item(1)->nodeValue);
        $levelPekerjaan = trim($panelInformasi->item(0)->getElementsByTagName('a')->item(0)->nodeValue);
        $pendidikan     = trim($panelInformasi->item(0)->getElementsByTagName('a')->item(1)->nodeValue);
        $tipePekerjaan  = trim($panelInformasi->item(0)->getElementsByTagName('a')->item(2)->nodeValue);
        $companyLogo    = $detailPerusahaan->item(0)->getElementsByTagName('img')->item(1)->getAttribute('src');
        $descCompany    = $detailPerusahaan->item(0)->getElementsByTagName('p')->item(1)->nodeValue;
        $industriComp   = $xpathDetailLoker->query('//div[contains(@class, "m-b-5")]')->item(3)->nodeValue;
        $websiteComp    = $detailPerusahaan->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href');

        return array(
            'posteDate'      => $posteDate,
            'description'    => $description,
            'levelPekerjaan' => $levelPekerjaan,
            'pendidikan'     => $pendidikan,
            'tipePekerjaan'  => $tipePekerjaan,
            'companyLogo'    => $companyLogo,
            'descCompany'    => $descCompany,
            'industriComp'   => $industriComp,
            'websiteComp'    => $websiteComp
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
        $h2                  = $xpath->query('//h2[contains(@class, "media-heading")]');
        $tables              = $xpath->query('//table');
        $namaPT              = $xpath->query('//div[contains(@class, "media m-b-25")]');
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
                    print_r($getDetailLoker); die;

                    $data = array(
                        'JobTitle'          => trim($titleWork),
                        'location'          => trim($lokasi),
                        'sallary'           => trim($gaji),
                        'experience'        => $getDetailLoker['levelPekerjaan'],
                        'education'         => $getDetailLoker['pendidikan'],
                        'WorkType'          => $getDetailLoker['tipePekerjaan'],
                        'description'       => $getDetailLoker['description'],
                        'link'              => $href,
                        'post_date'         => $getDetailLoker['posteDate'],
                    );

                    $dataLoker[] = $data;

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