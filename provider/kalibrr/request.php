<?php

require __DIR__ . '/../../utils/constants.php';
require __DIR__ . '/../../utils/service_helpers.php';
class KalibrrRequest {
    
    public function __construct()
    {
        $this->education   = array(
            array(
                'shortName' => 'High School',
                'name' => 'Less than high school',
                'type' => 'secondary',
                'status' => 'dropout',
                'number' => 100
            ),
            array(
                'shortName' => 'High School',
                'name' => 'High school',
                'type' => 'secondary',
                'status' => 'current',
                'number' => 150
            ),
            array(
                'shortName' => 'High School',
                'name' => 'Graduated from high school',
                'type' => 'secondary',
                'status' => 'terminal',
                'number' => 200
            ),
            array(
                'shortName' => 'Vocational course',
                'name' => 'Vocational course',
                'type' => 'vocational',
                'status' => 'current',
                'number' => 300
            ),
            array(
                'shortName' => 'Vocational course',
                'name' => 'Completed vocational course',
                'type' => 'vocational',
                'status' => 'terminal',
                'number' => 350
            ),
            array(
                'shortName' => 'Associate\'s Degree',
                'name' => 'Associate\'s studies',
                'type' => 'associate',
                'status' => 'current',
                'number' => 400
            ),
            array(
                'shortName' => 'Associate\'s Degree',
                'name' => 'Completed associate\'s degree',
                'type' => 'associate',
                'status' => 'terminal',
                'number' => 450
            ),
            array(
                'shortName' => 'Bachelor\'s Degree',
                'name' => 'Bachelor\'s studies',
                'type' => 'college',
                'status' => 'current',
                'number' => 500
            ),
            array(
                'shortName' => 'Bachelor\'s Degree',
                'name' => 'Bachelor\'s degree graduate',
                'type' => 'college',
                'status' => 'terminal',
                'number' => 550
            ),
            array(
                'shortName' => 'Master\'s Degree',
                'name' => 'Graduate studies (Masters)',
                'type' => 'graduate',
                'status' => 'current',
                'number' => 600
            ),
            array(
                'shortName' => 'Master\'s Degree',
                'name' => 'Master\'s degree graduate',
                'type' => 'graduate',
                'status' => 'terminal',
                'number' => 650
            ),
            array(
                'shortName' => 'Doctorate',
                'name' => 'Post-graduate studies (Doctorate)',
                'type' => 'doctorate',
                'status' => 'current',
                'number' => 700
            ),
            array(
                'shortName' => 'Doctorate',
                'name' => 'Doctoral degree graduate',
                'type' => 'doctorate',
                'status' => 'terminal',
                'number' => 750
            )
        );   
        $this->level       =  array(
            array(
                'number' => 100,
                'name' => "Internship / OJT",
                'nameParts' => array("Internship", "OJT"),
                'icon' => "/assets/shared/img/icons/ic_internship.png"
            ),
            array(
                'number' => 200,
                'name' => "Entry Level / Junior, Apprentice",
                'nameParts' => array("Entry Level", "Junior, Apprentice"),
                'icon' => "/assets/shared/img/icons/ic_freshgrad.png"
            ),
            array(
                'number' => 300,
                'name' => "Associate / Supervisor",
                'nameParts' => array("Associate", "Supervisor"),
                'icon' => "/assets/shared/img/icons/ic_associate_supervisor.png"
            ),
            array(
                'number' => 400,
                'name' => "Mid-Senior Level / Manager",
                'nameParts' => array("Mid-Senior Level", "Manager"),
                'icon' => "/assets/shared/img/icons/ic_midseniorlevel.png"
            ),
            array(
                'number' => 500,
                'name' => "Director / Executive",
                'nameParts' => array("Director", "Executive"),
                'icon' => "/assets/shared/img/icons/ic_director.png"
            )
        );  
    }

    public function getAll($url)
    {
        $result   = curlGet($url);
        $resultArr = array();
        $no        = 1;
        foreach($result->jobs as $val) {
            $id            = $val->id;
            $code          = $val->company->code;

            echo KALIBRR_URL_API . "_next/data/kKfqNsDupBi-nR7EpRurt/c/$code/jobs/" . $id . "/$code.json?code=$code&param=" . $id;
            die;

            $detailWork    = curlGet(KALIBRR_URL_API . "_next/data/kKfqNsDupBi-nR7EpRurt/c/$code/jobs/" . $id . "/$code.json?code=$code&param=" . $id);
            print_r($detailWork);
            die;
            if(!$detailWork){
                continue;
            }

            $educationName = "";
            foreach($this->education as $key => $edu) {
                if($edu['number'] == @$detailWork->educationLevel) $educationName .= $edu['shortName'];
            }
            $levelName = "";
            foreach($this->level as $key => $edu) {
                if($edu['number'] == @$detailWork->workExperience) $levelName .= $edu['name'];
            }
            $data = [
                'nama_perusahaan'     => $val->company_name,
                'industri_perusahaan' => $detailWork->companyInfo->industry,
                'judul_pekerjaan'     => $val->name,
                'lokasi_pekerjaan'    => $detailWork->googleLocation->rawString,
                'tipe_pekerjaan'      => $val->tenure,
                'gaji_pekerjaan'      => @$detailWork->baseSalary,
                'pendidikan'          => @$educationName ?? "",
                'level_pekerjaan'     => @$levelName ?? "",
                'description'         => trim(@$detailWork->description),
                'kualifikasi'         => trim(@$detailWork->qualifications),
                'posted_job'          => @$detailWork->activationDate,
                'end_job'             => @$detailWork->applicationEndDate,
                'link'                => KALIBRR_URL_API . "c/$code/jobs/$id/" . @$detailWork->slug
            ];
            array_push($resultArr, $data);
            // print_r($detailWork);
            echo $no . ". " . $data['title'] . " | " . $data['nama_perusahaan'] . ' | ' . $data['gaji']  . "\n";
            // sleep(1);
            $no++;
        }

        return $resultArr;
    }

    public function getAllRekomendasiWork($url)
    {
        $result   = curlGet($url);
        $resultArr = array();
        $no        = 1;
        foreach($result->jobs as $val) {
            $id            = $val->id;
            $code          = $val->company_info->code;
            $detailWork    = curlGet(KALIBRR_URL_API . "_next/data/ncok1ewk9uSH_ONhJQ_pf/id-ID/c/$code/jobs/" . $id . "/$code.json?code=$code&param=" . $id)->pageProps->job ?? null;
            if(!$detailWork){
                continue;
            }

            $educationName = "";
            foreach($this->education as $key => $edu) {
                if($edu['number'] == @$detailWork->educationLevel) $educationName .= $edu['shortName'];
            }
            $levelName = "";
            foreach($this->level as $key => $edu) {
                if($edu['number'] == @$detailWork->workExperience) $levelName .= $edu['name'];
            }
            $data = [
                'nama_perusahaan'    => $val->company_info->company_name,
                'industri_perushaan' => $detailWork->companyInfo->industry,
                'title'           => $val->name,
                'lokasi'          => $detailWork->googleLocation->rawString,
                'tipe_pekerjaan'  => $val->tenure,
                'gaji'            => @$detailWork->baseSalary,
                'pendidikan'      => @$educationName ?? "",
                'level'           => @$levelName ?? "",
                'description'     => trim(@$detailWork->description),
                'kualifikasi'     => trim(@$detailWork->qualifications),
                'posted_job'      => @$detailWork->activationDate,
                'end_job'         => @$detailWork->applicationEndDate,
                'link'            => KALIBRR_URL_API . "c/$code/jobs/$id/" . @$detailWork->slug
            ];
            array_push($resultArr, $data);
            // print_r($detailWork);
            echo $no . ". " . $data['title'] . " | " . $data['nama_perusahaan'] . ' | ' . $data['gaji']  . "\n";
            // sleep(1);
            $no++;
        }

        return $resultArr;
    } 
}