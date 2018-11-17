<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class M_htmltoPDF extends CI_Model {
        
        public function __construct()
        {
            parent::__construct();
        }
        
        public function pdf_day($order_day = "",$date_time_end = "", $week = "", $month = "", $year = "",  $lil = "lil", $asia24 = "asia24", $outside = "outside")
        {
            if ($order_day == "" && $date_time_end == ""){
                $order_day  = date("Y-m-d");
            }
            // xu ly kho
            $kho = "";
            if($lil == "" && $asia24 == "" && $outside == ""){
                $kho = "";
            }elseif($lil != "" && $asia24 == "" && $outside == ""){
                $kho = $lil;
            }elseif($lil == "" && $asia24 != "" && $outside == ""){
                $kho = $asia24;
            }elseif($lil == "" && $asia24 == "" && $outside != ""){
                $kho = $outside;
            }else {
                // 1 la in tat, 2 la in tung kho le , khong cho in lil * asia 24
            }
            //end xu ly vi tri kho
            if($week != "" && $year != ""){
                $year_of_week = $year;
                
                $timestamp = mktime( 0, 0, 0, 1, 1,  $year_of_week ) + ( $week * 7 * 24 * 60 * 60 );
                $timestamp_for_end = $timestamp - 86400 * ( date( 'N', $timestamp ) );
                $end_week  = date( 'Y-m-d', $timestamp_for_end ); //string(10) "2018-11-18"
                
                $timestamp_for_start = $timestamp - 86400 * ( date( 'N', $timestamp ) + 6);
                $first_week = date( 'Y-m-d', $timestamp_for_start);
            }
            $this->db->select('line_items');
            $this->db->from('voxy_package_orders');

            if($week == "" && $month == ""){//tim theo ngay
                if( $date_time_end == ""){
                    $this->db->like('created_time', $order_day);
                }else {
                    $this->db->where('created_time <=' , $date_time_end);
                    $this->db->where('created_time >=' , $order_day);
                }
                
            }else {//tim theo thang va nam
                
                if($week != "" && $month == ""){
                    $this->db->where('created_time <=' , $end_week);
                    $this->db->where('created_time >=' , $first_week);
                }
                if($week == "" && $month != "" && $year != ""){ // phai chon month vs year
                    $this->db->like('created_time', $year.'-'.$month ); // du lieu month la 2018-11
                }
            }
            $query = $this->db->get();
                //var_dump($this->db->last_query());die;
            $data = $query->result_array();
            $_export = array();
            $i = 0;
            foreach ($data as $item){
                foreach (json_decode($item['line_items']) as $key2 => $item2 ){
                    $i++;
                    $_export[$i] = get_object_vars($item2);
                }
            }
            
            //ghep location like key to sort
            $export = array();
            $export2 = array();
            $chiso_remove = array();
            
            foreach($_export as $key => $item){
                // kiem tra co tat ca bao nhieu product trong list, rôi tang quantity len, go bo nhung thang giong nhau
                
                foreach ($_export as $key2 => $item2){
                    if($key2 > $key ){
                        if($item['title'] == $item2['title'] && $item['variant_title'] == $item2['variant_title'] && $item['name'] == $item2['name'] ){
                            $item['quantity'] = $item['quantity'] +  $item2['quantity'];
                            $chiso_remove[$key2-1] = $key2-1;
                        }
                    }
                }
                $export2[] = $item;
            }
            //remove nhung thang giong di
            foreach ($export2 as $key => $item){
                foreach ($chiso_remove as $key_reomove => $item_remove){
                    unset($export2[$item_remove]);
                    unset($chiso_remove[$key_reomove]);
                }
            }
            //gan location key
            foreach($export2 as $key3 => $item){
                
                if($item["location"] == false){
                    $item["location"] = $key3."_NULL";
                }
                $export[$item["location"]] = $item;
                
            }
            //ksort tag theo khoa, krsort giam theo khoa hehe :D
            ksort($export);
            $output = '<table width="100%" cellspacing="5" cellpadding="5" style="font-family: DejaVu Sans">
            <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Loại SP</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Tổng</th>
            <th>Vị trí</th>
            </tr>
            ';
            $id = 0;
            foreach($export as $row)
            {
                //check in ra trong kho nao $row['location'] vs $kho
                    if($kho == "" ){ // in tat ca k phan biet
                        $id ++;
                        $output .= '
                    <tr>
                    <td>'.$id.'</td>
                    <td>'.$row['title'].'</td>
                    <td>'.$row['variant_title'].'</td>
                    <td>'.$row['price'].'</td>
                    <td>'.$row['quantity'].'</td>
                    <td>'.$row['price'] * $row['quantity'].'</td>
                    <td>'.$row['location'].'</td>
                    </tr>
                ';
                }elseif (strpos($row['location'], $kho) != false){
                        $id ++;
                        $output .= '
                    <tr>
                    <td>'.$id.'</td>
                    <td>'.$row['title'].'</td>
                    <td>'.$row['variant_title'].'</td>
                    <td>'.$row['price'].'</td>
                    <td>'.$row['quantity'].'</td>
                    <td>'.$row['price'] * $row['quantity'].'</td>
                    <td>'.$row['location'].'</td>
                    </tr>
                ';
                    }else {

                    }
                //end check in ra trong kho nao

            }
            $output .= '</table>';
            return $output;
        }
        
        public function pdf_order($oder_number)
        {
            
            $this->db->select('line_items');
            $this->db->from('voxy_package_orders');
            $this->db->where("order_number", $oder_number);
            $query = $this->db->get();
            $data = $query->result_array();
            $_export = array();
            foreach ($data[0] as $item){
                foreach (json_decode($item) as $key2 => $item2 ){
                    $_export[$key2] = get_object_vars($item2);
                }
            }
            
            //ghep location like key to sort
            $export = array();
            foreach($_export as $key => $item){
                if($item["location"] == false){
                    $item["location"] = $key."_NULL";
                }
                $export[$item["location"]] = $item;
            }
            //ksort tag theo khoa, krsort giam theo khoa hehe :D
            ksort($export);
            
            $output = '<table width="100%" cellspacing="5" cellpadding="5" style="font-family: DejaVu Sans">
            <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Loại SP</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Tổng</th>
            <th>Vị trí</th>
            </tr>
            ';
            $id = 0;
            foreach($export as $row)
            {
                $id ++;
                $output .= '
                <tr>
                <td>'.$id.'</td>
                <td>'.$row['title'].'</td>
                <td>'.$row['variant_title'].'</td>
                <td>'.$row['price'].'</td>
                <td>'.$row['quantity'].'</td>
                <td>'.$row['price'] * $row['quantity'].'</td>
                <td>'.$row['location'].'</td>
                </tr>
                ';
            }
            $output .= '</table>';
            return $output;
        }
        
        
    }
    
    ?>

