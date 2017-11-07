<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
	/*
	* Return the payment dates list
	**/
    public function index($language,$name,$year)
    {
        return this::show($language,$name,$year);
    }

    public function show($language,$name,$year)
    {
    	return $this::paymentDays($year);
        //return $this::renderCVS($language,$name,$year);
    }

	/*
	* Render CSV file --- Error in Response (not enough time to find the issue)
	**/
    private function renderCVS($language,$name,$year){
	    $headers = [
	            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0'
	        ,   'Content-type'        => 'text/csv'
	        ,   'Content-Disposition' => 'attachment; filename='.$name.'.csv'
	        ,   'Expires'             => '0'
	        ,   'Pragma'              => 'public'
	    ];    	

    	$columns = array('Month Name', '1st expenses day', '2nd expenses day', 'Salary day');
    	$payments = $this::paymentDays($year);

	    $callback = function() use ($payments, $columns)
	    {
	        $file = fopen('php://output', 'w');
	        fputcsv($file, $columns);

	        foreach($reviews as $review) {
	            fputcsv($file, array($row['name'], $row['expenses'][1], $row['expenses'][2], $row['lastday']));
	        }
	        fclose($file);
	    };
	    return Response::stream($callback, 200, $headers);
    }
  
 	/*
	* Return the array with the payments by month
	**/ 
    private function paymentDays($year)
    {
    	$months = array();
       	for ($i=1;$i<13;$i++){
       		$month=$i < 10? '0'.$i:$i;
       		$months[$i]['name'] = date("F", strtotime($year.'-'.$month.'-01'));
    		$months[$i]['lastday'] = date('Y-m-d',$this::lastDayOfMonth($month,$year));
    		$months[$i]['expenses'][1] = date('Y-m-d',$this::weekDaysValidation($year,$month,1,'expenses'));
    		$months[$i]['expenses'][2] = date('Y-m-d',$this::weekDaysValidation($year,$month,15,'expenses'));
    	}  	
    	return $months;
    } 

	/*
	* Return the last day of the month
	**/
    private function lastDayOfMonth($month,$year)
    {
    	$day = date('t',strtotime($year.'-'.$month.'-01'));
    	$date = $this::weekDaysValidation($year,$month,$day,'normal');
        return $date;
    }      

	/*
	* Return the weekdays validated by weekends
	**/    
	private function weekDaysValidation($year,$month,$day,$case)
	{
		$date = strtotime($year.'-'.$month.'-'.$day);
    	$weekday = date('w',$date);
    	//echo $weekday.'-'.date('Y m d',$date).'<br>';
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

    /* Validate the empty values and messages by language - not enough time to finish it */
    private function validateInput($input)
    {

    }  
    private function isEmpty($value)
    {
    	return (!empty($value));
    }
    private function messages($type,$value)
    {
    	switch ($type) {
    		case 'empty':
    			$message = '';
    			break;
    		
    		default:
    			# code...
    			break;
    	}
    	return $message;
    } 
}
