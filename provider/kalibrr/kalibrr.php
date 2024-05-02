<?php 
require_once __DIR__ . '/./request.php';

class Kalibrr {

    public function __construct()
    {
        $this->url         = KALIBRR_URL_API;
        $this->requestCurl = new KalibrrRequest();
        $this->limit       = 100;      
    }

    public function getPekerjaanByRekomendasi() 
    {

        $countLimit = curlGet($this->url . "kjs/job_board/featured_jobs?limit=2&offset=1&country=Indonesia")->count;
        $result     = $this->requestCurl->getAllRekomendasiWork($this->url . "kjs/job_board/featured_jobs?limit=" . $countLimit . "&offset=1&country=Indonesia");

        $dateNow       = date('Y-m-d');
        $uniq          = uniqid();
        $path          = realpath(__DIR__ . '/../../result_get/kalibrr');
        @mkdir($path . '/' . $dateNow , 0777, true);
        $filename      = $uniq . "-" . date('H:i:s') . "-kalibrr-job-rekomendasi.csv";
        $handle        = fopen($path . "/" . $dateNow . "/" . $filename, 'w+');
        $ttl_data      = count($result);
        fputcsv($handle, array_keys($result[0]));
        foreach ($result as $line) {
            fputcsv($handle, $line);
        }
        fclose($handle);

        echo "Successfully Get data : " . $filename . "\n";

    } 

    public function getPekerjaanAll()
    {
        $totalLimit             = curlGet($this->url . "https://www.kalibrr.com/kjs/job_board/search?limit=1&offset=1&country=Indonesia")->pageProps->count;
        $totalPagination        = ceil($totalLimit / $this->limit);
        
        echo "\n Total Limit      => " . $totalLimit;
        echo "\n Total Pagination => " . $totalPagination;
        // sleep(4);

        $resultArr = array();
        for($i = 1; $i <= $totalPagination; $i++) {
            $offset = ($i - 1) * $this->limit;
            echo "\n Offset => " . $offset . "\n";
            $result = $this->requestCurl->getAll($this->url . "kjs/job_board/search?limit=" . $this->limit . "&offset=" . $offset . "&country=Indonesia");
            $resultArr = array_merge($resultArr, $result);
        }

        $dateNow       = date('Y-m-d');
        $uniq          = uniqid();
        $path          = realpath(__DIR__ . '/../../result_get/kalibrr');
        @mkdir($path . '/' . $dateNow , 0777, true);
        $filename      = $uniq . "-" . date('H:i:s') . "-kalibrr-all-jobs.csv";
        $handle        = fopen($path . "/" . $dateNow . "/" . $filename, 'w+');
        $ttl_data      = count($resultArr);
        fputcsv($handle, array_keys($resultArr[0]));
        foreach ($resultArr as $line) {
            fputcsv($handle, $line);
        }
        fclose($handle);

        echo "Successfully Get data : " . $filename . "\n";
    } 
}

$main = new Kalibrr();
$main->getPekerjaanByRekomendasi();