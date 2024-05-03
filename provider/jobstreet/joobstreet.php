<?php 

require_once __DIR__ . '/./request.php';

class JobStreet
{
    public function __construct()
    {
        $this->url         = JOBSTRET_COID_URL;
        $this->requestCurl = new JobStreetRequest();
        $this->limit       = 20;
    }

    public function getAll()
    {
        $pagesSize  = 30;
        $getTotal   = curlGet($this->url . "api/chalice-search/v4/search?siteKey=ID-Main&sourcesystem=houston&page=1&seekSelectAllPages=true&classification=6281&pageSize=$pagesSize&include=seodata&locale=id-ID");
        $totalCount = $getTotal->totalCount;
        $totalPages = $getTotal->totalPages;
        // $totalPages = ceil($totalCount / $pagesSize);
        // print_r($totalPages); die;
        $dataAllPages = array();
        for ($i = 1; $i <= $totalPages; $i++) {

            $result       = $this->requestCurl->getJobstreetCoId($this->url . "api/chalice-search/v4/search?siteKey=ID-Main&sourcesystem=houston&page=$i&seekSelectAllPages=true&classification=6281&pageSize=$pagesSize&include=seodata&locale=id-ID");
            $dataAllPages = array_merge($dataAllPages, $result);
        }

        $dateNow       = date('Y-m-d');
        $uniq          = uniqid();
        $path          = realpath(__DIR__ . '/../../result_get/joobstreet');
        @mkdir($path . '/' . $dateNow , 0777, true);
        $filename      = $uniq . "-" . date('H:i:s') . "-joobstreet.csv";
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

$main = new JobStreet();
$main->getAll();