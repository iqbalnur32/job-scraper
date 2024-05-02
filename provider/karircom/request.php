<?php

require __DIR__ . '/../../utils/constants.php';
require __DIR__ . '/../../utils/service_helpers.php';
class KarirRequest {

    public static function getDetail($url, $id) 
    {
        $payload      = array('opportunity_id' => $id, 'language' => 'id');
        $result       = curlPostJson($url, $payload);
        if($result->code == 200) {
            $resultData = [
                'location'          => $result->data->location,
                'requirements'      => $result->data->requirements,
                'salary_info'       => $result->data->salary_info,
                'pendidikan'        => json_encode($result->data->degrees),
                'level'             => $result->data->job_levels[0],
                'job_type'          => $result->data->job_type,
                'link'              => $result->data->opportunities_link
            ];
        }

        return $resultData;
    }

    public function getAll($url, $payload = array())
    {
        $result       = curlPostJson($url, $payload);
        if($result->code == 200) {
            $totalOpportunities = $result->data->total_opportunities;
            $opportunities      = $result->data->opportunities;
            $resultData         = array();
            $no = 1;
            foreach($opportunities as $key => $val) {
                $id                 = $val->id;
                $getDetail          = self::getDetail(KARIRCOM_URL_API . "v1/opportunity/detail", $id);

                $resultData[] = [
                    'pt'            => $val->company_name,
                    'title'         => $val->job_position,
                    'lokasi'        => $val->description,
                    'is_urgent'     => $val->is_urgent == false ? 'false' : 'true',
                    'gaji'          => $getDetail['salary_info'],
                    'pendidikan'    => $getDetail['pendidikan'],
                    'level'         => $getDetail['level'],
                    'link'          => $getDetail['link'],
                    'tipe_pekerjaan'=> $getDetail['job_type'],
                    'description'   => $getDetail['requirements'],
                    'post_date'     => $val->posted_at
                ];

                echo $no . ". " . $val->job_position . "\n";
                $no++;
            }  
        }

        return $resultData;

    }

    public function getCountPages($url, $payload = array())
    {
        $result       = curlPostJson($url, $payload);
        return $result->data->total_opportunities;

    }
}