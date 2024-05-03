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


    public function getDetailJobstreetCoId($id)
    {
        $payload = '{"operationName":"jobDetails","variables":{"jobId":"'.$id.'","jobDetailsViewedCorrelationId":"fccc1861-4a3d-4934-b26a-257795f4d1d0","sessionId":"b566628c-bb32-481c-a9cc-5e8aadfc41ac","zone":"asia-4","locale":"id-ID","languageCode":"id","countryCode":"ID","timezone":"Asia/Jakarta"},"query":"query jobDetails($jobId: ID!, $jobDetailsViewedCorrelationId: String!, $sessionId: String!, $zone: Zone!, $locale: Locale!, $languageCode: LanguageCodeIso!, $countryCode: CountryCodeIso2!, $timezone: Timezone!) {\n  jobDetails(\n    id: $jobId\n    tracking: {channel: \"WEB\", jobDetailsViewedCorrelationId: $jobDetailsViewedCorrelationId, sessionId: $sessionId}\n  ) {\n    job {\n      tracking {\n        adProductType\n        classificationInfo {\n          classificationId\n          classification\n          subClassificationId\n          subClassification\n          __typename\n        }\n        hasRoleRequirements\n        isPrivateAdvertiser\n        locationInfo {\n          area\n          location\n          locationIds\n          __typename\n        }\n        workTypeIds\n        postedTime\n        __typename\n      }\n      id\n      title\n      phoneNumber\n      isExpired\n      isLinkOut\n      contactMatches {\n        type\n        value\n        __typename\n      }\n      isVerified\n      abstract\n      content(platform: WEB)\n      status\n      listedAt {\n        label(context: JOB_POSTED, length: SHORT, timezone: $timezone, locale: $locale)\n        __typename\n      }\n      salary {\n        currencyLabel(zone: $zone)\n        label\n        __typename\n      }\n      shareLink(platform: WEB, zone: $zone, locale: $locale)\n      workTypes {\n        label(locale: $locale)\n        __typename\n      }\n      advertiser {\n        id\n        name(locale: $locale)\n        __typename\n      }\n      location {\n        label(locale: $locale, type: LONG)\n        __typename\n      }\n      classifications {\n        label(languageCode: $languageCode)\n        __typename\n      }\n      products {\n        branding {\n          id\n          cover {\n            url\n            __typename\n          }\n          thumbnailCover: cover(isThumbnail: true) {\n            url\n            __typename\n          }\n          logo {\n            url\n            __typename\n          }\n          __typename\n        }\n        bullets\n        questionnaire {\n          questions\n          __typename\n        }\n        video {\n          url\n          position\n          __typename\n        }\n        __typename\n      }\n      __typename\n    }\n    companyProfile(zone: $zone) {\n      id\n      name\n      companyNameSlug\n      shouldDisplayReviews\n      branding {\n        logo\n        __typename\n      }\n      overview {\n        description {\n          paragraphs\n          __typename\n        }\n        industry\n        size {\n          description\n          __typename\n        }\n        __typename\n      }\n      reviewsSummary {\n        overallRating {\n          numberOfReviews {\n            value\n            __typename\n          }\n          value\n          __typename\n        }\n        __typename\n      }\n      perksAndBenefits {\n        title\n        __typename\n      }\n      __typename\n    }\n    companyReviews(zone: $zone) {\n      id\n      name\n      fullName\n      rating\n      reviewCount\n      reviewsUrl\n      __typename\n    }\n    companySearchUrl(zone: $zone, languageCode: $languageCode)\n    learningInsights(platform: WEB, zone: $zone, locale: $locale) {\n      analytics\n      content\n      __typename\n    }\n    companyTags {\n      key(languageCode: $languageCode)\n      value\n      __typename\n    }\n    restrictedApplication(countryCode: $countryCode) {\n      label(locale: $locale)\n      __typename\n    }\n    sourcr {\n      image\n      imageMobile\n      link\n      __typename\n    }\n    __typename\n  }\n}\n"}';
        // print_r($payload); die;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.jobstreet.co.id/graphql');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, "$'{\"operationName\":\"jobDetails\",\"variables\":{\"jobId\":\"75548060\",\"jobDetailsViewedCorrelationId\":\"fccc1861-4a3d-4934-b26a-257795f4d1d0\",\"sessionId\":\"b566628c-bb32-481c-a9cc-5e8aadfc41ac\",\"zone\":\"asia-4\",\"locale\":\"id-ID\",\"languageCode\":\"id\",\"countryCode\":\"ID\",\"timezone\":\"Asia/Jakarta\"},\"query\":\"query");
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Authority: www.jobstreet.co.id';
        $headers[] = 'Accept: */*';
        $headers[] = 'Accept-Language: en-US,en;q=0.9,id;q=0.8';
        $headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Content-Type: application/json';
        // $headers[] = 'Cookie: _ENV["sol_id=8f618d6b-62ea-4bf2-b4ae-17d0d9abeca1; JobseekerSessionId=b566628c-bb32-481c-a9cc-5e8aadfc41ac; JobseekerVisitorId=b566628c-bb32-481c-a9cc-5e8aadfc41ac; _gcl_au=1.1.951174297.1714543638; ajs_anonymous_id=15dddbaf4c7d2498b4266cd291b2827c; _hjHasCachedUserAttributes=true; da_searchTerm=undefined; _hjSessionUser_640499=eyJpZCI6IjJiNmJmNzg2LWNlNTctNTY1MS05ZDIwLWU1OGI1MjRjYzlhOSIsImNyZWF0ZWQiOjE3MTQ1NDM2MzgwMDQsImV4aXN0aW5nIjp0cnVlfQ==; da_cdt=visid_018f32c39eec006e45ac5b96055405065001c05d0086e-sesid_1714695629678; da_sa_candi_sid=1714695629678; _hjSession_640499=eyJpZCI6IjQxNDlhZDRjLTdjYzUtNDIzYy05ODlkLWJjNDc0YjBiNjkyNiIsImMiOjE3MTQ2OTU2NzAwNjQsInMiOjAsInIiOjAsInNiIjowLCJzciI6MCwic2UiOjAsImZzIjowLCJzcCI6MH0=; main=V%7C2~P%7Cjobsearch~I%7C6281~OSF%7Cquick&set=1714696131233/V%7C2~P%7Cjobsearch~I%7C6281%2C6251~OSF%7Cquick&set=1714696110721/V%7C2~P%7Cjobsearch~OSF%7Cquick&set=1714544280320; __cf_bm=cse9JbUy3_EmOc9i_vEUTXAMOrWUKKr3g91LiUW7mek-1714696756-1.0.1.1-34ONzW1NfhYypfA4PLJji281L5q68_60LsLTDRpwILJXPqrCHJPEQo7EbWBf8ea3LkMwI5Duvz9nTLPhwVchKA; utag_main=v_id:018f32c39eec006e45ac5b96055405065001c05d0086e_sn:2_se:5%3Bexp-session_ss:0%3Bexp-session_st:1714698555391%3Bexp-sessionses_id:1714695629678%3Bexp-session_pn:1%3Bexp-sessiondc_visit:1dc_event:15%3Bexp-sessiondc_region:ap-east-1%3Bexp-session_prevpage:search%20results%3Bexp-1714700355432; _dd_s=rum=0&expire=1714697695364"];
        $headers[] = 'Origin: https://www.jobstreet.co.id';
        $headers[] = 'Pragma: no-cache';
        $headers[] = 'Referer: https://www.jobstreet.co.id/id/jobs-in-information-communication-technology?jobId='.$id.'&type=standout';
        $headers[] = 'Sec-Ch-Ua: \" Not A;Brand\";v=\"99\", \"Chromium\";v=\"102\", \"Google Chrome\";v=\"102\"';
        $headers[] = 'Sec-Ch-Ua-Mobile: ?0';
        $headers[] = 'Sec-Ch-Ua-Platform: \"Linux\"';
        $headers[] = 'Sec-Fetch-Dest: empty';
        $headers[] = 'Sec-Fetch-Mode: cors';
        $headers[] = 'Sec-Fetch-Site: same-origin';
        $headers[] = 'Seek-Request-Brand: jobstreet';
        $headers[] = 'Seek-Request-Country: ID';
        $headers[] = 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36';
        $headers[] = 'X-Seek-Ec-Sessionid: b566628c-bb32-481c-a9cc-5e8aadfc41ac';
        $headers[] = 'X-Seek-Ec-Visitorid: b566628c-bb32-481c-a9cc-5e8aadfc41ac';
        $headers[] = 'X-Seek-Site: chalice';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $rest = json_decode(curl_exec($ch));
        curl_close($ch);
        return $rest;
    }

   public function getJobstreetCoId($url)
   {
      $result = curlGet($url);
      
      $no           = 1;
      $dataArr      = array(); 
      foreach($result->data as $val) {
        $id             = $val->id;
        $getDetail      = $this->getDetailJobstreetCoId($id)->data->jobDetails;
        $companyName    = @$val->companyName ?? $val->advertiser->description;
        $companyIndustri= @$getDetail->companyProfile->overview->industry ?? @$val->classification->description;
        $location       = $val->locationWhereValue;
        $listingDateDis = $val->listingDateDisplay;
        $listingDate    = $val->listingDate;
        $jobtitle       = $getDetail->job->title;
        $worktype       = $val->workType;
        $deskripsi      = $getDetail->job->content;
        $salary         = $val->salary;
        $status         = $getDetail->job->status;
        $link           = $getDetail->job->shareLink;
        $questionsJob   = @json_encode($getDetail->job->questionnaire->questions) ?? '[]';
        
        
        $data = array(
            'no'             => $no,
            'companyName'    => $companyName,
            'companyIndustri'=> $companyIndustri,
            'location'       => $location,
            'jobtitle'       => $jobtitle,
            'education'      => "",
            'worktype'       => $worktype,
            'deskripsi'      => $deskripsi,
            'salary'         => $salary,
            'status'         => $status,
            'listingDateDis' => $listingDateDis,
            'listingDate'    => $listingDate,
            'link'           => $link
        );

        $dataArr[] = $data;

        echo $no++ . ". " . $companyName . " | " . $companyIndustri . " |  " . $jobtitle . " | " . $salary  ."\n";
      }

      return $dataArr;

   }
}