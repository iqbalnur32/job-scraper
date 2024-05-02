<?php 
require_once __DIR__ . '/./request.php';

class LokerID {

    public function __construct()
    {
        $this->url         = LOKERID_URL_API;
        $this->requestCurl = new LokeridRequest();
    }

    public function getAll($search = "", $lokasi = "0", $category = "0", $pendidikan = "0") {

        $headers = [
            "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "accept-language: en-US,en;q=0.9,id;q=0.8",
            "authority: www.loker.id",
            "cache-control: no-cache",
            "cookie: PHPSESSID=ctdab9b8l073jvn1h8gonh2u3v",
            "pragma: no-cache",
            "user-agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36"
        ];
        $totalLoopPages = 100;
        $dataAllPages   = array();
        for($i = 1; $i <= $totalLoopPages; $i++) {
            if($i == 1) {
                $result = $this->requestCurl->getAll(
                    $this->url  . "cari-lowongan-kerja?q=$search&lokasi=$lokasi&category=$category&pendidikan=$pendidikan", 
                    $headers
                );
                // $result = $this->url  . "cari-lowongan-kerja?q=$search&lokasi=$lokasi&category=$category&pendidikan=$pendidikan" . "\n";
            } else {
                $search2 = $search != "" ? "?q=$search" : "?q";
                $result = $this->requestCurl->getAll(
                    $this->url  . "cari-lowongan-kerja/page/". $i . $search2 . "&lokasi=$lokasi&category=$category&pendidikan=$pendidikan",
                    $headers
                );
                // $result = $this->url  . "cari-lowongan-kerja/page/". $i . $search2 . "&lokasi=$lokasi&category=$category&pendidikan=$pendidikan". "\n";
            }
            // print_r($result);            
            
            $dataAllPages = array_merge($dataAllPages, $result);
        }

        // die;
        // save to excel to folder result_get/lokerid

        $dateNow       = date('Y-m-d');
        $uniq          = uniqid();
        $path          = realpath(__DIR__ . '/../../result_get/lokerid');
        @mkdir($path . '/' . $dateNow , 0777, true);
        $filename      = $uniq . "-" . date('H:i:s') . "-lokerid.csv";
        $handle        = fopen($path . "/" . $dateNow . "/" . $filename, 'w+');
        $ttl_data      = count($dataAllPages);
        fputcsv($handle, array_keys($dataAllPages[0]));
        foreach ($dataAllPages as $line) {
            fputcsv($handle, $line);
        }
        fclose($handle);

        echo "Successfully Get data : " . $filename . "\n";
    }

}

$main = new LokerID();
$main->getAll("it");

?>