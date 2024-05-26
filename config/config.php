<?php
// defined('BASEPATH') OR exit('No direct script access allowed');

class Databases {
 
  var $host     = "localhost";
  var $username = "iqbal";
  var $password = "iqbal";
  var $database = "job_scrape";

 
  function __construct($database = "job_scrape") {
    $this->db = new mysqli($this->host, $this->username, $this->password, $database);
    if (mysqli_connect_errno()){
      echo "Koneksi database gagal : " . mysqli_connect_error();
    }
  }

  public function begin_transaction()
  {
    return $this->db->begin_transaction();
  }

  public function commit()
  {
    return $this->db->commit();
  }

  public function rollback()
  {
    return $this->db->rollback();
  }

  public function site_url()
  {
    $config['base_url'] = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
    $config['base_url'] .= "://".$_SERVER['HTTP_HOST'];
    $config['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);

    print_r($config['base_url']);
  }

  public function generate_uuid()
  {
    return sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
  }
  
  
  public function queryLogin($query){
    $data = $this->query = $this->db->query($query);
        return $data;
  }
  
  public function query($query){
    $this->query = $this->db->query($query);
    return $this;
  }

  public function getEachTable($table,$mandatory,$key){
    $this->query = $this->db->query("SELECT * FROM $table WHERE $mandatory = '$key' ");
    return $this;
  }

  public function getEachTableKeuangan($key)
  {
    $this->query = $this->db->query("SELECT * FROM keuangan WHERE uuid_nasabah = '$key' ");
    return $this;
  }

  public function getLaporanAbsenToday($table, $mandatory, $key, $date)
  {
    $this->query = $this->db->query("SELECT nama_user,SUM(masuk) as ttl_masuk FROM $table WHERE $mandatory = '$key' AND tanggal = '$date' ");
    return $this;
  }

  public function getSumKeunagan($key)
  {
    $this->query = $this->db->query("SELECT SUM(penerimaan) as Jumlah FROM keuangan WHERE uuid_nasabah = '$key' AND is_delete = 0 ");
    return $this;
  }

  public function totalPenerimaan(){
    $this->query = $this->db->query("SELECT SUM(penerimaan) as ttl_penerimaan FROM keuangan WHERE is_delete = 0");
     return $this;
  }

  public function totalPenerimaanByUsernameDateNow($key, $tanggal)
  {
    $this->query = $this->db->query("SELECT sum(penerimaan) AS ttl_penerimaan FROM `keuangan` WHERE is_delete = 0 AND fk_id_users = '$key' AND tanggal_penerimaan = '$tanggal' ");
    return $this;
  }

  public function totalPenerimaanByidPeriode($key,$start_date,$end_date){
    $this->query = $this->db->query("SELECT sum(penerimaan) AS ttl_penerimaan FROM `keuangan` WHERE fk_id_users = '$key' AND tanggal_penerimaan BETWEEN '$start_date' AND '$end_date' AND is_delete = 0 ORDER BY tanggal_penerimaan DESC ");
    return $this;
  }

  public function totalPenerimaanClientDateNow($key, $tanggal)
  {
    $this->query = $this->db->query("SELECT sum(penerimaan) AS ttl_penerimaan FROM `keuangan` WHERE is_delete = 0 AND id_client = '$key' AND tanggal_penerimaan = '$tanggal' ");
    return $this;
  }

  public function totalPenerimaanClientPeriode($key,$start_date,$end_date){
    $this->query = $this->db->query("SELECT sum(penerimaan) AS ttl_penerimaan FROM `keuangan` WHERE id_client = '$key' AND tanggal_penerimaan BETWEEN '$start_date' AND '$end_date' AND is_delete = 0 ORDER BY tanggal_penerimaan DESC ");
    return $this;
  }

  public function UpdateListPaidManual($data, $session)
  {
    //  print_r($data['amount_collect']); die; 
    for ($i = 0; $i < count($data['amount_collect']); $i++) {
      $data_ammount      = $data['amount_collect'][$i] != "" ? $data['amount_collect'][$i] : "";
      $nama_aplikasi     = $data['nama_aplikasi'][$i] != "" ? $data['nama_aplikasi'][$i] : "";
      $fee_pembayaran    = $data['fee_pembayaran'][$i] != "" ? $data['fee_pembayaran'][$i] : "";
      $pph23              = $data['pph23'][$i] != "" ? $data['pph23'][$i] : "";
      $after_pph23        = $data['afterpph23'][$i] != "" ? $data['afterpph23'][$i] : "";
      $kat                = $data['kat'][$i] != "" ? $data['kat'][$i] : "";
      $indocoll          = $data['indocoll'][$i] != "" ? $data['indocoll'][$i] : "";

      // Amount Collect
      if ($data['amount_collect'][$i] != "") {

        $amount_collect_update  = array('amout_collect' => preg_replace('/\D/', '', $data_ammount), 'id_users' => $session['id_user']);
        $where_amount = array('id_client' => $nama_aplikasi);

        $result = $this->update('list_paid', $amount_collect_update, $where_amount);
      } else {
        $amount_collect_insert  = array('id_listpaid' => $this->generate_uuid(), 'id_client' => $nama_aplikasi, 'amout_collect' => preg_replace('/\D/', '', $data_ammount), 'id_users' => $session['id_user']);
        $result = $this->insert('list_paid', $amount_collect_insert);
      }

      // List Paid
      if ($data['fee_pembayaran'][$i]) {
        $amount_collect_update  = array('fee' => preg_replace('/\D/', '', $fee_pembayaran), 'id_users' => $session['id_user']);
        $where_amount = array('id_client' => $nama_aplikasi);

        $result = $this->update('list_paid', $amount_collect_update, $where_amount);
      }

      // List Paid
      if ($data['pph23'][$i]) {
        $amount_collect_update  = array('pph23' => preg_replace('/\D/', '', $pph23), 'id_users' => $session['id_user']);
        $where_amount = array('id_client' => $nama_aplikasi);

        $result = $this->update('list_paid', $amount_collect_update, $where_amount);
      }

      // After List Paid
      if ($data['afterpph23'][$i]) {
        $amount_collect_update  = array('after_pph23' => preg_replace('/\D/', '', $after_pph23), 'id_users' => $session['id_user']);
        $where_amount = array('id_client' => $nama_aplikasi);

        $result = $this->update('list_paid', $amount_collect_update, $where_amount);
      }

      // KAT List Paid
      if ($data['kat'][$i]) {
        $amount_collect_update  = array('kat' => preg_replace('/\D/', '', $kat), 'id_users' => $session['id_user']);
        $where_amount = array('id_client' => $nama_aplikasi);

        $result = $this->update('list_paid', $amount_collect_update, $where_amount);
      }

      // Indocoll List Paid
      if ($data['indocoll'][$i]) {
        $amount_collect_update  = array('indocoll' => preg_replace('/\D/', '', $indocoll), 'id_users' => $session['id_user']);
        $where_amount = array('id_client' => $nama_aplikasi);

        $result = $this->update('list_paid', $amount_collect_update, $where_amount);
      }
    }
  }

  public function UpdateListPaidManual2($data, $session)
  {

    for ($i = 0; $i < count($data['amount_collect']); $i++) {
      $amount_collect     = $data['amount_collect'][$i] != "" ? $data['amount_collect'][$i] : "";
      $nama_aplikasi      = $data['nama_aplikasi'][$i] != "" ? $data['nama_aplikasi'][$i] : "";
      $dpd                = $data['dpd'][$i] != "" ? $data['dpd'][$i] : "";

      if ($data['amount_collect'][$i] != "") {

        $amount_collect_update  = array('amout_collect' => $amount_collect, 'id_users' => $session['id_user']);
        $where_amount = array('dpd' => $dpd);
        print_r($where_amount);
      } else {
        $amount_collect_insert  = array('id_listpaid' => $this->generate_uuid(), 'id_client' => $nama_aplikasi, 'amout_collect' => $amount_collect, 'id_users' => $session['id_user']);
        print_r($amount_collect_insert);
      }
    }
  }

  public function filterPendapatanPenerimaan($client, $user, $start_date, $end_date, $is_delete)
  {
    $sql = "SELECT SUM(penerimaan) as ttl_penerimaan FROM keuangan WHERE ";
    if($client != "SEMUA") $sql .= "id_client='$client' AND ";
    if ($user != "SEMUA") $sql .= "fk_id_users='$user' AND ";

    $sql .= "tanggal_penerimaan BETWEEN '$start_date' AND '$end_date' AND is_delete = $is_delete ORDER BY tanggal_penerimaan DESC";
    // echo $sql;die;
    $this->query = $this->db->query($sql);
    return $this;
  }

  public function filterLaporanDataPengeluaran($start_date, $end_date)
  {
    $sql = "SELECT SUM(total) as ttl_pengeluaran FROM pengeluaran WHERE created_date BETWEEN '$start_date' AND 'end_date' ORDER BY created_date DESC";

    $this->query = $this->db->query($sql);
    return $this;
  }

  public function filterListLaporanDataPengeluaran($start_date, $end_date)
  {
    $sql = "SELECT * FROM pengeluaran WHERE created_date BETWEEN '$start_date' AND 'end_date' ORDER BY created_date DESC";

    $this->query = $this->db->query($sql);
    return $this;
  }

  public function filterDataPendapatanPenerimaan($client, $user, $start_date, $end_date, $is_delete)
  {
    $sql = "SELECT * FROM keuangan WHERE ";
    if($client != "SEMUA") $sql .= "id_client='$client' AND ";
    if ($user != "SEMUA") $sql .= "fk_id_users='$user' AND ";

    $sql .= "tanggal_penerimaan BETWEEN '$start_date' AND '$end_date' AND is_delete = $is_delete ORDER BY tanggal_penerimaan DESC";
    // echo $sql;die;
    $this->query = $this->db->query($sql);
    return $this;
  }

  public function filterClientReportPayment($client)
  {
    $sql = "SELECT * FROM client WHERE";
    if ($client != "SEMUA") $sql .= " id_client='$client'";
    $this->query = $this->db->query($sql);
    return $this;
  }

  public function searchTable($table,$where,$keyword)
  {
    $this->query = $this->db->query("SELECT * FROM $table WHERE $where LIKE '%$keyword%' ");
  }

  public function getTable($table,$key){
    $this->query = $this->db->query("SELECT * FROM $table ORDER BY $key DESC");
    return $this;
  }

  public function getWhere($table,$condition,$order,$condition2){
    $this->where = $this->db->query("SELECT * FROM $table WHERE $condition ORDER BY $order $condition2");
    return $this;
  }

  public function searchValidasiData($id, $ttl_real_payment)
  {
    $sql_validasi = $this->db->query("SELECT * FROM validasi_data");

    foreach ($sql_validasi as $key) {
      if ($key->uid === $id && $key->ttl_real_payment === $ttl_real_payment) {
        return "valid";
      } else {
        return "tidak Valid";
      }
    }
  }

  public function rowObject(){

    while($data = $this->query->fetch_object()){
      $result[] = $data;
    }
    return empty($result) ? null : $result;
  }

  public function rowArray(){


    while($data = $this->query->fetch_assoc()){
      $result[] = $data;
    }
    return empty($result) ? null : $result;
  }

  public function orderBy($condition1,$condition2){
    

  }
  
  public function insert($table,$data){
      $fields = "(";
      $values = "(";
      $index  = 0;
      
      foreach ($data as $key => $val) {
        $fieldname = ($index < count($data)-1) ? $key.", " : $key. ")";
        $valuedata = ($index < count($data)-1) ? "'".$val."', "  : "'".$val."')";

        $fields .= $fieldname;
        $values .= $valuedata;

        $index++;
      }

    $query = $this->db->query("INSERT INTO ".$table." ".$fields." VALUES ".$values." ");
    return $query;
  }

  public function update($table_name, $fields, $where)
  {
    $query = '';
    $condition = '';
    foreach ($fields as $key => $value) {
      $query .= $key . "='" . $value . "', ";
    }
    $query = substr($query, 0, -2);
    foreach ($where as $key => $value) {
      $condition .= $key . "='" . $value . "' AND ";
    }
    $condition = substr($condition, 0, -5);

    $query = $this->db->query("UPDATE " . $table_name . " SET " . $query . " WHERE " . $condition . "");
    try {
      return $query;
    } catch (\Throwable $th) {
      var_dump($th->getMessage());
      die;
    }
  } 
  

  public function rowCount(){
    $data = $this->query->num_rows;
    return $data;
  }

  public function result(){
    return empty($this->query->fetch_assoc()) ? null : $this->query->fetch_assoc();
  }

  // public function generate_uuid() {
  //   return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
  //     mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
  //     mt_rand( 0, 0xffff ),
  //     mt_rand( 0, 0x0fff ) | 0x4000,
  //     mt_rand( 0, 0x3fff ) | 0x8000,
  //     mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
  //   );
  // }

  public function validate_data($text, $html = true)
  {
    $e_s = array('\\', '\'', '"');
    $d_s = array('', '', '');
    $text = preg_replace("'<script[^>]*>.*?</script>'si", '', $text);
    $text = preg_replace('/<a\s+.*?href="([^"]+)"[^>]*>([^<]+)<\/a>/is', '\2 (\1)', $text);
    $text = preg_replace('/<!--.+?-->/', '', $text);
    $text = preg_replace('/{.+?}/', '', $text);
    $text = preg_replace('/&nbsp;/', ' ', $text);
    $text = preg_replace('/&amp;/', '', $text);
    $text = str_replace($e_s, $d_s, $text);
    $text = strip_tags($text);
    $text = preg_replace("/\r\n\r\n\r\n+/", " ", $text);
    $text = $html ? htmlspecialchars($text) : $text;
    return $text;
  }

  public function hariIndo($hariInggris)
  {
    switch ($hariInggris) {
      case 'Sunday':
        return 'Minggu';
      case 'Monday':
        return 'Senin';
      case 'Tuesday':
        return 'Selasa';
      case 'Wednesday':
        return 'Rabu';
      case 'Thursday':
        return 'Kamis';
      case 'Friday':
        return 'Jumat';
      case 'Saturday':
        return 'Sabtu';
      default:
        return 'hari tidak valid';
    }
  }

  public function tgl_indo($tanggal)
  {
    $bulan = array(
      1 => 'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
  }

  public function send_sms($no_tlp, $message)
  {
    $url = "http://sms.mysmsmasking.com/masking/send_post.php";

    $rows = array(
      'username' => 'indocoll',
      'password' => 'kontol2712',
      'hp'       => '62' . $no_tlp,
      'message'  => $message
    );

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($rows));
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    $htm = curl_exec($curl);
    if (curl_errno($curl) !== 0) {
      error_log('cURL error when connecting to ' . $url . ': ' . curl_error($curl));
    }
    $response = curl_close($curl);
    return $response;
  }

  // function  format bytes
  function formatBytes($size, $decimals = 0){
    $unit = array(
    '0' => 'Byte',
    '1' => 'KiB',
    '2' => 'MiB',
    '3' => 'GiB',
    '4' => 'TiB',
    '5' => 'PiB',
    '6' => 'EiB',
    '7' => 'ZiB',
    '8' => 'YiB'
    );
    
    for($i = 0; $size >= 1024 && $i <= count($unit); $i++){
      $size = $size/1024;
    }
  
    return round($size, $decimals).' '.$unit[$i];
  }
  
  
  // function  format bytes2
  function formatBytes2($size, $decimals = 0){
    $unit = array(
    '0' => 'Byte',
    '1' => 'KB',
    '2' => 'MB',
    '3' => 'GB',
    '4' => 'TB',
    '5' => 'PB',
    '6' => 'EB',
    '7' => 'ZB',
    '8' => 'YB'
    );
    
    for($i = 0; $size >= 1000 && $i <= count($unit); $i++){
      $size = $size/1000;
    }
    
    return round($size, $decimals).''.$unit[$i];
  }
  
  public function notif_tele($kalimat)
  {
    $token = "851808695:AAGZ4jgs1IMFjOdNakge2-LlzvDncr3oDSc";
    $apiURL = "https://api.telegram.org/bot$token";
    $chatID = "575780205";
    file_get_contents($apiURL . "/sendmessage?chat_id=" . $chatID . "&text=" . $kalimat);
  }
  
} 

?>
