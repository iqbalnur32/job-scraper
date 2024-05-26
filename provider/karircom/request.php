<?php

require __DIR__ . '/../../utils/constants.php';
require __DIR__ . '/../../utils/service_helpers.php';
require __DIR__ . '/../../config/config.php';

class KarirRequest {

    public function __construct()
    {
        $this->db          = new Databases();   
    }


    public static function getDetail($url, $id) 
    {
        $payload      = array('opportunity_id' => $id, 'language' => 'id');
        $result       = curlPostJson($url, $payload);
        // print_r($result); die;
        if($result->code == 200) {
            $resultData = [
                'location'          => $result->data->location,
                'requirements'      => $result->data->requirements,
                'salary_info'       => $result->data->salary_lower . '-' . $result->data->salary_upper,
                'pendidikan'        => json_encode($result->data->degrees),
                'level'             => $result->data->job_levels[0],
                'job_type'          => $result->data->job_type,
                'link'              => $result->data->opportunities_link,
                'company'           => $result->data->company
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
                // print_r($getDetail); die;
                $resultData[] = array(
                    'JobTitle'          => $val->job_position,
                    'location'          => $getDetail['location'],
                    'sallary'           => $getDetail['salary_info'],
                    'experience'        => $getDetail['level'],
                    'education'         => $getDetail['pendidikan'],
                    'WorkType'          => $getDetail['job_type'],
                    'description'       => $getDetail['requirements'],
                    'link'              => $getDetail['link'],
                    'post_date'         => $val->posted_at,
                    'company_name'      => $getDetail['company']->name,
                    'company_desc'      => $getDetail['company']->description,
                    'company_web'       => NULL,
                    'company_industri'  => $getDetail['company']->industry_name
                ); 
                
                $insertJob = array(
                    'title'              => $val->job_position,
                    'location'           => $getDetail['location'],
                    'basicpay'           => $getDetail['salary_info'],
                    'experience'         => $getDetail['level'],
                    'education'          => $getDetail['pendidikan'],
                    'work_type'          => $getDetail['job_type'],
                    'jobdesc'            => $getDetail['requirements'],
                    'work_category'      => $getDetail['job_type'],
                    'industry'           => $getDetail['company']->industry_name,
                    'postdate'           => $val->posted_at,
                    'melamar_mudah'      => 0,
                    'platform'           => 'karir.com',
                    'link_lamar'         => $getDetail['link'],
                );
                
                $insertCompany = array(
                    'fk_id_users'       => 1,
                    'company_name'      => $getDetail['company']->name,
                    'profile'           => $getDetail['company']->description,
                    'website'           => null,
                    'industry'          => $getDetail['company']->industry_name,
                    'logo'              => $getDetail['company']->logo,
                );

                $this->db->insert('company', $insertCompany);
                $insertJob['id_company'] = $this->db->db->insert_id;
                $this->db->insert('jobs', $insertJob);
                
                echo $no . ". " . $val->job_position . "\n";
                $no++;

                die;
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