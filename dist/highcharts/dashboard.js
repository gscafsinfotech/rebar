$(document).ready(function() {
	Highcharts.theme = {
		colors: ['#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572',   
             '#FF9655', '#FFF263', '#6AF9C4'],
		chart: {
			backgroundColor: {
				linearGradient: [0, 0, 500, 500],
				stops: [
					[0, 'rgb(255, 255, 255)'],
					[1, 'rgb(240, 240, 255)']
				]
			},
		},
		title: {
			style: {
				color: '#000',
				font: 'bold 16px "Trebuchet MS", Verdana, sans-serif'
			}
		},
		subtitle: {
			style: {
				color: '#666666',
				font: 'bold 12px "Trebuchet MS", Verdana, sans-serif'
			}
		},

		 legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'top',
			x: -40,
			y: 80,
			floating: true,
			borderWidth: 1,
			backgroundColor:
				Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
			shadow: true
		},
	};
	// Apply the theme
	Highcharts.setOptions(Highcharts.theme);
});

// TOTAL PAY DEPARTMENT WISE
/*function get_salary_info(start_date,end_date){
	var options = {
        chart: {
			type: 'pie',
            renderTo: 'dept_salary_info',
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
			options3d: {
				enabled: true,
				alpha: 45,
				beta: 0
			}
        },
        title: {
            text: 'Salary Info'
        },
        tooltip: {
            formatter: function() {
				return  '<b>'+ Highcharts.numberFormat(this.point.percentage)+' %</b> '+ this.point.name +'<b>: '+  this.y+'</b>';
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
				depth: 35,
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
						return  '<b>'+ Highcharts.numberFormat(this.point.percentage)+' %</b> ';
                        //return '<b>' + this.point.name + '</b>: ' + this.y;
                    }
                },
            showInLegend: true
            }
        },
        series: []
    };	
	$.ajax({
		type: "POST",
     data: {start_date:start_date,end_date:end_date},
		url: './index.php/home/get_dept_salary_info',
		success: function(data) {
			var rslt = JSON.parse(data);			
			options.series = rslt.series; 
			//options.drilldown.series = rslt.drill_list;
			chart = new Highcharts.Chart(options);
		},
	});
	
}


// EMPLOYEES AGE DISTRIBUTION CHART
function get_employee_age_distribution(start_date,end_date){
	var employees_age_info = {
			chart: {
			type: 'bar',
			renderTo: 'employee_age_distribution', 
		},
		 title: {
				text: 'Employees Age Distribution'
			},
		accessibility: {
			point: {
				descriptionFormatter: function (point) {
					var index = point.index + 1,
						category = point.category,
						val = Math.abs(point.y),
						series = point.series.name;

					return index + ', Age ' + category + ', ' + val + '%. ' + series + '.';
				}
			}
		},
		xAxis: [{
			categories: [],
			reversed: false,
			labels: {
				step: 1
			},
			accessibility: {
				description: 'Age (male)'
			}
		}, { // mirror axis on right side
			opposite: true,
			reversed: false,
			categories: [],
			linkedTo: 0,
			labels: {
				step: 1
			},
			accessibility: {
				description: 'Age (female)'
			}
		}],
		yAxis: {
			title: {
				text: null
			},
			labels: {
				formatter: function () {
					return Math.abs(this.value) + '%';
				}
			},
			accessibility: {
				description: 'Percentage population',
				rangeDescription: 'Range: 0 to 5%'
			}
		},

		plotOptions: {
			series: {
				stacking: 'normal'
			}
		},

		tooltip: {
			formatter: function () {
				return '<b>' + this.series.name + ', age ' + this.point.category + '</b><br/>' +
					'Population: ' + Highcharts.numberFormat(Math.abs(this.point.y), 1) + '%';
			}
		},
		series: []
    };	
	$.ajax({
		type: "POST",
     data: {start_date:start_date,end_date:end_date},
		url: './index.php/home/get_employee_age_distribution',
		success: function(data) {
			var rslt         = JSON.parse(data);			
			employees_age_info.series = rslt.series; 
			chart = new Highcharts.Chart(employees_age_info);
		},
	});
	
}

// EMPLOYEE COUNT DESIGNATION WISE
function get_designation_employee_count_info(start_date,end_date){
	var designation_employees_count = {
			 chart: {
					type: 'bar',
					renderTo: 'designation_employees_count'
			 },
			title: {
				text: 'Employees Count',
				x: -20 //center
			},
			xAxis: {
				title: {
					text: 'DESIGNATION'
				}
			},
			yAxis: {
				plotLines: [{
						value: 0,
						width: 1,
						color: '#0001'
					}]
			},
			tooltip: {
				valueSuffix: ''
			},
			plotOptions: {
				column: {
					pointPadding: 0.2,
					borderWidth: 0
				}
			},
		series: []
    };	
	$.ajax({
		type: "POST",
		data: {start_date:start_date,end_date:end_date},
		url: './index.php/home/get_designation_employee_count_info',
		success: function(data) {
			var rslt   = JSON.parse(data);
			designation_employees_count.xAxis.categories = rslt[0]['data'];		
			designation_employees_count.series[0] = rslt[1]; 
			chart = new Highcharts.Chart(designation_employees_count);
		},
	});
}

// PAY DESINATION WISE LINE CHART
function design_wise_sal(start_date,end_date)
{
    var design_wise_sal = {
        chart: {
            renderTo: 'design_wise_sal',
            type: 'column'
        },
        title:{
            text: 'Designation Wise Salary',
            x: -20 //center
        },
        xAxis:{
            categories: [],
            title: {
                text: 'Designation'
            }
        },
        yAxis: {
            title: {
                text: 'Count'
            },
            plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#d84f57'
                }]
        },
        tooltip: {
            valueSuffix: ''
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: []
    };
	$.ajax({
		type: "POST",
		data: {start_date:start_date,end_date:end_date},
		url: './index.php/home/design_wise_sal',
		success: function(data) {
			var rslt   = JSON.parse(data);
			design_wise_sal.xAxis.categories = rslt[0]['data'];		
			design_wise_sal.series[0] = rslt[1]; 
			chart = new Highcharts.Chart(design_wise_sal);
		},
	});
}

// EMPLOYEES COUNT DEPARTMENT WISE LINE CHART
function employees_count_chart(start_date,end_date)
{
    var options = {
        chart: {
            renderTo: 'employees_count_chart',
            type: 'line'
        },
        title: {
            text: 'Department Wise Employees Count',
            x: -20 //center
        },
        subtitle: {
            text: 'Total Employees',
            x: -20
        },
        xAxis: {
            categories: [],
            title: {
                text: 'Department'
            }
        },
        yAxis: {
            title: {
                text: 'Count'
            },
            plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#d84f57'
                }]
        },
        tooltip: {
            valueSuffix: ''
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: []
    };
	$.getJSON("./index.php/Home/employees_count_chart?start_date="+start_date+"&end_date="+end_date, function(json) {
       //alert(data);
        options.xAxis.categories = json[0]['data']; //xAxis: {categories: []}
        options.series[0] = json[1];
        chart = new Highcharts.Chart(options);
    });
}
// TOTAL SALARY MONTH WISE CHART
function total_salary_month_chart(start_date,end_date)
{
    var options = {
        chart: {
            renderTo: 'total_salary_month_chart',
            type: 'column'
        },
        title: {
            text: 'TOTAL SALARY MONTH WISE',
            x: -20 //center
        },
        subtitle: {
            text: 'Total Salary',
            x: -20
        },
        xAxis: {
            categories: [],
            title: {
                text: 'Month'
            },
        },
        yAxis: {
		max: '12000000',
            title: {
                text: ''
            },
            plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#d84f57'
                }]
        },
        tooltip: {
            valueSuffix: ''
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
		plotOptions: {
				column: {
					pointWidth: 70,
					pointPadding: 0.2,
					//colorByPoint: true,
					borderWidth: 0,
					dataLabels: {
					enabled: true,
					color: 'green',
					formatter: function() {
					total_pay  =  "Rs "+this.point.y;
					return total_pay;
					},
				}
			},
		},
        series: []
    };
	$.getJSON("./index.php/Home/total_salary_month_chart?start_date="+start_date+"&end_date="+end_date, function(json) {
        options.xAxis.categories = json[0]['data'];
        options.series[0] = json[1];
        chart = new Highcharts.Chart(options);
    });
}*/

function get_candidate_sts_info(start_date,end_date){
	$.ajax({
		type: "POST",
     data: {start_date:start_date,end_date:end_date},
		url: './index.php/home/get_candidate_sts_info',
		success: function(data) {
			var rslt         = JSON.parse(data);
			$('#table_view_candidate_sts_info').html(rslt.message);
		},
	});
}
// TOTAL GENDER DISTRIBUTION CHART
function candidate_sts_chart(start_date,end_date){
	var options = {
        chart: {
			type: 'pie',
			name:'Candidate Status Chart',
            renderTo: 'consult_candidate_sts_chart',		
            plotBorderWidth: null,
            plotShadow: false,
			options3d: {
				enabled: true,
				alpha: 45,
				beta: 0
			}
        },
        title: {
            text: 'Candidate Status'
        },
        tooltip: {
            formatter: function() {
				return  '<b>'+ Highcharts.numberFormat(this.point.percentage)+' %</b> '+ this.point.name +'<b>: '+  this.y+'</b>';
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
				depth: 35,
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
						return  '<b>'+ Highcharts.numberFormat(this.point.percentage)+' %</b> '+ this.point.name +'<b>: '+  this.y+'</b>';
                    }
                },
                showInLegend: false
            }
        },
        series: [],
		drilldown: {
			series: []
		}
    };	
	$.ajax({
		type: "POST",
		data: {start_date:start_date,end_date:end_date},
		url: './index.php/home/candidate_sts_chart',
		success: function(data) {
			var rslt = JSON.parse(data);			
			options.series = rslt.series; 
			options.drilldown.series = rslt.drill_list;
			chart = new Highcharts.Chart(options);
		},
	});	
}