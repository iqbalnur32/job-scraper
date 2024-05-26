<?php

require __DIR__ . '/../../utils/constants.php';
require __DIR__ . '/../../utils/service_helpers.php';
require __DIR__ . '/../../config/config.php';

class LokeridRequest {

    public function __construct()
    {
        $this->db          = new Databases();   
    }

    public static function getDetailLoker($url) {

        $months = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'April' => 'April',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'September' => 'September',
            'Oktober' => 'October',
            'November' => 'November',
            'Desember' => 'December',
        ];


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

        // Posted Date to timestamp
        $posteDate = str_replace("Posted Date: ", "", $posteDate);
        foreach ($months as $ind => $eng) {
            $posteDate = str_replace($ind, $eng, $posteDate);
        }
        $timestampPostDate = strtotime($posteDate);

        return array(
            'posteDate'      => date("Y-m-d H:i:s", $timestampPostDate),
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
                        'company_name'      => $namaPT,
                        'company_desc'      => $getDetailLoker['descCompany'],
                        'company_web'       => $getDetailLoker['websiteComp'],
                        'company_industri'  => $getDetailLoker['industriComp']
                    ); 
                    
                    $insertJob = array(
                        'title'              => trim($titleWork),
                        'location'           => trim($lokasi),
                        'basicpay'           => trim($gaji),
                        'experience'         => $getDetailLoker['levelPekerjaan'],
                        'education'          => $getDetailLoker['pendidikan'],
                        'work_type'          => $getDetailLoker['tipePekerjaan'],
                        'jobdesc'            => $getDetailLoker['description'],
                        'work_category'      => $getDetailLoker['industriComp'],
                        'industry'           => $getDetailLoker['industriComp'],
                        'postdate'           => $getDetailLoker['posteDate'],
                        'melamar_mudah'      => 0,
                        'link_lamar'         => $href,
                    );
                    $insertCompany = array(
                        'fk_id_users'       => 1,
                        'company_name'      => trim($namaPT),
                        'profile'           => $getDetailLoker['descCompany'],
                        'website'           => $getDetailLoker['websiteComp'],
                        'industry'          => $getDetailLoker['industriComp'],
                        'logo'              => $getDetailLoker['companyLogo'],
                    );

                    $this->db->insert('company', $insertCompany);
                    $insertJob['id_company'] = $this->db->db->insert_id;
                    $this->db->insert('jobs', $insertJob);

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