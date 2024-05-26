<?php 
require_once __DIR__ . '/./request.php';

class KarirCom {

    public function __construct()
    {
        $this->url         = KARIRCOM_URL_API;
        $this->requestCurl = new KarirRequest();
        $this->limit       = 20;
    }

    public function getAll($keywords = "") {
        
        $payload = [
            "keyword" => $keywords, 
            "location_ids" => [], 
            "company_ids" => [], 
            "industry_ids" => [], 
            "job_function_ids" => [], 
            "degree_ids" => [], 
            "locale" => "id",
            "limit"  => $this->limit, 
            "level" => "", 
            "min_employee" => 0, 
            "max_employee" => 50, 
            "is_opportunity" => true, 
            "sort_order" => "", 
            "is_recomendation" => false, 
            "is_preference" => false, 
            "is_choice_opportunity" => false, 
            "is_subscribe" => false, 
            "workplace" => null 
        ]; 
        $totalLimit         = $this->requestCurl->getCountPages($this->url . "v2/search/opportunities", $payload);
        $totalPagination    = ceil($totalLimit / $this->limit);
        $resultArrData      = array();

        echo "\n Total Limit      => " . $totalLimit;
        echo "\n Total Pagination => " . $totalPagination;
        sleep(4);

        for($i = 0; $i < $totalPagination; $i++) {
            $payload['offset'] = $i * $this->limit;
            $result            = $this->requestCurl->getAll($this->url . "v2/search/opportunities", $payload);
            print_r($result); die;
            $resultArrData     = array_merge($resultArrData, $result);
        }

        $dateNow       = date('Y-m-d');
        $uniq          = uniqid();
        $path          = realpath(__DIR__ . '/../../result_get/karircom');
        @mkdir($path . '/' . $dateNow , 0777, true);
        $filename      = $uniq . "-" . date('H:i:s') . "-karircom.csv";
        $handle        = fopen($path . "/" . $dateNow . "/" . $filename, 'w+');
        $ttl_data      = count($resultArrData);
        fputcsv($handle, array_keys($resultArrData[0]));
        foreach ($resultArrData as $line) {
            fputcsv($handle, $line);
        }
        fclose($handle);

        echo "Successfully Get data : " . $filename . "\n";
    }
}

$main = new KarirCom();
$main->getAll("");