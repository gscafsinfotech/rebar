select SEC_TO_TIME(SUM(TIME_TO_SEC(timediff(out_hour, in_hour)))) total_hours,in_hour,out_hour,entry_date,employee_code from cw_punched_data_details where employee_code ="0072" and trans_status = 1