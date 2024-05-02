<?php 

require_once __DIR__ . '/./request.php';

class JobStreet
{
    public function __construct()
    {
        $this->url         = JOBSTRET_EXPRESS_URL;
        $this->requestCurl = new JobStreetRequest();
        $this->limit       = 20;
    }

    public function getPekerjaanAll()
    {
        

        $totalLoopPages = 100;
        $dataAllPages   = array();
        $totalAllPages  = ceil($totalAllPages / 15);
        for($i = 1; $i <= $totalLoopPages; $i++) {

            echo "\n Total Page => " . $totalAllPages . "\n";
            echo "\n Page => " . $i . "\n";

            $result = $this->requestCurl->getAll($this->url . "j?a=&jt=&l=&p=$i&q=&since=lv&sp=recent_homepage&surl=0&tk=zsNizCAVEh2aaDEmHGCq-YbB9f5Jx6TpZZsVF8v4u");
            $dataAllPages = array_merge($dataAllPages, $result);
        }

        $dateNow       = date('Y-m-d');
        $uniq          = uniqid();
        $path          = realpath(__DIR__ . '/../../result_get/joobstreet');
        @mkdir($path . '/' . $dateNow , 0777, true);
        $filename      = $uniq . "-" . date('H:i:s') . "-joobstreet-express.csv";
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
$main->getPekerjaanAll();