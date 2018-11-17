<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    class HtmltoPDF extends CI_Controller {
        public function __construct()
        {
            parent::__construct();
            $this->load->model('m_htmltopdf');
            $this->load->library('pdf');
        }
        
        public function index()
        {
            $html_content = '<h3 align="center">Convert HTML to PDF in CodeIgniter using Dompdf</h3>';
            $date_time = $this->input->post('date_for_orders');
            $date_time_end = $this->input->post('date_for_orders_end');
            $week= $this->input->post('week');
            $month = $this->input->post('month');
            $year = $this->input->post('year');
            // vi tri trong kho hang
            $lil = $this->input->post('lil');
            $asia24 = $this->input->post('asia24');
            $out_size = $this->input->post('out_size');
            $html_content .= $this->m_htmltopdf->pdf_day($date_time, $date_time_end, $week, $month, $year, $lil, $asia24, $out_size);
            $this->pdf->loadHtml($html_content);
            $this->pdf->render();
            $this->pdf->stream("".$date_time.".pdf", array("Attachment"=>0));
        }

        public function pdf_order()
        {
            if(isset($_GET["order_number"]))
            {
                $order_number = $_GET["order_number"];
            }
            if(isset($_GET["total_price"]))
            {
                //5 eu tien phi shipping
                $total_price = $_GET["total_price"] +5;
            }
            $html_content = '<h3 align="center">Convert HTML to PDF in CodeIgniter using Dompdf</h3>';
            $html_content .= $this->m_htmltopdf->pdf_order($order_number);
            $this->pdf->loadHtml($html_content);
            $this->pdf->render();
            $this->pdf->stream("".$order_number.".pdf", array("Attachment"=>0));
        }
    }
    ?>

