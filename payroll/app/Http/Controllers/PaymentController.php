<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
//use Illuminate\Support\Facades\Log;
use App\Rules\ValidName;
use App\Rules\ValidYear;

class PaymentController extends Controller
{
   
    /**
    *  Payment dates list
    *
    *  @return CVS file
    *
    **/
    public function index($language,$name,$year)
    {
        return $this::show($language,$name,$year);
    }

    /**
    *  Show Payment dates list
    *
    *  @return CVS file
    *
    **/
    public function show($language,$name,$year)
    {
        
        if ($this::validateLanguage($language))
        {
            \App::setLocale($language);
        }
        if ($this::validateName($name))
        {
            if ($this::validateYear($year))
               return $this::renderCSV($name,$year);
            else 
                return 'year error';
        } 
        else        
            return 'name error';

    }

    /**
    * Render CSV file
    *
    *  @return CSV file
    *
    **/
    private function renderCSV($name,$year)
    {
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',   
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename='.$name.'.csv',
                'Expires'             => '0',
                'Pragma'              => 'public'
        ];        

        $columns = array(trans('payments.paymentsheaders.column1'), 
                         trans('payments.paymentsheaders.column2'), 
                         trans('payments.paymentsheaders.column3'), 
                         trans('payments.paymentsheaders.column4')
                         );

        $rows = $this::paymentDays($year);

        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);
        foreach($rows as $row) 
        {
            fputcsv($file, array($row['name'], $row['expenses'][1], $row['expenses'][2], $row['lastday']));
        }
        fclose($file);
        return response()->download($name.'.csv', $name.'.csv', $headers); 
    }
  
    /**
    * Payments by month
    *
    *  @return array
    *
    **/
    private function paymentDays($year)
    {
        $payments = array();
        for ($i=1;$i<13;$i++)
        {
            $month=$i < 10? '0'.$i:$i;
            $payments[$i]['name'] = date("F", strtotime($year.'-'.$month.'-01'));
            $payments[$i]['lastday'] = date('Y-m-d',$this::lastDayOfMonth($month,$year));
            $payments[$i]['expenses'][1] = date('Y-m-d',$this::weekDaysValidation($year,$month,1,'expenses'));
            $payments[$i]['expenses'][2] = date('Y-m-d',$this::weekDaysValidation($year,$month,15,'expenses'));
        }      
        return $payments;
    } 

    /*
    * Last day of the month
    *
    *  @return array
    *
    **/
    private function lastDayOfMonth($month,$year)
    {
        $day = date('t',strtotime($year.'-'.$month.'-01'));
        $date = $this::weekDaysValidation($year,$month,$day,'normal');
        return $date;
    }      

    /**
    * Return the weekdays validated by weekends
    *
    *  @return date
    *
    **/
    private function weekDaysValidation($year,$month,$day,$case)
    {
        $date = strtotime($year.'-'.$month.'-'.$day);
        $weekday = date('w',$date);
        if($weekday == 0 && $case == 'normal') {
            $date = strtotime('-2 day',$date); 
        }
        if($weekday == 6 && $case == 'normal') {
            $date = strtotime('-1 day',$date); 
        }        
        if($weekday == 0 && $case == 'expenses') {
            $date = strtotime('+1 day',$date); 
        }
        if($weekday == 6 && $case == 'expenses') {
            $date = strtotime('+2 day',$date); 
        } 

        return $date;    
    }

    private function validateLanguage($value){
        return strlen($value) == 2 && !$this::isEmpty($value);        
    }

    private function validateName($value){
        return !$this::isEmpty($value);        
    }

    private function validateYear($value){
        return strlen($value)==4 && !$this::isEmpty($value);        
    }

    private function isEmpty($value){
        return empty($value);
    }

}
