<?php 

require __DIR__ . '/../../utils/constants.php';
require __DIR__ . '/../../utils/service_helpers.php';

class JobStreetRequest
{

   public function getDetail($url)
   {
     $result = curlGet2($url);
     $dom    = new DOMDocument();
     libxml_use_internal_errors(true);
     $dom->loadHTML($result);
     libxml_clear_errors();
     $xpath = new DOMXPath($dom);

     $listedData        = $xpath->query('//span[contains(@class, "listed-date")]')->item(0)->nodeValue;
     $jobDescription    = $xpath->query('//div[contains(@id, "job-description-container")]')->item(0)->nodeValue;
     $typePekerjaan      = $xpath->query('//div[contains(@id, "job-info-container")]')->item(0)->getElementsByTagName('div')->item(4);
     
     return array(
         'listedDate'       => $listedData,
         'jobDescription'   => $jobDescription,
         'typePekerjaan'    => trim(@$typePekerjaan->nodeValue)
     );

   }

   public function getAll($url)
    {
        $result = curlGet2($url);
        
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML($result);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        $h2            = $xpath->query("//h2[contains(@class, 'job-title')]");
        $locAnchor     = $xpath->query("//a[contains(@class, 'job-location')]");
        $spanJobComp   = $xpath->query("//span[contains(@class, 'job-company')]");
        $badgeSalary   = $xpath->query("//div[contains(@class, 'badge -default-badge')]");
        $dataArr       = array();
        if($h2->length > 0) {
            $no     = 1;
            for($i = 0; $i < $h2->length; $i++) {
                $h2Loop             = $h2->item($i);
                $anchor             = $h2Loop->getElementsByTagName('a');
                $anchorHref         = JOBSTRET_EXPRESS_URL . trim($anchor->item(0)->getAttribute('href'));
                $anchorText         = trim($anchor->item(0)->nodeValue);
                $location           = $locAnchor->item($i)->nodeValue;
                $nameCompany        = $spanJobComp->item($i)->nodeValue;
                $salary             = @$badgeSalary->item($i)->nodeValue;
                $detailJobs         = $this->getDetail($anchorHref);

                $data = array(
                    'nama_perusahaan'       => $nameCompany,
                    'judul_pekerjaan'       => $anchorText,
                    'lokasi_pekerjaan'      => $location,
                    'gaji_pekerjaan'        => $salary,
                    'type_pekerjaan'        => $detailJobs['typePekerjaan'],
                    'deskripsi_pekerjaan'   => $detailJobs['jobDescription'],
                    'tanggal_diposting'     => $detailJobs['listedDate'],
                    'link'                  => $anchorHref
                );
                echo $no . ". " . $data['nama_perusahaan'] . " - " . $data['judul_pekerjaan'] . ' - ' . $data['lokasi_pekerjaan'] .  ' - ' . $data['gaji_pekerjaan'] . "\n";
                array_push($dataArr, $data);
                $no++;
            }
        }
        
        return $dataArr;

    }
}